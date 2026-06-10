<?php

namespace App\Http\Controllers;

use App\Models\PortalNotification;
use App\Models\PushSubscription;
use App\Services\PortalNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalPushSubscriptionController extends Controller
{
    public function subscribe(Request $request, PortalNotificationService $notifications): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
        ]);

        $user = $request->user();

        PushSubscription::updateOrCreate(
            ['endpoint' => $data['endpoint']],
            [
                'user_id' => $user->id,
                'p256dh' => $data['keys']['p256dh'],
                'auth' => $data['keys']['auth'],
                'user_agent' => $request->userAgent(),
                'is_active' => true,
                'last_seen_at' => now(),
            ]
        );

        $notifications->preferenceFor($user)->update(['push_enabled' => true]);

        return response()->json(['ok' => true]);
    }

    public function unsubscribe(Request $request, PortalNotificationService $notifications): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['nullable', 'string'],
        ]);

        $query = PushSubscription::where('user_id', $request->user()->id);

        if (! empty($data['endpoint'])) {
            $query->where('endpoint', $data['endpoint']);
        }

        $query->update(['is_active' => false]);
        $notifications->preferenceFor($request->user())->update(['push_enabled' => false]);

        return response()->json(['ok' => true]);
    }

    public function test(Request $request, PortalNotificationService $notifications): JsonResponse
    {
        $notification = $notifications->createForUser(
            $request->user(),
            'PUSH_TEST',
            'Δοκιμή ειδοποίησης',
            'Αν το βλέπεις σε κινητό ή υπολογιστή, το push λειτουργεί σωστά.',
            route('portal.notifications.index', absolute: false),
            ['source' => 'push_test']
        );

        return response()->json([
            'ok' => true,
            'notification_id' => $notification?->id,
        ]);
    }
}
