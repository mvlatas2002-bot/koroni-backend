<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Services\ApprovalWorkflowService;
use App\Support\PortalAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PortalApprovalRequestController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load(['role', 'manager', 'secondaryApprover', 'actingManager']);
        $type = $this->normalizedType($request->query('type'));

        return view('portal.approvals.index', [
            'title' => $this->indexTitle($type),
            'user' => $user,
            'mode' => 'mine',
            'type' => $type,
            'requests' => ApprovalRequest::with(['requester.role', 'currentApprover.role', 'steps.approver.role'])
                ->where('requester_id', $user->id)
                ->when($type, fn ($query) => $query->where('workflow_type', $type))
                ->latest()
                ->get(),
        ]);
    }

    public function pending(Request $request, ApprovalWorkflowService $workflow): View
    {
        $user = $request->user()->load(['role', 'manager', 'secondaryApprover', 'actingManager']);
        abort_unless(PortalAccess::permissions($user)['can_approve_requests'], 403);

        $type = $this->normalizedType($request->query('type'));
        $requests = $workflow->pendingFor($user);

        if ($type) {
            $requests = $requests->where('workflow_type', $type)->values();
        }

        return view('portal.approvals.index', [
            'title' => $this->pendingTitle($type),
            'user' => $user,
            'mode' => 'pending',
            'type' => $type,
            'requests' => $requests,
        ]);
    }

    public function create(Request $request): View
    {
        $type = $this->normalizedType($request->query('type')) ?? 'general';

        return view('portal.approvals.form', [
            'user' => $request->user()->load(['role', 'manager', 'secondaryApprover', 'actingManager']),
            'type' => $type,
            'reasonCategories' => $this->discountReasonCategories(),
        ]);
    }

    public function store(Request $request, ApprovalWorkflowService $workflow): RedirectResponse
    {
        $workflowType = $request->input('workflow_type');

        $data = match ($workflowType) {
            'discount' => $this->validatedDiscountData($request),
            'leave' => $this->validatedLeaveData($request),
            default => $this->validatedGeneralData($request),
        };

        $approvalRequest = $workflow->create(
            $request->user()->load(['role', 'department', 'manager.role', 'secondaryApprover.role', 'actingManager.role']),
            $data
        );

        return redirect()
            ->route('portal.approvals.show', $approvalRequest)
            ->with('status', $approvalRequest->status === 'draft'
                ? 'Το προσχέδιο αποθηκεύτηκε.'
                : 'Η αίτηση δημιουργήθηκε και δρομολογήθηκε σωστά.');
    }

    public function show(Request $request, ApprovalRequest $approvalRequest): View
    {
        $user = $request->user()->load('role');
        $approvalRequest->load(['requester.role', 'currentApprover.role', 'steps.approver.role']);
        $activeStep = $approvalRequest->steps->firstWhere('status', 'pending');
        $canDecide = $approvalRequest->status === 'pending' && (
            $approvalRequest->current_approver_id === $user->id ||
            ($activeStep && $activeStep->approver_id === null && $activeStep->required_role_code === $user->role?->code) ||
            $user->role?->code === 'SYSTEM_ADMIN'
        );

        abort_unless(
            $approvalRequest->requester_id === $user->id ||
            $approvalRequest->current_approver_id === $user->id ||
            $canDecide ||
            $user->role?->code === 'SYSTEM_ADMIN',
            403
        );

        return view('portal.approvals.show', [
            'user' => $user,
            'approvalRequest' => $approvalRequest,
            'canDecide' => $canDecide,
        ]);
    }

    public function decide(
        Request $request,
        ApprovalRequest $approvalRequest,
        ApprovalWorkflowService $workflow
    ): RedirectResponse {
        $data = $request->validate([
            'decision' => ['required', 'in:approve,reject,comment'],
            'comments' => ['nullable', 'string', 'max:1000'],
        ]);

        $workflow->decide(
            $request->user()->load('role'),
            $approvalRequest,
            $data['decision'],
            $data['comments'] ?? null
        );

        return redirect()
            ->route('portal.approvals.pending', ['type' => $approvalRequest->workflow_type])
            ->with('status', 'Η απόφαση καταχωρήθηκε και η αίτηση ενημερώθηκε.');
    }

    private function validatedDiscountData(Request $request): array
    {
        $intent = $request->input('intent', 'submit');
        $required = $intent === 'draft' ? 'nullable' : 'required';

        $validated = $request->validate([
            'workflow_type' => ['required', 'in:discount'],
            'intent' => ['nullable', 'in:draft,submit'],
            'request_date' => [$required, 'date'],
            'customer_name' => [$required, 'string', 'max:180'],
            'customer_code' => [$required, 'string', 'max:80'],
            'product_summary' => [$required, 'string', 'max:2000'],
            'regular_price' => [$required, 'numeric', 'min:0.01'],
            'requested_price' => [$required, 'numeric', 'min:0.01'],
            'reason_category' => [$required, Rule::in(array_keys($this->discountReasonCategories()))],
            'reason' => ['nullable', 'string', 'max:1200'],
            'comments' => ['nullable', 'string', 'max:1200'],
        ]);

        if ($intent !== 'draft' && (float) $validated['requested_price'] > (float) $validated['regular_price']) {
            throw ValidationException::withMessages([
                'requested_price' => 'Η ζητούμενη τιμή δεν μπορεί να είναι μεγαλύτερη από την κανονική.',
            ]);
        }

        $regularPrice = (float) ($validated['regular_price'] ?? 0);
        $requestedPrice = (float) ($validated['requested_price'] ?? 0);
        $discountPercent = $regularPrice > 0 && $requestedPrice > 0
            ? round(max((($regularPrice - $requestedPrice) / $regularPrice) * 100, 0), 2)
            : null;

        if ($intent !== 'draft' && $discountPercent !== null && $discountPercent > 10 && empty($validated['reason_category'])) {
            throw ValidationException::withMessages([
                'reason_category' => 'Για εκπτώσεις πάνω από 10% χρειάζεται κατηγορία λόγου.',
            ]);
        }

        $customerName = trim((string) ($validated['customer_name'] ?? ''));

        return [
            'workflow_type' => 'discount',
            'intent' => $intent,
            'title' => $customerName !== ''
                ? sprintf('Έκπτωση %s%% - %s', number_format((float) $discountPercent, 2), $customerName)
                : 'Προσχέδιο έκπτωσης',
            'description' => $validated['reason'] ?? $validated['comments'] ?? null,
            'amount' => $regularPrice > 0 && $requestedPrice > 0 ? round($regularPrice - $requestedPrice, 2) : null,
            'discount_percent' => $discountPercent,
            'payload' => [
                'request_date' => $validated['request_date'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_code' => $validated['customer_code'] ?? null,
                'product_summary' => $validated['product_summary'] ?? null,
                'regular_price' => $validated['regular_price'] ?? null,
                'requested_price' => $validated['requested_price'] ?? null,
                'reason_category' => $validated['reason_category'] ?? null,
                'reason_category_label' => $this->discountReasonCategories()[$validated['reason_category'] ?? ''] ?? null,
                'reason' => $validated['reason'] ?? null,
                'comments' => $validated['comments'] ?? null,
            ],
        ];
    }

    private function validatedLeaveData(Request $request): array
    {
        return $request->validate([
            'workflow_type' => ['required', 'in:leave'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
        ]);
    }

    private function validatedGeneralData(Request $request): array
    {
        return $request->validate([
            'workflow_type' => ['required', 'in:general'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'amount' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function discountReasonCategories(): array
    {
        return [
            'STRATEGIC_CUSTOMER' => 'Στρατηγικός πελάτης',
            'COMPETITIVE_PRICING_PRESSURE' => 'Ανταγωνιστική πίεση τιμής',
            'PROMOTIONAL_ACTION' => 'Προωθητική ενέργεια',
            'COMMERCIAL_AGREEMENT' => 'Εμπορική συμφωνία',
            'PRICING_CORRECTION' => 'Διόρθωση τιμής',
            'EXCEPTIONAL_HANDLING' => 'Εξαιρετικός χειρισμός',
            'OTHER' => 'Άλλο',
        ];
    }

    private function normalizedType(?string $type): ?string
    {
        return in_array($type, ['leave', 'discount', 'general'], true) ? $type : null;
    }

    private function indexTitle(?string $type): string
    {
        return match ($type) {
            'leave' => 'Οι άδειές μου',
            'discount' => 'Οι αιτήσεις έκπτωσης μου',
            default => 'Οι αιτήσεις μου',
        };
    }

    private function pendingTitle(?string $type): string
    {
        return match ($type) {
            'leave' => 'Άδειες προς έγκριση',
            'discount' => 'Εκπτώσεις προς έγκριση',
            default => 'Εγκρίσεις προς απόφαση',
        };
    }
}
