@php
    $profile = $assignment->employeeProfile;
    $person = $profile->user;
    $initials = collect(explode(' ', $profile->full_name))->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->join('');
@endphp

<div class="org-person {{ $isLeader ? 'leader' : '' }}">
    <div class="avatar">{{ $initials }}</div>
    <div class="truncate">
        <strong>{{ $profile->full_name }}</strong>
        <div class="muted truncate">{{ $assignment->position?->title ?? $person?->role?->name }}</div>
        @if ($assignment->directManagerProfile)
            <div class="muted truncate" style="font-size:12px;margin-top:3px;">Προϊστάμενος: {{ $assignment->directManagerProfile->full_name }}</div>
        @endif
    </div>
    @if ($isLeader)
        <span class="pill green">Υπεύθυνος</span>
    @endif
</div>
