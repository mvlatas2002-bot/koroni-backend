@extends('portal.layout', ['title' => 'Οργάνωση | Koroni Portal'])

@php
    $unitByCode = $departments->keyBy('code');
    $company = $unitByCode->get('KORONI_AE');

    $chart = [
        [
            'code' => 'OPERATIONAL_DIRECTORATE',
            'children' => [
                [
                    'code' => 'LOGISTICS_FUNCTION',
                    'children' => [
                        ['code' => 'INVOICING_DEPT'],
                        [
                            'code' => 'MOVEMENT_OFFICE',
                            'children' => [
                                ['code' => 'WAREHOUSEMEN_TEAM'],
                                ['code' => 'DRIVERS_TEAM'],
                            ],
                        ],
                        ['code' => 'RECEIVING_DEPT'],
                    ],
                ],
                ['code' => 'ACCOUNTING_DEPT'],
                ['code' => 'OPERATIONS_DEPT'],
                ['code' => 'IT_DEPT'],
            ],
        ],
        [
            'code' => 'COMMERCIAL_DIRECTORATE',
            'children' => [
                [
                    'code' => 'COMMERCIAL_DEPT',
                    'children' => [
                        ['code' => 'SALES_OPS_DEPT'],
                        ['code' => 'CUSTOMER_DEPT'],
                        ['code' => 'SALES_DEPT'],
                    ],
                ],
                ['code' => 'TECHNICAL_TEAM'],
                ['code' => 'PROCUREMENT_DEPT'],
            ],
        ],
    ];

    $peopleFor = fn ($unit) => $assignmentsByDepartment
        ->get($unit?->id, collect())
        ->sortByDesc(fn ($assignment) => $assignment->position?->orgLevel?->rank ?? $assignment->position?->level ?? 0)
        ->values();

    $leaderFor = fn ($unit) => $peopleFor($unit)->first(fn ($assignment) => $assignment->position?->is_managerial);
    $membersFor = fn ($unit) => $peopleFor($unit)->reject(fn ($assignment) => $assignment->position?->is_managerial)->values();

    $initials = function ($name) {
        return collect(preg_split('/\s+/u', trim((string) $name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_substr($part, 0, 1))
            ->implode('');
    };

    $renderPeopleDetails = function ($unit, $compact = false) use ($membersFor, $leaderFor) {
        $leader = $leaderFor($unit);
        $members = $membersFor($unit);
        $total = $members->count() + ($leader ? 1 : 0);

        if ($total === 0) {
            return '';
        }

        $html = '<details class="org-details'.($compact ? ' compact' : '').'"><summary><span>Άνθρωποι</span><em>'.$total.' άτομα</em></summary>';

        if ($leader) {
            $html .= '<div class="org-detail-person pinned"><strong>'.e($leader->employeeProfile->full_name).'</strong><span>'.e($leader->position?->title).'</span></div>';
        }

        if ($members->isNotEmpty()) {
            $html .= '<div class="org-detail-list">';
            foreach ($members as $member) {
                $html .= '<div class="org-detail-person"><strong>'.e($member->employeeProfile->full_name).'</strong><span>'.e($member->position?->title).'</span></div>';
            }
            $html .= '</div>';
        }

        return $html.'</details>';
    };

    $renderUnit = function ($node, $depth = 0) use (&$renderUnit, $unitByCode, $leaderFor, $membersFor, $initials, $renderPeopleDetails) {
        $unit = $unitByCode->get($node['code']);
        $leader = $leaderFor($unit);
        $members = $membersFor($unit);
        $children = $node['children'] ?? [];
        $totalPeople = $members->count() + ($leader ? 1 : 0);

        $html = '<article class="org-node department depth-'.$depth.'">';
        $html .= '<div class="org-unit-title"><div><span class="eyebrow">'.($depth === 0 ? 'Τμήμα' : 'Υποομάδα').'</span><h3>'.e($unit?->name).'</h3></div><span class="pill">'.$totalPeople.' άτομα</span></div>';

        if ($leader) {
            $html .= '<div class="org-manager compact"><span class="avatar small">'.e($initials($leader->employeeProfile->full_name)).'</span><div><small>'.e($leader->position?->title).'</small><strong>'.e($leader->employeeProfile->full_name).'</strong></div></div>';
        }

        if ($members->isNotEmpty()) {
            $html .= '<div class="org-members"><span>Μέλη</span>';
            foreach ($members->take(3) as $member) {
                $html .= '<em>'.e($member->employeeProfile->full_name).'</em>';
            }
            if ($members->count() > 3) {
                $html .= '<em>+'.($members->count() - 3).' ακόμη</em>';
            }
            $html .= '</div>';
        }

        $html .= $renderPeopleDetails($unit, true);

        if (! empty($children)) {
            $html .= '<div class="org-subtree visible">';
            foreach ($children as $child) {
                $html .= $renderUnit($child, $depth + 1);
            }
            $html .= '</div>';
        }

        return $html.'</article>';
    };
@endphp

@section('body')
<div class="shell">
    @include('portal.partials.sidebar', ['user' => $user])

    <main class="content organization-page">
        <header class="topbar compact-topbar">
            <div>
                <div class="eyebrow">Οργάνωση</div>
                <h1>Οργανόγραμμα ΚΟΡΩΝΗ Α.Ε.</h1>
                <p class="muted">Η πραγματική ιεραρχία τμημάτων, υπευθύνων και ομάδων. Οι μεγάλες λίστες ανοίγουν μόνο όταν τις χρειαστείς.</p>
            </div>
        </header>

        <section class="corporate-org">
            <article class="org-node company">
                <span class="eyebrow">Κεντρικό node</span>
                <h2>{{ $company?->name ?? 'ΚΟΡΩΝΗ Α.Ε.' }}</h2>
            </article>

            <div class="org-branch-row">
                @foreach ($chart as $directorateNode)
                    @php
                        $directorate = $unitByCode->get($directorateNode['code']);
                        $director = $leaderFor($directorate);
                    @endphp

                    <section class="org-branch" data-accordion-scope>
                        <article class="org-node directorate">
                            <span class="eyebrow">Διεύθυνση</span>
                            <h2>{{ $directorate?->name }}</h2>
                            @if ($director)
                                <div class="org-manager">
                                    <span class="avatar">{{ $initials($director->employeeProfile->full_name) }}</span>
                                    <div>
                                        <small>{{ $director->position?->title }}</small>
                                        <strong>{{ $director->employeeProfile->full_name }}</strong>
                                    </div>
                                </div>
                            @endif
                        </article>

                        <div class="org-children-row">
                            @foreach ($directorateNode['children'] as $departmentNode)
                                {!! $renderUnit($departmentNode, 0) !!}
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </section>
    </main>
</div>
@endsection
