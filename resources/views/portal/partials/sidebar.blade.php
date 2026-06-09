@php
    use App\Models\ApprovalRequest;
    use App\Support\PortalAccess;

    $permissions = PortalAccess::permissions($user);
    $initials = collect(preg_split('/\s+/u', $user->name) ?: [])->filter()->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->join('');
    $roleName = $user->role?->name ?? 'Χωρίς ρόλο';
    $departmentName = $user->department?->name ?? 'Χωρίς οργανωτική μονάδα';
    $activeRoute = request()->route()?->getName() ?? '';
    $activePath = trim(request()->path(), '/');
    $notificationCount = ApprovalRequest::query()
        ->where('status', 'pending')
        ->where(fn ($query) => $query->where('current_approver_id', $user->id)->orWhere('requester_id', $user->id))
        ->count();

    $icons = [
        'home' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 11.5 12 4l8 7.5v7.2a1.8 1.8 0 0 1-1.8 1.8h-3.4v-6.1H9.2v6.1H5.8A1.8 1.8 0 0 1 4 18.7Z"/></svg>',
        'sales' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 18V6.8A2.8 2.8 0 0 1 7.8 4h8.4A2.8 2.8 0 0 1 19 6.8V18M8.5 8h7M8.5 12h7M8.5 16h3"/></svg>',
        'discounts' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 18 12-12M7.5 9.2a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm9 9.6a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/></svg>',
        'leave' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4v3M17 4v3M5.5 8h13M6.8 6h10.4A2.3 2.3 0 0 1 19.5 8.3v10.4a2.3 2.3 0 0 1-2.3 2.3H6.8a2.3 2.3 0 0 1-2.3-2.3V8.3A2.3 2.3 0 0 1 6.8 6Zm2.2 6.2h3.8v3.8H9Z"/></svg>',
        'coordination' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12.8 19 5.5l-3.1 14-4-5.1-5.9 2Z"/></svg>',
        'organization' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5.2v4.1M7.2 18.8h9.6M7.2 14.6v4.2M16.8 14.6v4.2M9.3 9.3h5.4v5.4H9.3Z"/></svg>',
        'routes' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.5 17.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM17.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM8.7 14.5h3.8a5 5 0 0 0 5-5V8"/></svg>',
        'admin' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3.8 18.5 6v5.2c0 4.1-2.6 7.2-6.5 8.9-3.9-1.7-6.5-4.8-6.5-8.9V6Zm-2.3 8.4 1.7 1.7 3.8-4"/></svg>',
    ];

    $groups = [
        ['label' => 'Πωλητές', 'description' => 'Πρόγραμμα ημέρας', 'icon' => 'sales', 'visible' => $permissions['can_view_sales_program'], 'items' => [
            ['label' => 'Πρόγραμμα πωλητών', 'href' => route('portal.modules.show', 'sales_program')],
        ]],
        ['label' => 'Εκπτώσεις', 'description' => 'Αιτήσεις και εγκρίσεις', 'icon' => 'discounts', 'items' => [
            ['label' => 'Νέα έκπτωση', 'href' => route('portal.approvals.create', ['type' => 'discount'])],
            ['label' => 'Οι εκπτώσεις μου', 'href' => route('portal.approvals.index', ['type' => 'discount'])],
            ['label' => 'Εκπτώσεις προς έγκριση', 'href' => route('portal.approvals.pending', ['type' => 'discount']), 'visible' => $permissions['can_approve_requests']],
        ]],
        ['label' => 'Άδειες', 'description' => 'Άδειες και ημερολόγιο', 'icon' => 'leave', 'items' => [
            ['label' => 'Νέα άδεια', 'href' => route('portal.approvals.create', ['type' => 'leave'])],
            ['label' => 'Οι άδειές μου', 'href' => route('portal.approvals.index', ['type' => 'leave'])],
            ['label' => 'Άδειες προς έγκριση', 'href' => route('portal.approvals.pending', ['type' => 'leave']), 'visible' => $permissions['can_approve_requests']],
        ]],
        ['label' => 'Συντονισμός', 'description' => 'Ενημερώσεις και meetings', 'icon' => 'coordination', 'items' => [
            ['label' => 'Ανακοινώσεις', 'href' => route('portal.modules.show', 'announcements')],
            ['label' => 'Meetings', 'href' => route('portal.modules.show', 'meetings')],
        ]],
        ['label' => 'Οργάνωση', 'description' => 'Άνθρωποι και δομή', 'icon' => 'organization', 'visible' => $permissions['can_view_people_information'], 'items' => [
            ['label' => 'Οργανόγραμμα', 'href' => route('portal.organization.index')],
            ['label' => 'Χρήστες', 'href' => route('portal.users.index')],
            ['label' => 'Τμήματα και ομάδες', 'href' => route('portal.organization.units'), 'visible' => $permissions['can_manage_organization']],
            ['label' => 'Λειτουργίες', 'href' => route('portal.modules.index'), 'visible' => $permissions['can_manage_organization']],
        ]],
        ['label' => 'Δρομολόγια', 'description' => 'Περιοχές και στόλος', 'icon' => 'routes', 'visible' => $permissions['can_access_dispatch_board'], 'items' => [
            ['label' => 'Περιοχές σήμερα', 'href' => route('portal.modules.show', 'dispatch')],
            ['label' => 'Στόλος', 'href' => route('portal.modules.show', 'fleet'), 'visible' => $permissions['can_manage_organization']],
        ]],
        ['label' => 'Διαχείριση', 'description' => 'Κανόνες και έλεγχος', 'icon' => 'admin', 'visible' => $permissions['can_manage_organization'], 'items' => [
            ['label' => 'Κανόνες εκπτώσεων', 'href' => route('portal.approval-authorities.index')],
            ['label' => 'Κανόνες εγκρίσεων', 'href' => route('portal.modules.show', 'approvals')],
            ['label' => 'SOPs', 'href' => route('portal.modules.show', 'process_library')],
        ]],
    ];

    $groups = collect($groups)
        ->filter(fn ($group) => $group['visible'] ?? true)
        ->map(function ($group) {
            $group['items'] = collect($group['items'])->filter(fn ($item) => $item['visible'] ?? true)->values()->all();
            return $group;
        })
        ->filter(fn ($group) => count($group['items']) > 0)
        ->values();

    $isActiveHref = function (string $href) use ($activePath): bool {
        $path = trim(parse_url($href, PHP_URL_PATH) ?? '', '/');
        $query = parse_url($href, PHP_URL_QUERY);

        if ($query) {
            parse_str($query, $hrefQuery);
            foreach ($hrefQuery as $key => $value) {
                if ((string) request()->query($key) !== (string) $value) {
                    return false;
                }
            }
            return $activePath === $path;
        }

        return $path !== '' && ($activePath === $path || str_starts_with($activePath, $path.'/'));
    };
@endphp

<aside class="sidebar">
    <div class="brand">
        <div class="brand-mark">KP</div>
        <div>
            <strong>Koroni Portal</strong>
            <span>Κέντρο λειτουργίας</span>
        </div>
    </div>

    <nav class="nav" data-accordion-scope>
        <a class="nav-link nav-link-home {{ $activeRoute === 'portal.dashboard' ? 'active' : '' }}" href="{{ route('portal.dashboard') }}">
            <span class="nav-icon">{!! $icons['home'] !!}</span>
            <span><strong>Αρχική</strong><span>Dashboard ημέρας</span></span>
        </a>

        @foreach ($groups as $group)
            @php
                $groupActive = collect($group['items'])->contains(fn ($item) => $isActiveHref($item['href']));
            @endphp
            <details class="nav-group {{ $groupActive ? 'active' : '' }}" @if($groupActive) open @endif>
                <summary>
                    <span class="nav-icon">{!! $icons[$group['icon']] !!}</span>
                    <span class="nav-group-title">
                        <strong>{{ $group['label'] }}</strong>
                        <span>{{ $group['description'] }}</span>
                    </span>
                    <span class="nav-chevron">⌄</span>
                </summary>

                <div class="nav-submenu">
                    @foreach ($group['items'] as $item)
                        <a class="nav-sublink {{ $isActiveHref($item['href']) ? 'active' : '' }}" href="{{ $item['href'] }}">
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </details>
        @endforeach
    </nav>

    <div class="account-dock">
        <a class="account-action-button" href="{{ route('portal.approvals.create', ['type' => 'leave']) }}" aria-label="Νέα άδεια">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4v3M17 4v3M5.5 8h13M6.8 6h10.4A2.3 2.3 0 0 1 19.5 8.3v10.4a2.3 2.3 0 0 1-2.3 2.3H6.8a2.3 2.3 0 0 1-2.3-2.3V8.3A2.3 2.3 0 0 1 6.8 6Z"/></svg>
        </a>

        <a class="account-action-button has-badge" href="{{ $permissions['can_approve_requests'] ? route('portal.approvals.pending') : route('portal.approvals.index') }}" aria-label="Κέντρο ειδοποιήσεων">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 9.4a6 6 0 1 0-12 0c0 6-2.2 6.8-2.2 6.8h16.4S18 15.4 18 9.4ZM9.7 19.2a2.6 2.6 0 0 0 4.6 0"/></svg>
            @if ($notificationCount > 0)
                <span>{{ $notificationCount > 9 ? '9+' : $notificationCount }}</span>
            @endif
        </a>

        <a class="account-action-button" href="{{ $permissions['can_manage_organization'] ? route('portal.organization.units') : route('portal.profile.edit') }}" aria-label="Ρυθμίσεις">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 15.2a3.2 3.2 0 1 0 0-6.4 3.2 3.2 0 0 0 0 6.4Zm7.2-3.2a7.4 7.4 0 0 0-.1-1.1l2-1.5-2-3.5-2.4 1a8.2 8.2 0 0 0-1.9-1.1L14.5 3h-5l-.4 2.8a8.2 8.2 0 0 0-1.9 1.1l-2.4-1-2 3.5 2 1.5a7.4 7.4 0 0 0 0 2.2l-2 1.5 2 3.5 2.4-1a8.2 8.2 0 0 0 1.9 1.1l.4 2.8h5l.4-2.8a8.2 8.2 0 0 0 1.9-1.1l2.4 1 2-3.5-2-1.5c.1-.4.1-.8.1-1.1Z"/></svg>
        </a>

        <details class="account-menu">
            <summary aria-label="Άνοιγμα προφίλ">
                <span class="account-pill-avatar">{{ $initials }}</span>
                <span class="account-pill-copy">
                    <strong>{{ $user->name }}</strong>
                    <span>{{ $roleName }}</span>
                </span>
                <svg class="account-caret" viewBox="0 0 24 24" aria-hidden="true"><path d="m7 10 5 5 5-5"/></svg>
            </summary>

            <div class="account-card">
                <div class="account-card-hero">
                    <span class="account-pill-avatar large">{{ $initials }}</span>
                    <div style="min-width:0;">
                        <strong>{{ $user->name }}</strong>
                        <span>{{ $user->email }}</span>
                    </div>
                </div>
                <span class="account-department">{{ $roleName }} · {{ $departmentName }}</span>

                <a class="account-menu-link" href="{{ route('portal.profile.edit') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12.2a4.1 4.1 0 1 0 0-8.2 4.1 4.1 0 0 0 0 8.2Zm-7.1 7.3c.9-3.6 3.5-5.4 7.1-5.4s6.2 1.8 7.1 5.4"/></svg>
                    <span>Προφίλ και λογαριασμός</span>
                </a>
                <a class="account-menu-link" href="{{ $permissions['can_approve_requests'] ? route('portal.approvals.pending') : route('portal.approvals.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 9.4a6 6 0 1 0-12 0c0 6-2.2 6.8-2.2 6.8h16.4S18 15.4 18 9.4ZM9.7 19.2a2.6 2.6 0 0 0 4.6 0"/></svg>
                    <span>Κέντρο ειδοποιήσεων</span>
                </a>
                @if ($permissions['can_manage_organization'])
                    <a class="account-menu-link" href="{{ route('portal.users.index') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8.5 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm7 1.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM2.8 20c.7-3.4 2.7-5.1 5.7-5.1s5 1.7 5.7 5.1M13.8 17.2c.7-.7 1.7-1.1 3-1.1 2.2 0 3.7 1.2 4.4 3.9"/></svg>
                        <span>Διαχείριση λογαριασμών</span>
                    </a>
                @endif

                <form method="POST" action="{{ route('portal.logout') }}">
                    @csrf
                    <button class="account-menu-link logout" type="submit">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 7V5.8A1.8 1.8 0 0 0 12.2 4H5.8A1.8 1.8 0 0 0 4 5.8v12.4A1.8 1.8 0 0 0 5.8 20h6.4A1.8 1.8 0 0 0 14 18.2V17M10 12h10M17 9l3 3-3 3"/></svg>
                        <span>Έξοδος</span>
                    </button>
                </form>
            </div>
        </details>
    </div>
</aside>
