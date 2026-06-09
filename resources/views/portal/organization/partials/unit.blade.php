@php
    $children = $childrenByParent->get($department->id, collect());
    $assignments = $assignmentsByDepartment->get($department->id, collect())
        ->sortByDesc(fn ($assignment) => $assignment->position?->orgLevel?->rank ?? $assignment->position?->level ?? 0);
    $leaders = $assignments->filter(fn ($assignment) => $assignment->position?->is_managerial);
    $members = $assignments->reject(fn ($assignment) => $assignment->position?->is_managerial);
    $typeLabels = [
        'GROUP' => 'Όμιλος',
        'MANAGEMENT' => 'Διοίκηση',
        'LEADERSHIP_BRANCH' => 'Κλάδος ευθύνης',
        'LEGAL_ENTITY' => 'Εταιρεία',
        'BRANCH_SITE' => 'Εγκατάσταση',
        'DIRECTORATE_FUNCTION' => 'Διεύθυνση',
        'DIVISION' => 'Διεύθυνση',
        'DEPARTMENT' => 'Τμήμα',
        'TEAM' => 'Ομάδα',
        'PROCESS_AREA' => 'Περιοχή διαδικασιών',
    ];
@endphp

<article class="org-unit depth-{{ $depth }}">
    <div class="org-unit-main">
        <div>
            <div class="eyebrow">{{ $typeLabels[$department->org_type] ?? $department->org_type }}</div>
            <h3>{{ $department->name }}</h3>
        </div>
        @if ($assignments->isNotEmpty())
            <span class="pill">{{ $assignments->count() }} {{ $assignments->count() === 1 ? 'άτομο' : 'άτομα' }}</span>
        @endif
    </div>

    @if ($leaders->isNotEmpty())
        <div class="org-people">
            @foreach ($leaders as $assignment)
                @include('portal.organization.partials.person', ['assignment' => $assignment, 'isLeader' => true])
            @endforeach
        </div>
    @endif

    @if ($members->isNotEmpty())
        <div class="org-people">
            @foreach ($members as $assignment)
                @include('portal.organization.partials.person', ['assignment' => $assignment, 'isLeader' => false])
            @endforeach
        </div>
    @endif

    @if ($children->isNotEmpty())
        <div class="org-children">
            @foreach ($children as $child)
                @include('portal.organization.partials.unit', [
                    'department' => $child,
                    'childrenByParent' => $childrenByParent,
                    'assignmentsByDepartment' => $assignmentsByDepartment,
                    'depth' => $depth + 1,
                ])
            @endforeach
        </div>
    @endif
</article>
