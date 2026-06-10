<?php

namespace App\Http\Controllers;

use App\Support\PortalAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class PortalModuleController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load(['role', 'department', 'position']);
        abort_unless(PortalAccess::permissions($user)['can_manage_organization'], 403);

        $modules = collect(config('portal.modules'))
            ->map(fn (array $module, string $key) => $this->modulePayload($key, $module))
            ->values();

        return view('portal.modules.index', [
            'user' => $user,
            'navigation' => PortalAccess::navigation($user),
            'modules' => $modules,
            'enabledCount' => $modules->where('included', true)->count(),
            'disabledCount' => $modules->where('included', false)->count(),
        ]);
    }

    public function show(Request $request, string $module): View|RedirectResponse
    {
        $user = $request->user()->load(['role', 'department', 'position']);
        $moduleKey = strtoupper($module);
        $definition = config("portal.modules.$moduleKey");

        abort_unless($definition, 404);
        abort_unless($this->canViewModule($moduleKey, $user), 403);

        if ($moduleKey === 'ORGANIZATION') {
            return redirect()->route('portal.organization.index');
        }

        if ($moduleKey === 'SALES_PROGRAM') {
            return redirect()->route('portal.sales-program.index');
        }

        $widgets = collect(config('portal.widgets'))
            ->filter(fn (array $widget) => ($widget['module'] ?? null) === $moduleKey)
            ->map(fn (array $widget, string $key) => [
                'key' => $key,
                ...$widget,
                'visible_for_user' => PortalAccess::canViewWidget($key, $user),
            ])
            ->values();

        $dependents = collect(config('portal.modules'))
            ->filter(fn (array $candidate) => in_array($moduleKey, $candidate['dependencies'] ?? [], true))
            ->map(fn (array $candidate, string $key) => $this->modulePayload($key, $candidate))
            ->values();

        return view('portal.modules.show', [
            'user' => $user,
            'navigation' => PortalAccess::navigation($user),
            'moduleKey' => $moduleKey,
            'module' => $this->modulePayload($moduleKey, $definition),
            'widgets' => $widgets,
            'dependents' => $dependents,
        ]);
    }

    private function canViewModule(string $moduleKey, $user): bool
    {
        $permissions = PortalAccess::permissions($user);

        return match ($moduleKey) {
            'ORGANIZATION' => $permissions['can_view_people_information'],
            'SALES_PROGRAM' => $permissions['can_view_sales_program'],
            'DISPATCH', 'FLEET' => $permissions['can_access_dispatch_board'],
            'APPROVALS' => $permissions['can_approve_requests'] || $permissions['can_manage_organization'],
            default => PortalAccess::moduleEnabled($moduleKey),
        };
    }

    private function modulePayload(string $key, array $module): array
    {
        return [
            'key' => $key,
            'title' => $module['title'],
            'summary' => $module['summary'],
            'category' => $module['category'],
            'included' => (bool) ($module['included'] ?? false),
            'dependencies' => Arr::wrap($module['dependencies'] ?? []),
            'widget_count' => collect(config('portal.widgets'))
                ->filter(fn (array $widget) => ($widget['module'] ?? null) === $key)
                ->count(),
        ];
    }
}
