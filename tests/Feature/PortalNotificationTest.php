<?php

namespace Tests\Feature;

use App\Models\PortalNotification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PortalNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_center_lists_user_notifications_and_marks_all_read(): void
    {
        $user = $this->user();

        PortalNotification::create([
            'recipient_id' => $user->id,
            'type' => 'TEST',
            'title' => 'Δοκιμαστική ειδοποίηση',
            'message' => 'Μήνυμα για τον χρήστη',
            'link' => route('portal.dashboard', absolute: false),
        ]);

        $this->actingAs($user)
            ->get(route('portal.notifications.index'))
            ->assertOk()
            ->assertSee('Κέντρο ειδοποιήσεων')
            ->assertSee('Δοκιμαστική ειδοποίηση');

        $this->actingAs($user)
            ->post(route('portal.notifications.mark-all-read'))
            ->assertRedirect();

        $this->assertTrue(PortalNotification::first()->is_read);
    }

    private function user(): User
    {
        $role = Role::create([
            'code' => 'STANDARD_USER',
            'name' => 'Standard User',
            'is_system' => true,
        ]);

        return User::create([
            'name' => 'Notification User',
            'email' => 'notification@example.test',
            'password' => Hash::make('secret'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }
}
