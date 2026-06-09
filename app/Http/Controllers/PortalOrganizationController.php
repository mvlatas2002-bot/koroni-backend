<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\EmployeeAssignment;
use App\Models\User;
use App\Support\PortalAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PortalOrganizationController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load(['role', 'department', 'position']);
        abort_unless(PortalAccess::permissions($user)['can_view_people_information'], 403);

        $departments = Department::with(['parent', 'children', 'positions'])
            ->withCount(['users', 'positions'])
            ->where('is_active', true)
            ->orderBy('parent_id')
            ->orderBy('name')
            ->get();

        $assignments = EmployeeAssignment::with([
            'employeeProfile.user.role',
            'department',
            'position.orgLevel',
            'directManagerProfile',
            'secondaryApproverProfile',
            'actingManagerProfile',
        ])
            ->where('is_active', true)
            ->where('is_primary', true)
            ->get()
            ->groupBy('department_id');

        return view('portal.organization.index', [
            'user' => $user,
            'departments' => $departments,
            'roots' => $this->roots($departments),
            'childrenByParent' => $departments->groupBy('parent_id'),
            'assignmentsByDepartment' => $assignments,
            'totalPeople' => User::where('is_active', true)->count(),
        ]);
    }

    private function roots(Collection $departments): Collection
    {
        $roots = $departments->whereNull('parent_id');

        if ($roots->isNotEmpty()) {
            return $roots;
        }

        return $departments->filter(fn (Department $department) => $department->code === 'KORONI_GROUP');
    }
}
