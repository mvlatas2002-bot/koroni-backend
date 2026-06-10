<?php

namespace Tests\Feature;

use App\Models\ApprovalRequest;
use App\Models\CompanyHoliday;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Services\ApprovalWorkflowService;
use App\Services\LeaveCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LeaveCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_leave_working_days_exclude_weekends_and_company_holidays(): void
    {
        CompanyHoliday::create([
            'holiday_date' => '2026-05-01',
            'name' => 'Πρωτομαγιά',
        ]);

        $result = app(LeaveCalendarService::class)->workingDatesBetween('2026-04-28', '2026-05-02');

        $this->assertSame(3, $result['charged_days']);
        $this->assertSame(['2026-04-28', '2026-04-29', '2026-04-30'], $result['charged_dates']);
        $this->assertArrayHasKey('2026-05-01', $result['excluded_dates']);
        $this->assertArrayHasKey('2026-05-02', $result['excluded_dates']);
    }

    public function test_leave_approval_prefers_secondary_approver(): void
    {
        $role = Role::create(['code' => 'SUPERVISOR', 'name' => 'Supervisor', 'is_system' => true]);
        $employeeRole = Role::create(['code' => 'SALES_REP', 'name' => 'Sales', 'is_system' => true]);

        $directManager = User::create([
            'name' => 'Direct Manager',
            'email' => 'direct@example.test',
            'password' => Hash::make('secret'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $secondaryApprover = User::create([
            'name' => 'Commercial Director',
            'email' => 'commercial@example.test',
            'password' => Hash::make('secret'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $requester = User::create([
            'name' => 'Requester',
            'email' => 'requester@example.test',
            'password' => Hash::make('secret'),
            'role_id' => $employeeRole->id,
            'manager_id' => $directManager->id,
            'secondary_approver_id' => $secondaryApprover->id,
            'is_active' => true,
        ]);

        $request = app(ApprovalWorkflowService::class)->create($requester->load(['role', 'manager.role', 'secondaryApprover.role']), [
            'workflow_type' => 'leave',
            'title' => 'Κανονική άδεια',
            'starts_on' => '2026-06-10',
            'ends_on' => '2026-06-10',
        ]);

        $this->assertSame('pending', $request->status);
        $this->assertSame($secondaryApprover->id, $request->current_approver_id);
        $this->assertNotSame($directManager->id, $request->current_approver_id);
    }

    public function test_leave_balance_counts_only_approved_days_that_have_arrived(): void
    {
        Carbon::setTestNow('2026-05-02 10:00:00');

        CompanyHoliday::create([
            'holiday_date' => '2026-05-01',
            'name' => 'Πρωτομαγιά',
        ]);

        $role = Role::create(['code' => 'SALES_REP', 'name' => 'Sales', 'is_system' => true]);
        $user = User::create([
            'name' => 'Employee',
            'email' => 'employee@example.test',
            'password' => Hash::make('secret'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        ApprovalRequest::create([
            'request_code' => 'LREQ-2026-000001',
            'workflow_type' => 'leave',
            'title' => 'Κανονική άδεια',
            'requester_id' => $user->id,
            'status' => 'approved',
            'starts_on' => '2026-04-28',
            'ends_on' => '2026-05-02',
            'submitted_at' => now(),
            'decided_at' => now(),
        ]);

        $balance = app(LeaveCalendarService::class)->balanceFor($user, 2026);

        $this->assertSame(3, $balance['used_to_date']);
        $this->assertSame(19.0, $balance['remaining_now']);

        Carbon::setTestNow();
    }

    public function test_leave_calendar_page_loads_for_authenticated_user(): void
    {
        $role = Role::create(['code' => 'SALES_REP', 'name' => 'Sales', 'is_system' => true]);
        $department = Department::create(['name' => 'Sales', 'code' => 'SALES_DEPT', 'is_active' => true]);
        $user = User::create([
            'name' => 'Calendar User',
            'email' => 'calendar@example.test',
            'password' => Hash::make('secret'),
            'role_id' => $role->id,
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('portal.leave-calendar.index', ['month' => '2026-06']))
            ->assertOk()
            ->assertSee('Ημερολόγιο αδειών');
    }
}
