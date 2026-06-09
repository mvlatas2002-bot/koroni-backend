<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_update_organization_units(): void
    {
        $admin = $this->admin();

        $parent = Department::create([
            'code' => 'PARENT_DEPT',
            'name' => 'Parent Department',
            'org_type' => 'DEPARTMENT',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('portal.organization.units.store'), [
            'name' => 'Νέα Ομάδα',
            'code' => 'NEW_TEAM',
            'org_type' => 'TEAM',
            'parent_id' => $parent->id,
            'description' => null,
            'is_active' => 1,
        ])->assertRedirect(route('portal.organization.units'));

        $unit = Department::where('code', 'NEW_TEAM')->firstOrFail();
        $this->assertSame('Νέα Ομάδα', $unit->name);
        $this->assertSame($parent->id, $unit->parent_id);

        $this->actingAs($admin)->put(route('portal.organization.units.update', $unit), [
            'name' => 'Νέα Ομάδα Updated',
            'code' => 'NEW_TEAM',
            'org_type' => 'TEAM',
            'parent_id' => $parent->id,
            'description' => null,
            'is_active' => 0,
        ])->assertRedirect(route('portal.organization.units'));

        $unit->refresh();
        $this->assertSame('Νέα Ομάδα Updated', $unit->name);
        $this->assertFalse($unit->is_active);
    }

    public function test_department_seeder_preserves_admin_edited_names_and_admin_created_units(): void
    {
        Department::create([
            'code' => 'SALES_DEPT',
            'name' => 'Πωλήσεις Custom',
            'org_type' => 'TEAM',
            'is_active' => true,
        ]);

        Department::create([
            'code' => 'ADMIN_CREATED',
            'name' => 'Admin Created Unit',
            'org_type' => 'TEAM',
            'is_active' => true,
        ]);

        $this->seed(DepartmentSeeder::class);

        $this->assertDatabaseHas('departments', [
            'code' => 'SALES_DEPT',
            'name' => 'Πωλήσεις Custom',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('departments', [
            'code' => 'ADMIN_CREATED',
            'name' => 'Admin Created Unit',
            'is_active' => true,
        ]);
    }

    private function admin(): User
    {
        $role = Role::create([
            'code' => 'SYSTEM_ADMIN',
            'name' => 'System Admin',
            'is_system' => true,
        ]);

        return User::create([
            'name' => 'Admin Test',
            'email' => 'admin-units@example.test',
            'password' => Hash::make('secret'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }
}
