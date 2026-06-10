<?php

namespace App\Http\Controllers;

use App\Models\PortalNotification;
use App\Services\PortalNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalNotificationController extends Controller
{
    public function index(Request $request, PortalNotificationService $notifications): View
    {
        $user = $request->user()->load(['role', 'department']);
        $preference = $notifications->preferenceFor($user);

        return view('portal.notifications.index', [
            'user' => $user,
            'notifications' => $user->portalNotifications()->latest()->limit(80)->get(),
            'unreadCount' => $notifications->unreadCount($user),
            'preference' => $preference,
            'activeDevices' => $user->pushSubscriptions()->where('is_active', true)->latest('last_seen_at')->get(),
            'pushReady' => config('notifications.push.enabled')
                && filled(config('notifications.push.public_key'))
                && filled(config('notifications.push.private_key')),
            'vapidPublicKey' => config('notifications.push.public_key'),
        ]);
    }

    public function open(Request $request, PortalNotification $notification): RedirectResponse
    {
        abort_unless($notification->recipient_id === $request->user()->id, 403);

        if (! $notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return redirect($notification->link ?: route('portal.notifications.index'));
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()
            ->portalNotifications()
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return back()->with('status', 'Οι ειδοποιήσεις σημειώθηκαν ως διαβασμένες.');
    }
}
