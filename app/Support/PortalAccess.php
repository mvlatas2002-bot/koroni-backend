<?php

namespace App\Support;

use App\Models\User;

class PortalAccess
{
    public static function roleLabel(?string $roleCode): ?string
    {
        return config("portal.roles.$roleCode");
    }

    public static function permissions(User $user): array
    {
        $roleCode = $user->role?->code;
        $departmentCode = $user->department?->code;
        $positionTitle = $user->position?->title;
        $isFieldOps = in_array($departmentCode, ['LOGISTICS_FUNCTION', 'MOVEMENT_OFFICE', 'WAREHOUSEMEN_TEAM', 'DRIVERS_TEAM', 'RECEIVING_DEPT'], true);

        return [
            'can_manage_organization' => self::hasAnyRole($roleCode, ['OPERATIONS_ADMIN', 'SYSTEM_ADMIN']),
            'can_view_people_information' => self::hasAnyRole($roleCode, self::roleGroup('people_viewers')),
            'can_publish_announcements' => self::hasAnyRole($roleCode, self::roleGroup('people_viewers')),
            'can_manage_meetings' => self::hasAnyRole($roleCode, self::roleGroup('people_viewers')),
            'can_manage_process_library' => self::hasAnyRole($roleCode, self::roleGroup('management')),
            'can_manage_onboarding' => self::hasAnyRole($roleCode, self::roleGroup('management')),
            'can_access_dispatch_board' => self::hasAnyRole($roleCode, self::roleGroup('management')) || $positionTitle === 'Logistic Manager' || $isFieldOps,
            'can_view_sales_program' => self::hasAnyRole($roleCode, self::roleGroup('sales_program_viewers'))
                || in_array($departmentCode, ['CUSTOMER_DEPT', 'OPERATIONS_DEPT'], true),
            'can_manage_all_sales_programs' => self::hasAnyRole($roleCode, self::roleGroup('sales_program_managers')),
            'can_approve_requests' => self::hasAnyRole($roleCode, self::roleGroup('approvers')),
            'can_manage_leave_balances' => self::hasAnyRole($roleCode, ['OPERATIONS_ADMIN', 'SYSTEM_ADMIN', 'MANAGEMENT'])
                || $departmentCode === 'ACCOUNTING_DEPT',
            'can_manage_platform' => self::hasAnyRole($roleCode, self::roleGroup('platform_admins')),
        ];
    }

    public static function navigation(User $user): array
    {
        $permissions = self::permissions($user);

        return collect(config('portal.navigation'))
            ->filter(function (array $item) use ($permissions) {
                if (($item['roles'] ?? null) === 'all') {
                    return self::moduleEnabled($item['module'] ?? null);
                }

                if (isset($item['permission']) && !($permissions[$item['permission']] ?? false)) {
                    return false;
                }

                return self::moduleEnabled($item['module'] ?? null);
            })
            ->values()
            ->all();
    }

    public static function dashboardWidgets(User $user): array
    {
        $roleCode = $user->role?->code;
        $profile = match (true) {
            self::hasAnyRole($roleCode, ['OPERATIONS_ADMIN', 'SYSTEM_ADMIN', 'MANAGEMENT']) => 'operations',
            self::hasAnyRole($roleCode, ['SUPERVISOR', 'COMMERCIAL_DIRECTOR']) => 'manager',
            $roleCode === 'SALES_REP' => 'sales',
            default => 'basic',
        };

        return collect(config("portal.default_widgets.$profile", []))
            ->filter(fn (string $widgetKey) => self::canViewWidget($widgetKey, $user))
            ->map(fn (string $widgetKey) => [
                'key' => $widgetKey,
                'title' => config("portal.widgets.$widgetKey.title"),
                'summary' => config("portal.widgets.$widgetKey.summary"),
                'slot' => config("portal.widgets.$widgetKey.default_slot"),
                'module' => config("portal.widgets.$widgetKey.module"),
                'viewer_group' => config("portal.widgets.$widgetKey.viewer_group"),
            ])
            ->values()
            ->all();
    }

    public static function enabledModules(): array
    {
        return collect(config('portal.modules'))
            ->filter(fn (array $module) => $module['included'] ?? false)
            ->keys()
            ->values()
            ->all();
    }

    public static function canViewWidget(string $widgetKey, User $user): bool
    {
        $widget = config("portal.widgets.$widgetKey");

        if (!$widget) {
            return false;
        }

        if (!self::moduleEnabled($widget['module'] ?? null)) {
            return false;
        }

        $allowedRoles = $widget['allowed_roles'] ?? [];

        if ($allowedRoles === 'all') {
            return true;
        }

        return self::hasAnyRole($user->role?->code, $allowedRoles);
    }

    public static function moduleEnabled(?string $moduleKey): bool
    {
        if ($moduleKey === null) {
            return true;
        }

        return (bool) config("portal.modules.$moduleKey.included", false);
    }

    public static function hasAnyRole(?string $roleCode, array $roles): bool
    {
        return $roleCode !== null && in_array($roleCode, $roles, true);
    }

    private static function roleGroup(string $group): array
    {
        return config("portal.role_groups.$group", []);
    }
}
