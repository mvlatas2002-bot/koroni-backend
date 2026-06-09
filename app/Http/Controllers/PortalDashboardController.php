<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Support\AuthenticatedUserPayload;
use App\Support\PortalAccess;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load(['role', 'department', 'position', 'manager', 'secondaryApprover']);

        $users = User::with(['role', 'department', 'position', 'manager'])
            ->orderBy('department_id')
            ->orderBy('name')
            ->get();

        $team = User::with(['role', 'department', 'position'])
            ->where('manager_id', $user->id)
            ->orderBy('name')
            ->get();

        if ($team->isEmpty() && $user->department_id) {
            $team = User::with(['role', 'department', 'position'])
                ->where('department_id', $user->department_id)
                ->whereKeyNot($user->id)
                ->orderBy('name')
                ->limit(8)
                ->get();
        }

        return view('portal.dashboard', [
            'user' => $user,
            'team' => $team,
            'bootstrap' => AuthenticatedUserPayload::bootstrap($user),
            'navigation' => PortalAccess::navigation($user),
            'availableWidgets' => PortalAccess::dashboardWidgets($user),
            'enabledModules' => PortalAccess::enabledModules(),
            'counts' => [
                'users' => User::count(),
                'roles' => Role::count(),
                'departments' => Department::count(),
                'positions' => Position::count(),
            ],
            'users' => $users,
            'roles' => Role::orderBy('code')->get(),
            'departments' => Department::withCount(['users', 'positions'])->orderBy('name')->get(),
            'positions' => Position::with('department')->orderBy('level', 'desc')->orderBy('title')->get(),
        ]);
    }
}
