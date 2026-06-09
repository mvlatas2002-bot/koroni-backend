<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PortalAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_rep_cannot_access_people_or_admin_pages(): void
    {
        $sales = $this->userWithRole('SALES_REP');

        $this->actingAs($sales)->get(route('portal.organization.index'))->assertForbidden();
        $this->actingAs($sales)->get(route('portal.users.index'))->assertForbidden();
        $this->actingAs($sales)->get(route('portal.users.create'))->assertForbidden();
        $this->actingAs($sales)->get(route('portal.modules.index'))->assertForbidden();
        $this->actingAs($sales)->get(route('portal.modules.show', 'ORGANIZATION'))->assertForbidden();
        $this->actingAs($sales)->get(route('portal.organization.units'))->assertForbidden();
    }

    public function test_sales_rep_can_access_sales_program_module(): void
    {
        $sales = $this->userWithRole('SALES_REP');

        $this->actingAs($sales)->get(route('portal.modules.show', 'SALES_PROGRAM'))->assertOk();
    }

    public function test_system_admin_can_access_people_admin_and_modules(): void
    {
        $admin = $this->userWithRole('SYSTEM_ADMIN');

        $this->actingAs($admin)->get(route('portal.organization.index'))->assertOk();
        $this->actingAs($admin)->get(route('portal.users.index'))->assertOk();
        $this->actingAs($admin)->get(route('portal.users.create'))->assertOk();
        $this->actingAs($admin)->get(route('portal.modules.index'))->assertOk();
        $this->actingAs($admin)->get(route('portal.organization.units'))->assertOk();
    }

    public function test_only_approvers_can_access_pending_approval_queue(): void
    {
        $sales = $this->userWithRole('SALES_REP');
        $supervisor = $this->userWithRole('SUPERVISOR');

        $this->actingAs($sales)->get(route('portal.approvals.pending'))->assertForbidden();
        $this->actingAs($supervisor)->get(route('portal.approvals.pending'))->assertOk();
    }

    private function userWithRole(string $roleCode): User
    {
        $role = Role::create([
            'code' => $roleCode,
            'name' => $roleCode,
            'is_system' => true,
        ]);

        $department = Department::create([
            'code' => $roleCode . '_DEPT',
            'name' => $roleCode . ' Department',
            'is_active' => true,
        ]);

        return User::create([
            'name' => $roleCode . ' User',
            'email' => strtolower($roleCode) . '@example.test',
            'password' => Hash::make('secret'),
            'role_id' => $role->id,
            'department_id' => $department->id,
            'is_active' => true,
        ]);
    }
}
