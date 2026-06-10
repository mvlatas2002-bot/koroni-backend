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
    $directPeopleCount = fn ($unit) => $peopleFor($unit)->count();

    $treePeopleCount = function ($node) use (&$treePeopleCount, $unitByCode, $directPeopleCount) {
        $unit = $unitByCode->get($node['code']);
        $total = $directPeopleCount($unit);

        foreach (($node['children'] ?? []) as $child) {
            $total += $treePeopleCount($child);
        }

        return $total;
    };

    $initials = function ($name) {
        return collect(preg_split('/\s+/u', trim((string) $name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_substr($part, 0, 1))
            ->implode('');
    };

    $renderPerson = function ($assignment, $leader = false) use ($initials) {
        $name = $assignment->employeeProfile->full_name;
        $title = $assignment->position?->title;
        $label = $leader ? '<em>Υπεύθυνος</em>' : '';

        return '<div class="org-tree-person">'
            .'<span class="avatar mini">'.e($initials($name)).'</span>'
            .'<div><strong>'.e($name).'</strong>'.($title ? '<small>'.e($title).'</small>' : '').'</div>'
            .$label
            .'</div>';
    };

    $renderPeopleBlock = function ($unit) use ($leaderFor, $membersFor, $renderPerson) {
        $leader = $leaderFor($unit);
        $members = $membersFor($unit);

        if (! $leader && $members->isEmpty()) {
            return '<p class="org-tree-empty">Δεν έχουν συνδεθεί ακόμα ενεργοί άνθρωποι.</p>';
        }

        $html = '<div class="org-tree-people">';
        if ($leader) {
            $html .= $renderPerson($leader, true);
        }

        foreach ($members as $member) {
            $html .= $renderPerson($member);
        }

        return $html.'</div>';
    };

    $renderUnit = function ($node, $depth = 0) use (&$renderUnit, $unitByCode, $treePeopleCount, $directPeopleCount, $renderPeopleBlock) {
        $unit = $unitByCode->get($node['code']);
        if (! $unit) {
            return '';
        }

        $children = $node['children'] ?? [];
        $treeTotal = $treePeopleCount($node);
        $directTotal = $directPeopleCount($unit);
        $label = $depth === 0 ? 'Τμήμα' : 'Ομάδα';

        $html = '<details class="org-tree-unit depth-'.$depth.'">';
        $html .= '<summary><span><small>'.$label.'</small><b>'.e($unit->name).'</b></span><em>'.$treeTotal.' άτομα</em></summary>';
        $html .= '<div class="org-tree-unit-body">';

        if ($directTotal > 0) {
            $html .= $renderPeopleBlock($unit);
        }

        if (! empty($children)) {
            $html .= '<div class="org-tree-children">';
            foreach ($children as $child) {
                $html .= $renderUnit($child, $depth + 1);
            }
            $html .= '</div>';
        } elseif ($directTotal === 0) {
            $html .= $renderPeopleBlock($unit);
        }

        return $html.'</div></details>';
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
                <p class="muted">Συμμετρική προβολή: αρχικά βλέπεις μόνο τη δομή και ανοίγεις όποιο τμήμα θέλεις για πλήρη ονόματα.</p>
            </div>
        </header>

        <section class="org-tree-map">
            <article class="org-tree-company">
                <div>
                    <span class="eyebrow">Κεντρική εταιρεία</span>
                    <h2>{{ $company?->name ?? 'ΚΟΡΩΝΗ Α.Ε.' }}</h2>
                </div>
                <strong>{{ $totalPeople }} ενεργοί άνθρωποι</strong>
            </article>

            <div class="org-tree-columns">
                @foreach ($chart as $directorateNode)
                    @php
                        $directorate = $unitByCode->get($directorateNode['code']);
                        $director = $leaderFor($directorate);
                        $directorateTotal = $treePeopleCount($directorateNode);
                    @endphp

                    @continue(! $directorate)

                    <section class="org-tree-column">
                        <header class="org-tree-directorate">
                            <div>
                                <span class="eyebrow">Διεύθυνση</span>
                                <h2>{{ $directorate->name }}</h2>
                            </div>
                            <strong>{{ $directorateTotal }} άτομα</strong>
                        </header>

                        @if ($director)
                            <details class="org-tree-director">
                                <summary>Προβολή υπεύθυνου διεύθυνσης</summary>
                                <div class="org-tree-people single">
                                    {!! $renderPerson($director, true) !!}
                                </div>
                            </details>
                        @endif

                        <div class="org-tree-list">
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
