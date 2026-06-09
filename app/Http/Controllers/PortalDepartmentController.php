<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Support\PortalAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PortalDepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load(['role', 'department', 'position']);
        $this->authorizeOrganizationManagement($user);

        return view('portal.organization.units', [
            'user' => $user,
            'departments' => Department::with('parent')
                ->withCount(['users', 'positions'])
                ->orderBy('parent_id')
                ->orderBy('org_type')
                ->orderBy('name')
                ->get(),
            'parentOptions' => Department::where('is_active', true)
                ->orderBy('org_type')
                ->orderBy('name')
                ->get(),
            'types' => $this->types(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeOrganizationManagement($request->user()->load(['role', 'department', 'position']));

        Department::create($this->validated($request));

        return redirect()
            ->route('portal.organization.units')
            ->with('status', 'Η οργανωτική μονάδα δημιουργήθηκε.');
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $this->authorizeOrganizationManagement($request->user()->load(['role', 'department', 'position']));

        $data = $this->validated($request, $department);

        if ((int) ($data['parent_id'] ?? 0) === $department->id) {
            return back()->withErrors(['parent_id' => 'Μια μονάδα δεν μπορεί να έχει γονέα τον εαυτό της.']);
        }

        $department->update($data);

        return redirect()
            ->route('portal.organization.units')
            ->with('status', 'Η οργανωτική μονάδα ενημερώθηκε.');
    }

    private function authorizeOrganizationManagement($user): void
    {
        abort_unless(PortalAccess::permissions($user)['can_manage_organization'], 403);
    }

    private function validated(Request $request, ?Department $department = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:80',
                Rule::unique('departments', 'code')->ignore($department?->id),
            ],
            'org_type' => ['required', Rule::in(array_keys($this->types()))],
            'parent_id' => ['nullable', 'exists:departments,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function types(): array
    {
        return [
            'LEGAL_ENTITY' => 'Εταιρεία / νομικός κόμβος',
            'DIRECTORATE_FUNCTION' => 'Διεύθυνση',
            'DEPARTMENT' => 'Τμήμα',
            'TEAM' => 'Ομάδα',
            'LOCATION' => 'Τοποθεσία',
        ];
    }
}
