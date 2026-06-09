<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApprovalSectionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_keeps_leave_and_discount_sections_separate(): void
    {
        $user = $this->userWithRole('SUPERVISOR');

        $response = $this->actingAs($user)->get(route('portal.dashboard'));

        $response->assertOk();
        $response->assertSee('Νέα έκπτωση');
        $response->assertSee('Οι εκπτώσεις μου');
        $response->assertSee('Εκπτώσεις προς έγκριση');
        $response->assertSee('/approval-requests?type=discount', false);
        $response->assertSee('/approval-requests/pending?type=discount', false);
        $response->assertSee('Νέα άδεια');
        $response->assertSee('Οι άδειές μου');
        $response->assertSee('Άδειες προς έγκριση');
        $response->assertSee('/approval-requests?type=leave', false);
        $response->assertSee('/approval-requests/pending?type=leave', false);
    }

    public function test_leave_and_discount_create_forms_show_different_fields(): void
    {
        $user = $this->userWithRole('STANDARD_USER');

        $this->actingAs($user)
            ->get(route('portal.approvals.create', ['type' => 'leave']))
            ->assertOk()
            ->assertSee('Νέα αίτηση άδειας')
            ->assertSee('Τίτλος άδειας')
            ->assertSee('Από')
            ->assertSee('Έως')
            ->assertDontSee('Κανονική τιμή');

        $this->actingAs($user)
            ->get(route('portal.approvals.create', ['type' => 'discount']))
            ->assertOk()
            ->assertSee('Νέα αίτηση έκπτωσης')
            ->assertSee('Επωνυμία πελάτη')
            ->assertSee('Κωδικός πελάτη')
            ->assertSee('Κανονική τιμή')
            ->assertSee('Ζητούμενη τιμή')
            ->assertDontSee('Τίτλος άδειας');
    }

    private function userWithRole(string $roleCode): User
    {
        $role = Role::create([
            'code' => $roleCode,
            'name' => $roleCode,
            'is_system' => true,
        ]);

        return User::create([
            'name' => $roleCode . ' User',
            'email' => strtolower($roleCode) . '-approval@example.test',
            'password' => Hash::make('secret'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }
}
