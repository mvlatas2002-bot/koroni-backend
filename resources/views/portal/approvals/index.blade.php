@php
    $isLeave = ($type ?? null) === 'leave';
    $isDiscount = ($type ?? null) === 'discount';
    $sectionLabel = $isLeave ? 'Άδειες' : ($isDiscount ? 'Εκπτώσεις' : 'Εγκρίσεις');
@endphp

@extends('portal.layout', ['title' => $title . ' | Koroni Portal'])

@section('body')
<div class="shell">
    @include('portal.partials.sidebar', ['user' => $user])

    <main class="content">
        <header class="topbar">
            <div>
                <div class="eyebrow">{{ $sectionLabel }}</div>
                <h1>{{ $title }}</h1>
            </div>
        </header>

        @if (session('status'))
            <div class="notice-success">{{ session('status') }}</div>
        @endif

        <section class="surface">
            <div class="panel-head">
                <div>
                    <div class="eyebrow">{{ $mode === 'pending' ? 'Ουρά απόφασης' : 'Ιστορικό' }}</div>
                    <h2>{{ $requests->count() === 1 ? '1 αίτηση' : $requests->count().' αιτήσεις' }}</h2>
                </div>
            </div>

            @if ($requests->isEmpty())
                <div class="empty">
                    <div>
                        <strong>Καθαρή εικόνα</strong>
                        <p style="margin-top:8px;">
                            {{ $isLeave ? 'Δεν υπάρχουν άδειες σε αυτή την ενότητα.' : ($isDiscount ? 'Δεν υπάρχουν αιτήσεις έκπτωσης σε αυτή την ενότητα.' : 'Δεν υπάρχουν αιτήσεις σε αυτή την ενότητα.') }}
                        </p>
                    </div>
                </div>
            @else
                <div class="compact-list">
                    @foreach ($requests as $approvalRequest)
                        @include('portal.approvals._card', ['approvalRequest' => $approvalRequest])
                    @endforeach
                </div>
            @endif
        </section>
    </main>
</div>
@endsection
