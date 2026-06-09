<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\EmployeeAssignment;
use App\Models\EmployeeProfile;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Support\PortalAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PortalUserController extends Controller
{
    public function index(Request $request): View
    {
        $viewer = $request->user()->load(['role', 'department', 'position']);
        abort_unless(PortalAccess::permissions($viewer)['can_view_people_information'], 403);

        return view('portal.users.index', [
            'user' => $viewer,
            'users' => User::with(['role', 'department', 'position', 'manager', 'secondaryApprover'])
                ->orderBy('department_id')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizeUserManagement($request);

        return view('portal.users.form', $this->formData($request, new User(), 'create'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeUserManagement($request);

        $data = $this->validated($request);
        $data['password'] = Hash::make($data['password'] ?? 'Koroni!2026');
        $data['is_active'] = $request->boolean('is_active', true);

        $createdUser = User::create($data);
        $this->syncEmployeeAssignment($createdUser);

        return redirect()->route('portal.users.index')->with('status', 'Ο χρήστης δημιουργήθηκε.');
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorizeUserManagement($request);

        $user->load(['role', 'department', 'position', 'manager', 'secondaryApprover']);

        return view('portal.users.form', $this->formData($request, $user, 'edit'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeUserManagement($request);

        $data = $this->validated($request, $user);
        $data['is_active'] = $request->boolean('is_active', true);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        $this->syncEmployeeAssignment($user->fresh());

        return redirect()->route('portal.users.index')->with('status', 'Ο χρήστης ενημερώθηκε.');
    }

    private function formData(Request $request, User $user, string $mode): array
    {
        $departmentQuery = Department::query()
            ->where('is_active', true)
            ->whereNotIn('org_type', ['LEGAL_ENTITY']);

        if ($user->department_id) {
            $departmentQuery->orWhereKey($user->department_id);
        }

        return [
            'user' => $request->user()->load(['role', 'department', 'position']),
            'mode' => $mode,
            'portalUser' => $user,
            'roles' => Role::orderBy('code')->get(),
            'departments' => $departmentQuery->orderBy('org_type')->orderBy('name')->get(),
            'positions' => Position::with('department')
                ->where('is_active', true)
                ->orderBy('level', 'desc')
                ->orderBy('title')
                ->get(),
            'managers' => User::with(['role', 'department', 'position'])
                ->where('is_active', true)
                ->whereKeyNot($user->id ?? 0)
                ->orderBy('name')
                ->get(),
        ];
    }

    private function authorizeUserManagement(Request $request): void
    {
        $viewer = $request->user()->loadMissing(['role', 'department', 'position']);
        abort_unless(PortalAccess::permissions($viewer)['can_manage_organization'], 403);
    }

    private function syncEmployeeAssignment(User $user): void
    {
        $user->load(['manager.employeeProfile', 'secondaryApprover.employeeProfile', 'actingManager.employeeProfile']);

        $profile = EmployeeProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $user->name,
                'email' => $user->email,
                'employment_type' => 'internal',
                'employment_status' => $user->employment_status ?: 'active',
                'is_external_collaborator' => false,
                'is_active' => $user->is_active,
                'annual_leave_allowance' => 22,
            ]
        );

        if (! $user->department_id) {
            return;
        }

        EmployeeAssignment::updateOrCreate(
            [
                'employee_profile_id' => $profile->id,
                'is_primary' => true,
            ],
            [
                'department_id' => $user->department_id,
                'position_id' => $user->position_id,
                'direct_manager_profile_id' => $user->manager?->employeeProfile?->id,
                'secondary_approver_profile_id' => $user->secondaryApprover?->employeeProfile?->id,
                'acting_manager_profile_id' => $user->actingManager?->employeeProfile?->id,
                'is_active' => $user->is_active,
                'effective_from' => now()->toDateString(),
                'effective_to' => null,
            ]
        );
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:1'],
            'employment_status' => ['required', 'string', 'max:80'],
            'role_id' => ['required', 'exists:roles,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'secondary_approver_id' => ['nullable', 'exists:users,id'],
            'acting_manager_id' => ['nullable', 'exists:users,id'],
        ]);
    }
}
