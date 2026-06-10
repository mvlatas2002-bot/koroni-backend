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
                                ['code' => 'WAREHOUSEMEN_TEAM', 'large' => true],
                                ['code' => 'DRIVERS_TEAM', 'large' => true],
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
                        ['code' => 'SALES_DEPT', 'large' => true],
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

    $directPeopleCount = fn ($unit) => $peopleFor($unit)->count();

    $treePeopleCount = function ($node) use (&$treePeopleCount, $unitByCode, $directPeopleCount) {
        $unit = $unitByCode->get($node['code']);
        $total = $directPeopleCount($unit);

        foreach (($node['children'] ?? []) as $child) {
            $total += $treePeopleCount($child);
        }

        return $total;
    };

    $renderPerson = function ($assignment, $mode = 'line') use ($initials) {
        $name = $assignment->employeeProfile->full_name;
        $title = $assignment->position?->title;

        if ($mode === 'compact') {
            return '<span class="org-name-chip">'.e($name).'</span>';
        }

        return '<div class="org-person-row"><span class="avatar mini">'.e($initials($name)).'</span><div><strong>'.e($name).'</strong>'.($title ? '<small>'.e($title).'</small>' : '').'</div></div>';
    };

    $renderPeople = function ($unit, $large = false) use ($leaderFor, $membersFor, $renderPerson) {
        $leader = $leaderFor($unit);
        $members = $membersFor($unit);
        $total = $members->count() + ($leader ? 1 : 0);
        $html = '';

        if ($leader) {
            $html .= '<div class="org-leader-card"><span>Υπεύθυνος</span>'.$renderPerson($leader).'</div>';
        }

        if ($members->isEmpty()) {
            return $html;
        }

        $previewLimit = $large ? 2 : 4;
        $html .= '<div class="org-members-preview"><span>Μέλη</span><div>';
        foreach ($members->take($previewLimit) as $member) {
            $html .= $renderPerson($member, 'compact');
        }
        $html .= '</div></div>';

        if ($members->count() > $previewLimit || $large) {
            $html .= '<details class="org-roster"><summary>Όλοι οι άνθρωποι <em>'.$total.' άτομα</em></summary><div class="org-roster-grid">';
            if ($leader) {
                $html .= $renderPerson($leader);
            }
            foreach ($members as $member) {
                $html .= $renderPerson($member);
            }
            $html .= '</div></details>';
        }

        return $html;
    };

    $renderUnit = function ($node, $depth = 0) use (&$renderUnit, $unitByCode, $treePeopleCount, $directPeopleCount, $renderPeople) {
        $unit = $unitByCode->get($node['code']);
        if (! $unit) {
            return '';
        }

        $children = $node['children'] ?? [];
        $large = (bool) ($node['large'] ?? false);
        $treeTotal = $treePeopleCount($node);
        $directTotal = $directPeopleCount($unit);
        $kind = $depth === 0 ? 'Τμήμα' : 'Ομάδα';

        $html = '<article class="org-map-card depth-'.$depth.($large ? ' is-large-team' : '').'">';
        $html .= '<header class="org-map-card-head"><div><span class="eyebrow">'.$kind.'</span><h3>'.e($unit->name).'</h3></div><strong>'.$treeTotal.' άτομα</strong></header>';
        $html .= $renderPeople($unit, $large);

        if (! empty($children)) {
            $html .= '<div class="org-child-lane">';
            foreach ($children as $child) {
                $html .= $renderUnit($child, $depth + 1);
            }
            $html .= '</div>';
        } elseif ($directTotal === 0) {
            $html .= '<p class="org-empty-note">Δεν έχουν συνδεθεί ακόμα ενεργοί άνθρωποι.</p>';
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
                <p class="muted">Καθαρή εικόνα διευθύνσεων, τμημάτων, υπευθύνων και ομάδων χωρίς να πνίγεται η οθόνη με ονόματα.</p>
            </div>
        </header>

        <section class="org-map">
            <article class="org-company-banner">
                <span class="eyebrow">Κεντρική εταιρεία</span>
                <h2>{{ $company?->name ?? 'ΚΟΡΩΝΗ Α.Ε.' }}</h2>
                <p>{{ $totalPeople }} ενεργοί άνθρωποι στο portal</p>
            </article>

            <div class="org-directorate-map">
                @foreach ($chart as $directorateNode)
                    @php
                        $directorate = $unitByCode->get($directorateNode['code']);
                        $director = $leaderFor($directorate);
                        $directorateTotal = $treePeopleCount($directorateNode);
                    @endphp

                    @continue(! $directorate)

                    <section class="org-directorate-panel">
                        <header class="org-directorate-hero">
                            <div>
                                <span class="eyebrow">Διεύθυνση</span>
                                <h2>{{ $directorate->name }}</h2>
                            </div>
                            <strong>{{ $directorateTotal }} άτομα</strong>
                            @if ($director)
                                <div class="org-director-strip">
                                    <span class="avatar">{{ $initials($director->employeeProfile->full_name) }}</span>
                                    <div>
                                        <small>{{ $director->position?->title }}</small>
                                        <b>{{ $director->employeeProfile->full_name }}</b>
                                    </div>
                                </div>
                            @endif
                        </header>

                        <div class="org-department-grid">
                            @foreach ($directorateNode['children'] as $departmentNode)
                                {!! $renderUnit($departmentNode) !!}
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </section>
    </main>
</div>
@endsection
