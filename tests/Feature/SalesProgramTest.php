<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Role;
use App\Models\SalesProgramDayStatus;
use App\Models\SalesProgramStop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SalesProgramTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_rep_can_manage_only_their_own_program(): void
    {
        [$sales, $otherSales] = $this->salesUsers();

        $this->actingAs($sales)->post(route('portal.sales-program.stops.store'), [
            'sales_rep_id' => $sales->id,
            'day_label' => 'Δευτέρα',
            'area' => 'Ηράκλειο',
            'customer_label' => 'Πελάτης Α',
            'sort_order' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('sales_program_stops', [
            'sales_rep_id' => $sales->id,
            'day_label' => 'Δευτέρα',
            'area' => 'Ηράκλειο',
            'customer_label' => 'Πελάτης Α',
        ]);

        $this->actingAs($sales)->post(route('portal.sales-program.stops.store'), [
            'sales_rep_id' => $otherSales->id,
            'day_label' => 'Τρίτη',
            'area' => 'Χανιά',
            'sort_order' => 1,
        ])->assertForbidden();
    }

    public function test_manager_can_create_program_for_any_sales_rep(): void
    {
        [$sales] = $this->salesUsers();
        $manager = $this->userWithRole('COMMERCIAL_DIRECTOR', 'manager@example.test');

        $this->actingAs($manager)->post(route('portal.sales-program.stops.store'), [
            'sales_rep_id' => $sales->id,
            'day_label' => 'Παρασκευή',
            'area' => 'Ρέθυμνο',
            'customer_label' => 'Πελάτης Β',
            'sort_order' => 2,
        ])->assertRedirect();

        $this->assertDatabaseHas('sales_program_stops', [
            'sales_rep_id' => $sales->id,
            'day_label' => 'Παρασκευή',
            'area' => 'Ρέθυμνο',
        ]);
    }

    public function test_sales_rep_can_start_and_end_their_day(): void
    {
        [$sales] = $this->salesUsers();

        $this->actingAs($sales)->post(route('portal.sales-program.day.start'), [
            'schedule_date' => '2026-06-10',
        ])->assertRedirect();

        $status = SalesProgramDayStatus::first();
        $this->assertNotNull($status?->started_at);
        $this->assertNull($status?->ended_at);

        $this->actingAs($sales)->post(route('portal.sales-program.day.end'), [
            'schedule_date' => '2026-06-10',
        ])->assertRedirect();

        $this->assertNotNull($status->fresh()->ended_at);
    }

    public function test_today_page_uses_weekly_template_when_no_exact_date_exists(): void
    {
        [$sales] = $this->salesUsers();

        SalesProgramStop::create([
            'sales_rep_id' => $sales->id,
            'day_label' => 'Τετάρτη',
            'area' => 'Σητεία',
            'customer_label' => 'Πελάτης Γ',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($sales)
            ->get(route('portal.sales-program.index', ['date' => '2026-06-10']))
            ->assertOk()
            ->assertSee('Σητεία')
            ->assertSee('Πελάτης Γ');
    }

    private function salesUsers(): array
    {
        return [
            $this->userWithRole('SALES_REP', 'sales-a@example.test'),
            $this->userWithRole('SALES_REP', 'sales-b@example.test'),
        ];
    }

    private function userWithRole(string $roleCode, string $email): User
    {
        $role = Role::firstOrCreate(
            ['code' => $roleCode],
            ['name' => $roleCode, 'is_system' => true]
        );
        $department = Department::firstOrCreate(
            ['code' => $roleCode . '_DEPT'],
            ['name' => $roleCode . ' Department', 'is_active' => true]
        );

        return User::create([
            'name' => $roleCode . ' User',
            'email' => $email,
            'password' => Hash::make('secret'),
            'role_id' => $role->id,
            'department_id' => $department->id,
            'is_active' => true,
        ]);
    }
}
