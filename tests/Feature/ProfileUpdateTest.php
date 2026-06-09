<?php

namespace Tests\Feature;

use App\Models\EmployeeProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_profile_updates_are_persisted_to_database(): void
    {
        $role = Role::create([
            'code' => 'test-role',
            'name' => 'Test Role',
            'description' => 'Test role for profile updates',
            'is_system' => false,
        ]);

        $user = User::create([
            'name' => 'Initial User',
            'email' => 'initial@koronisa.local',
            'password' => Hash::make('50'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('portal.profile.update'), [
                'name' => 'Μάνος Βλατάς Updated',
                'first_name' => 'Μάνος',
                'last_name' => 'Βλατάς',
                'phone' => '6900000000',
                'birth_date' => '2002-01-01',
                'emergency_contact_name' => 'Επαφή Test',
                'emergency_contact_phone' => '2100000000',
                'profile_notes' => 'Σημείωση test',
                'current_password' => '50',
                'new_password' => '51',
                'new_password_confirmation' => '51',
            ]);

        $response->assertRedirect(route('portal.profile.edit'));

        $user->refresh();

        $this->assertSame('Μάνος Βλατάς Updated', $user->name);
        $this->assertSame('Μάνος', $user->first_name);
        $this->assertSame('Βλατάς', $user->last_name);
        $this->assertSame('6900000000', $user->phone);
        $this->assertSame('2002-01-01', $user->birth_date->toDateString());
        $this->assertSame('Επαφή Test', $user->emergency_contact_name);
        $this->assertSame('2100000000', $user->emergency_contact_phone);
        $this->assertSame('Σημείωση test', $user->profile_notes);
        $this->assertTrue(Hash::check('51', $user->password));

        $this->assertDatabaseHas(EmployeeProfile::class, [
            'user_id' => $user->id,
            'full_name' => 'Μάνος Βλατάς Updated',
            'email' => 'initial@koronisa.local',
        ]);
    }
}
