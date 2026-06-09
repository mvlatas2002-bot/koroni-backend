<?php

namespace Tests\Feature;

use App\Models\ApprovalAuthority;
use App\Models\Role;
use App\Models\User;
use App\Services\ApprovalWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DiscountApprovalRulebookTest extends TestCase
{
    use RefreshDatabase;

    public function test_discount_form_uses_the_full_customer_price_reason_shape(): void
    {
        $user = $this->userWithRole('SALES_REP', 'sales-form@example.test');

        $this->actingAs($user)
            ->get(route('portal.approvals.create', ['type' => 'discount']))
            ->assertOk()
            ->assertSee('Επωνυμία πελάτη')
            ->assertSee('Κωδικός πελάτη')
            ->assertSee('Σύνοψη προϊόντων')
            ->assertSee('Κανονική τιμή')
            ->assertSee('Ζητούμενη τιμή')
            ->assertSee('Κατηγορία λόγου')
            ->assertSee('Υπολογισμένη έκπτωση');
    }

    public function test_discount_up_to_four_percent_is_auto_approved(): void
    {
        $requester = $this->userWithRole('SALES_REP', 'sales-auto@example.test');

        $request = app(ApprovalWorkflowService::class)->create($requester->load('role'), [
            'workflow_type' => 'discount',
            'title' => 'Έκπτωση 4%',
            'discount_percent' => 4,
        ]);

        $this->assertSame('approved', $request->status);
        $this->assertSame(0, $request->steps()->count());
    }

    public function test_discount_between_four_and_fifteen_routes_to_commercial_approver(): void
    {
        $requester = $this->userWithRole('SALES_REP', 'sales-commercial@example.test');
        $commercial = $this->userWithRole('COMMERCIAL_DIRECTOR', 'commercial@example.test');

        ApprovalAuthority::create([
            'workflow_type' => 'discount',
            'authority_type' => 'functional_approver',
            'approver_id' => $commercial->id,
            'required_role_code' => 'COMMERCIAL_DIRECTOR',
            'min_percent' => 4,
            'max_percent' => 15,
            'min_inclusive' => false,
            'max_inclusive' => false,
            'effective_from' => now()->toDateString(),
            'is_active' => true,
            'label' => 'Εμπορική έγκριση',
        ]);

        $request = app(ApprovalWorkflowService::class)->create($requester->load('role'), [
            'workflow_type' => 'discount',
            'title' => 'Έκπτωση 8%',
            'discount_percent' => 8,
        ]);

        $this->assertSame('pending', $request->status);
        $this->assertSame($commercial->id, $request->current_approver_id);
        $this->assertSame('COMMERCIAL_DIRECTOR', $request->steps()->first()->required_role_code);
    }

    public function test_exactly_fifteen_percent_routes_to_management(): void
    {
        $requester = $this->userWithRole('SALES_REP', 'sales-management@example.test');
        $management = $this->userWithRole('MANAGEMENT', 'management@example.test');

        ApprovalAuthority::create([
            'workflow_type' => 'discount',
            'authority_type' => 'management',
            'approver_id' => $management->id,
            'required_role_code' => 'MANAGEMENT',
            'min_percent' => 15,
            'max_percent' => null,
            'min_inclusive' => true,
            'max_inclusive' => true,
            'effective_from' => now()->toDateString(),
            'is_active' => true,
            'label' => 'Έγκριση διοίκησης',
        ]);

        $request = app(ApprovalWorkflowService::class)->create($requester->load('role'), [
            'workflow_type' => 'discount',
            'title' => 'Έκπτωση 15%',
            'discount_percent' => 15,
        ]);

        $this->assertSame('pending', $request->status);
        $this->assertSame($management->id, $request->current_approver_id);
        $this->assertSame('MANAGEMENT', $request->steps()->first()->required_role_code);
    }

    public function test_discount_submission_calculates_percent_and_persists_payload(): void
    {
        $requester = $this->userWithRole('SALES_REP', 'sales-submit@example.test');
        $commercial = $this->userWithRole('COMMERCIAL_DIRECTOR', 'commercial-submit@example.test');

        ApprovalAuthority::create([
            'workflow_type' => 'discount',
            'authority_type' => 'functional_approver',
            'approver_id' => $commercial->id,
            'required_role_code' => 'COMMERCIAL_DIRECTOR',
            'min_percent' => 4,
            'max_percent' => 15,
            'min_inclusive' => false,
            'max_inclusive' => false,
            'effective_from' => now()->toDateString(),
            'is_active' => true,
        ]);

        $this->actingAs($requester)
            ->post(route('portal.approvals.store'), [
                'workflow_type' => 'discount',
                'intent' => 'submit',
                'request_date' => '2026-06-08',
                'customer_name' => 'Κρήτη Market',
                'customer_code' => 'CUST-10045',
                'product_summary' => 'Γραβιέρα 20 κιβώτια',
                'regular_price' => 100,
                'requested_price' => 92,
                'reason_category' => 'COMMERCIAL_AGREEMENT',
                'reason' => 'Συμφωνία μήνα',
                'comments' => 'Να περαστεί μετά την έγκριση.',
            ])
            ->assertRedirect();

        $request = \App\Models\ApprovalRequest::firstOrFail();

        $this->assertSame('pending', $request->status);
        $this->assertSame('8.00', $request->discount_percent);
        $this->assertSame($commercial->id, $request->current_approver_id);
        $this->assertSame('Κρήτη Market', $request->payload['customer_name']);
        $this->assertSame('Γραβιέρα 20 κιβώτια', $request->payload['product_summary']);
    }

    private function userWithRole(string $roleCode, string $email): User
    {
        $role = Role::firstOrCreate(
            ['code' => $roleCode],
            ['name' => $roleCode, 'is_system' => true]
        );

        return User::create([
            'name' => $roleCode . ' User',
            'email' => $email,
            'password' => Hash::make('secret'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }
}
