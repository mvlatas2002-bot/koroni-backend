<?php

namespace App\Http\Controllers;

use App\Models\ApprovalAuthority;
use App\Models\Department;
use App\Models\User;
use App\Support\PortalAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalApprovalAuthorityController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('role');
        abort_unless(PortalAccess::permissions($user)['can_manage_organization'], 403);

        return view('portal.approval-authorities.index', [
            'user' => $user,
            'rules' => ApprovalAuthority::with(['approver.role', 'department'])
                ->where('workflow_type', 'discount')
                ->orderByDesc('is_active')
                ->orderBy('min_percent')
                ->get(),
            'approvers' => User::with('role')
                ->where('is_active', true)
                ->whereHas('role', fn ($query) => $query->whereIn('code', [
                    'COMMERCIAL_DIRECTOR',
                    'MANAGEMENT',
                    'OPERATIONS_ADMIN',
                    'SYSTEM_ADMIN',
                    'SUPERVISOR',
                ]))
                ->orderBy('name')
                ->get(),
            'departments' => Department::where('is_active', true)
                ->where('type', '!=', 'LEGAL_ENTITY')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user()->load('role');
        abort_unless(PortalAccess::permissions($user)['can_manage_organization'], 403);

        $data = $this->validatedRule($request);

        ApprovalAuthority::create($data);

        return redirect()
            ->route('portal.approval-authorities.index')
            ->with('status', 'Ο κανόνας έκπτωσης δημιουργήθηκε.');
    }

    public function update(Request $request, ApprovalAuthority $approvalAuthority): RedirectResponse
    {
        $user = $request->user()->load('role');
        abort_unless(PortalAccess::permissions($user)['can_manage_organization'], 403);

        $approvalAuthority->update($this->validatedRule($request));

        return redirect()
            ->route('portal.approval-authorities.index')
            ->with('status', 'Ο κανόνας έκπτωσης ενημερώθηκε.');
    }

    private function validatedRule(Request $request): array
    {
        $data = $request->validate([
            'authority_type' => ['required', 'in:functional_approver,management,role_based'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'approver_id' => ['nullable', 'exists:users,id'],
            'required_role_code' => ['nullable', 'string', 'max:60'],
            'min_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_percent' => ['nullable', 'numeric', 'min:0', 'max:100', 'gte:min_percent'],
            'min_inclusive' => ['nullable', 'boolean'],
            'max_inclusive' => ['nullable', 'boolean'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_active' => ['nullable', 'boolean'],
            'label' => ['nullable', 'string', 'max:180'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        return [
            ...$data,
            'workflow_type' => 'discount',
            'department_id' => $data['department_id'] ?? null,
            'approver_id' => $data['approver_id'] ?? null,
            'required_role_code' => $data['required_role_code'] ?: null,
            'max_percent' => $data['max_percent'] ?? null,
            'min_inclusive' => (bool) ($data['min_inclusive'] ?? false),
            'max_inclusive' => (bool) ($data['max_inclusive'] ?? false),
            'effective_from' => $data['effective_from'] ?? now()->toDateString(),
            'effective_to' => $data['effective_to'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'label' => $data['label'] ?: null,
            'notes' => $data['notes'] ?? null,
        ];
    }
}
