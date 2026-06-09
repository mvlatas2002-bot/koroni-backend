<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserFormOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_active_user_appears_in_future_manager_and_replacement_options(): void
    {
        [$admin, $role] = $this->adminAndRole();

        $this->actingAs($admin)->post(route('portal.users.store'), [
            'name' => 'Νέος Αντικαταστάτης',
            'first_name' => 'Νέος',
            'last_name' => 'Αντικαταστάτης',
            'email' => 'neos.antikatastatis@koronisa.local',
            'password' => '1',
            'employment_status' => 'active',
            'is_active' => '1',
            'role_id' => $role->id,
            'department_id' => null,
            'position_id' => null,
            'manager_id' => null,
            'secondary_approver_id' => null,
            'acting_manager_id' => null,
        ])->assertRedirect(route('portal.users.index'));

        $this->actingAs($admin)
            ->get(route('portal.users.create'))
            ->assertOk()
            ->assertSee('Νέος Αντικαταστάτης');
    }

    public function test_user_form_excludes_company_node_from_department_options(): void
    {
        [$admin] = $this->adminAndRole();

        Department::create([
            'code' => 'KORONI_AE',
            'name' => 'ΚΟΡΩΝΗ Α.Ε.',
            'org_type' => 'LEGAL_ENTITY',
            'is_active' => true,
        ]);

        Department::create([
            'code' => 'REAL_TEAM',
            'name' => 'Πραγματικό Τμήμα',
            'org_type' => 'TEAM',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('portal.users.create'))
            ->assertOk()
            ->assertDontSee('ΚΟΡΩΝΗ Α.Ε.')
            ->assertSee('Πραγματικό Τμήμα');
    }

    private function adminAndRole(): array
    {
        $adminRole = Role::create([
            'code' => 'SYSTEM_ADMIN',
            'name' => 'System Admin',
            'is_system' => true,
        ]);

        $userRole = Role::create([
            'code' => 'STANDARD_USER',
            'name' => 'Βασικός χρήστης',
            'is_system' => true,
        ]);

        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@example.test',
            'password' => Hash::make('secret'),
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        return [$admin, $userRole];
    }
}
