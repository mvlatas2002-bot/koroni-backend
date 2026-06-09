<?php

namespace App\Support;

use App\Models\User;

class AuthenticatedUserPayload
{
    public static function user(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'is_active' => $user->is_active,
            'employment_status' => $user->employment_status,
            'role' => $user->role ? [
                'id' => $user->role->id,
                'code' => $user->role->code,
                'name' => PortalAccess::roleLabel($user->role->code) ?? $user->role->name,
            ] : null,
            'department' => $user->department ? [
                'id' => $user->department->id,
                'code' => $user->department->code,
                'name' => $user->department->name,
            ] : null,
            'position' => $user->position ? [
                'id' => $user->position->id,
                'code' => $user->position->code,
                'title' => $user->position->title,
                'level' => $user->position->level,
                'is_managerial' => $user->position->is_managerial,
            ] : null,
            'manager' => $user->manager ? [
                'id' => $user->manager->id,
                'name' => $user->manager->name,
                'email' => $user->manager->email,
            ] : null,
            'secondary_approver' => $user->secondaryApprover ? [
                'id' => $user->secondaryApprover->id,
                'name' => $user->secondaryApprover->name,
                'email' => $user->secondaryApprover->email,
            ] : null,
            'acting_manager' => $user->actingManager ? [
                'id' => $user->actingManager->id,
                'name' => $user->actingManager->name,
                'email' => $user->actingManager->email,
            ] : null,
            'direct_reports_count' => $user->directReports()->count(),
        ];
    }

    public static function bootstrap(User $user): array
    {
        return [
            'user' => self::user($user),
            'permissions' => PortalAccess::permissions($user),
            'navigation' => PortalAccess::navigation($user),
            'modules' => PortalAccess::enabledModules(),
            'dashboard' => [
                'available_widgets' => PortalAccess::dashboardWidgets($user),
            ],
        ];
    }
}
