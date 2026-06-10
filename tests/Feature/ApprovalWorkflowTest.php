<?php

namespace Tests\Feature;

use App\Models\ApprovalRequest;
use App\Models\PortalNotification;
use App\Models\Role;
use App\Models\User;
use App\Services\ApprovalWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_leave_request_routes_to_manager_and_can_be_approved(): void
    {
        $managerRole = Role::create([
            'code' => 'SUPERVISOR',
            'name' => 'Προϊστάμενος',
            'is_system' => true,
        ]);

        $userRole = Role::create([
            'code' => 'SALES_REP',
            'name' => 'Πωλητής',
            'is_system' => true,
        ]);

        $manager = User::create([
            'name' => 'Manager Test',
            'email' => 'manager@example.test',
            'password' => Hash::make('secret'),
            'role_id' => $managerRole->id,
            'is_active' => true,
        ]);

        $requester = User::create([
            'name' => 'Requester Test',
            'email' => 'requester@example.test',
            'password' => Hash::make('secret'),
            'role_id' => $userRole->id,
            'manager_id' => $manager->id,
            'is_active' => true,
        ]);

        $workflow = app(ApprovalWorkflowService::class);
        $request = $workflow->create($requester->load(['role', 'manager.role']), [
            'workflow_type' => 'leave',
            'title' => 'Κανονική άδεια',
            'starts_on' => '2026-06-10',
            'ends_on' => '2026-06-10',
        ]);

        $this->assertSame('pending', $request->status);
        $this->assertSame($manager->id, $request->current_approver_id);
        $this->assertSame(1, $request->steps()->count());
        $this->assertSame(1, PortalNotification::where('recipient_id', $manager->id)->where('type', 'LEAVE_APPROVAL_REQUESTED')->count());

        $updated = $workflow->decide($manager->load('role'), $request, 'approve', 'OK');

        $this->assertSame('approved', $updated->status);
        $this->assertNull($updated->current_approver_id);
        $this->assertSame('approved', ApprovalRequest::first()->steps()->first()->status);
        $this->assertSame(1, PortalNotification::where('recipient_id', $requester->id)->where('type', 'LEAVE_APPROVED')->count());
    }

    public function test_discount_up_to_four_percent_is_auto_approved(): void
    {
        $role = Role::create([
            'code' => 'SALES_REP',
            'name' => 'Πωλητής',
            'is_system' => true,
        ]);

        $requester = User::create([
            'name' => 'Sales Test',
            'email' => 'sales@example.test',
            'password' => Hash::make('secret'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $request = app(ApprovalWorkflowService::class)->create($requester->load('role'), [
            'workflow_type' => 'discount',
            'title' => 'Έκπτωση 4%',
            'discount_percent' => 4,
        ]);

        $this->assertSame('approved', $request->status);
        $this->assertSame(0, $request->steps()->count());
    }

    public function test_pending_discount_notification_goes_only_to_the_active_approver(): void
    {
        $salesRole = Role::create(['code' => 'SALES_REP', 'name' => 'Sales Rep', 'is_system' => true]);
        $directorRole = Role::create(['code' => 'COMMERCIAL_DIRECTOR', 'name' => 'Commercial Director', 'is_system' => true]);

        $requester = User::create([
            'name' => 'Sales Requester',
            'email' => 'sales-requester@example.test',
            'password' => Hash::make('secret'),
            'role_id' => $salesRole->id,
            'is_active' => true,
        ]);

        $approver = User::create([
            'name' => 'Commercial Approver',
            'email' => 'commercial-approver@example.test',
            'password' => Hash::make('secret'),
            'role_id' => $directorRole->id,
            'is_active' => true,
        ]);

        $otherDirector = User::create([
            'name' => 'Other Director',
            'email' => 'other-director@example.test',
            'password' => Hash::make('secret'),
            'role_id' => $directorRole->id,
            'is_active' => true,
        ]);

        $request = app(ApprovalWorkflowService::class)->create($requester->load('role'), [
            'workflow_type' => 'discount',
            'title' => 'Discount needs commercial approval',
            'discount_percent' => 8,
        ]);

        $this->assertSame('pending', $request->status);
        $this->assertSame($approver->id, $request->current_approver_id);
        $this->assertSame(1, PortalNotification::where('recipient_id', $approver->id)->where('type', 'DISCOUNT_APPROVAL_REQUESTED')->count());
        $this->assertSame(0, PortalNotification::where('recipient_id', $requester->id)->where('type', 'DISCOUNT_APPROVAL_REQUESTED')->count());
        $this->assertSame(0, PortalNotification::where('recipient_id', $otherDirector->id)->count());
    }

    public function test_final_rejection_notification_goes_only_to_requester(): void
    {
        $salesRole = Role::create(['code' => 'SALES_REP', 'name' => 'Sales Rep', 'is_system' => true]);
        $directorRole = Role::create(['code' => 'COMMERCIAL_DIRECTOR', 'name' => 'Commercial Director', 'is_system' => true]);

        $requester = User::create([
            'name' => 'Rejected Requester',
            'email' => 'rejected-requester@example.test',
            'password' => Hash::make('secret'),
            'role_id' => $salesRole->id,
            'is_active' => true,
        ]);

        $approver = User::create([
            'name' => 'Rejecting Approver',
            'email' => 'rejecting-approver@example.test',
            'password' => Hash::make('secret'),
            'role_id' => $directorRole->id,
            'is_active' => true,
        ]);

        $workflow = app(ApprovalWorkflowService::class);
        $request = $workflow->create($requester->load('role'), [
            'workflow_type' => 'discount',
            'title' => 'Discount rejected',
            'discount_percent' => 8,
        ]);

        $workflow->decide($approver->load('role'), $request, 'reject', 'No');

        $this->assertSame(1, PortalNotification::where('recipient_id', $requester->id)->where('type', 'DISCOUNT_REJECTED')->count());
        $this->assertSame(0, PortalNotification::where('recipient_id', $approver->id)->where('type', 'DISCOUNT_REJECTED')->count());
    }

    public function test_requester_cannot_approve_their_own_pending_request(): void
    {
        $role = Role::create([
            'code' => 'COMMERCIAL_DIRECTOR',
            'name' => 'Commercial Director',
            'is_system' => true,
        ]);

        $requester = User::create([
            'name' => 'Self Approver Test',
            'email' => 'self-approver@example.test',
            'password' => Hash::make('secret'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Other Commercial Director',
            'email' => 'other-commercial@example.test',
            'password' => Hash::make('secret'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $workflow = app(ApprovalWorkflowService::class);
        $request = $workflow->create($requester->load('role'), [
            'workflow_type' => 'discount',
            'title' => 'Discount needs approval',
            'discount_percent' => 8,
        ]);

        $this->assertSame('pending', $request->status);
        $this->assertFalse($workflow->pendingFor($requester)->contains('id', $request->id));

        $this->expectException(ValidationException::class);

        $workflow->decide($requester->load('role'), $request, 'approve');
    }
}
