<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\PortalNotification;
use App\Models\PushSubscription as UserPushSubscription;
use App\Models\User;
use App\Models\UserNotificationPreference;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PortalNotificationService
{
    public function createForUser(
        User|int|null $recipient,
        string $type,
        string $title,
        string $message,
        ?string $link = null,
        array $metadata = []
    ): ?PortalNotification {
        $recipientId = $recipient instanceof User ? $recipient->id : $recipient;

        if (! $recipientId) {
            return null;
        }

        $notification = PortalNotification::create([
            'recipient_id' => $recipientId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'metadata' => $metadata,
        ]);

        $this->sendPush($notification);

        return $notification;
    }

    public function notifyApprovalPending(ApprovalRequest $request): void
    {
        if ($request->status !== 'pending' || ! $request->current_approver_id) {
            return;
        }

        $label = $request->workflow_type === 'leave' ? 'άδεια' : ($request->workflow_type === 'discount' ? 'έκπτωση' : 'αίτηση');

        $this->createForUser(
            $request->current_approver_id,
            strtoupper($request->workflow_type).'_APPROVAL_REQUESTED',
            'Νέα αίτηση προς έγκριση',
            "{$request->request_code} · {$label} · {$request->requester?->name}",
            route('portal.approvals.show', $request, false),
            ['approval_request_id' => $request->id, 'request_code' => $request->request_code]
        );
    }

    public function notifyApprovalFinalDecision(ApprovalRequest $request): void
    {
        if (! in_array($request->status, ['approved', 'rejected'], true)) {
            return;
        }

        $approved = $request->status === 'approved';
        $label = $request->workflow_type === 'leave' ? 'Η άδειά σου' : ($request->workflow_type === 'discount' ? 'Η έκπτωση' : 'Η αίτηση');

        $this->createForUser(
            $request->requester_id,
            strtoupper($request->workflow_type).($approved ? '_APPROVED' : '_REJECTED'),
            $approved ? "{$label} εγκρίθηκε" : "{$label} απορρίφθηκε",
            "{$request->request_code} · {$request->title}",
            route('portal.approvals.show', $request, false),
            ['approval_request_id' => $request->id, 'request_code' => $request->request_code, 'status' => $request->status]
        );
    }

    public function unreadCount(User $user): int
    {
        return $user->portalNotifications()->where('is_read', false)->count();
    }

    public function preferenceFor(User $user): UserNotificationPreference
    {
        return UserNotificationPreference::firstOrCreate(
            ['user_id' => $user->id],
            ['push_enabled' => false, 'email_enabled' => false]
        );
    }

    private function sendPush(PortalNotification $notification): void
    {
        if (! config('notifications.push.enabled')) {
            return;
        }

        $publicKey = (string) config('notifications.push.public_key');
        $privateKey = (string) config('notifications.push.private_key');
        $subject = (string) config('notifications.push.subject');

        if ($publicKey === '' || $privateKey === '' || $subject === '') {
            Log::warning('Push notification skipped because VAPID configuration is incomplete.', [
                'notification_id' => $notification->id,
            ]);

            return;
        }

        $preference = UserNotificationPreference::where('user_id', $notification->recipient_id)->first();

        if (! $preference?->push_enabled) {
            return;
        }

        $subscriptions = UserPushSubscription::where('user_id', $notification->recipient_id)
            ->where('is_active', true)
            ->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => $subject,
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
            ],
        ]);

        $payload = json_encode([
            'title' => $notification->title,
            'body' => $notification->message,
            'url' => $notification->link ?: route('portal.notifications.index', absolute: false),
            'notificationId' => $notification->id,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        foreach ($subscriptions as $subscription) {
            try {
                $report = $webPush->sendOneNotification(
                    Subscription::create([
                        'endpoint' => $subscription->endpoint,
                        'keys' => [
                            'p256dh' => $subscription->p256dh,
                            'auth' => $subscription->auth,
                        ],
                    ]),
                    $payload
                );

                if ($report->isSuccess()) {
                    $subscription->update(['last_seen_at' => now()]);
                    continue;
                }

                $statusCode = $report->getResponse()?->getStatusCode();

                if (in_array($statusCode, [404, 410], true)) {
                    $subscription->update(['is_active' => false]);
                }

                Log::warning('Push delivery failed.', [
                    'notification_id' => $notification->id,
                    'subscription_id' => $subscription->id,
                    'status' => $statusCode,
                    'reason' => $report->getReason(),
                ]);
            } catch (\Throwable $exception) {
                Log::warning('Push delivery exception.', [
                    'notification_id' => $notification->id,
                    'subscription_id' => $subscription->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }
}
