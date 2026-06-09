@extends('portal.layout', ['title' => 'Dashboard | Koroni Portal'])

@php
    $displayName = $user->first_name ?: $user->name;
    $managerName = $user->manager?->name ?? 'Δεν έχει οριστεί';
    $teamPreview = $team->take(4);
@endphp

@section('body')
<div class="shell">
    @include('portal.partials.sidebar', ['user' => $user])

    <main class="content">
        <header class="topbar">
            <div>
                <div class="eyebrow">Σήμερα</div>
                <h1>Καλώς ήρθες, {{ $displayName }}</h1>
                <p class="muted" style="margin-top:10px;font-size:17px;">
                    Γρήγορη εικόνα για όσα χρειάζονται κίνηση μέσα στην ημέρα.
                </p>
            </div>
        </header>

        <section class="portal-grid three-col">
            <article class="panel">
                <div class="panel-head">
                    <div>
                        <div class="eyebrow">Ενέργειες</div>
                        <h2>Άμεση προσοχή</h2>
                    </div>
                </div>
                <div class="compact-list">
                    <a class="list-item" href="{{ route('portal.approvals.pending') }}">
                        <div class="list-row">
                            <div class="truncate">
                                <strong>Εκκρεμείς εγκρίσεις</strong>
                                <div class="muted truncate">Όσα περιμένουν απόφαση από εσένα ή την ομάδα σου.</div>
                            </div>
                            <span class="pill amber">Άνοιγμα</span>
                        </div>
                    </a>
                    <a class="list-item" href="{{ route('portal.approvals.create') }}">
                        <div class="list-row">
                            <div class="truncate">
                                <strong>Νέα αίτηση</strong>
                                <div class="muted truncate">Άδεια, έκπτωση ή γενική έγκριση.</div>
                            </div>
                            <span class="pill">+</span>
                        </div>
                    </a>
                </div>
            </article>

            <article class="panel">
                <div class="panel-head">
                    <div>
                        <div class="eyebrow">Η ομάδα μου</div>
                        <h2>Άμεση εικόνα</h2>
                    </div>
                </div>
                @if ($teamPreview->isNotEmpty())
                    <div class="compact-list">
                        @foreach ($teamPreview as $member)
                            <div class="list-item">
                                <strong>{{ $member->name }}</strong>
                                <div class="muted truncate" style="margin-top:5px;">{{ $member->position?->title ?? $member->role?->name }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty">
                        <div>
                            <strong>Δεν υπάρχει ομάδα</strong>
                            <p style="margin-top:8px;">Θα εμφανιστεί όταν οριστούν προϊστάμενοι.</p>
                        </div>
                    </div>
                @endif
            </article>

            <article class="panel">
                <div class="panel-head">
                    <div>
                        <div class="eyebrow">Ροή εγκρίσεων</div>
                        <h2>Ποιος αποφασίζει</h2>
                    </div>
                </div>
                <div class="compact-list">
                    <div class="list-item">
                        <span class="muted">Προϊστάμενος</span>
                        <h3 style="margin-top:6px;">{{ $managerName }}</h3>
                    </div>
                    <div class="list-item">
                        <span class="muted">Δεύτερη έγκριση</span>
                        <h3 style="margin-top:6px;">{{ $user->secondaryApprover?->name ?? 'Δεν έχει οριστεί' }}</h3>
                    </div>
                </div>
            </article>
        </section>

        <section class="panel" style="margin-top:18px;">
            <div class="panel-head">
                <div>
                    <div class="eyebrow">Οργάνωση</div>
                    <h2>Θέση και τμήμα</h2>
                </div>
                <a class="button" href="{{ route('portal.users.index') }}">Χρήστες</a>
            </div>
            <div class="portal-grid three-col">
                <div class="list-item">
                    <span class="muted">Ρόλος</span>
                    <h3 style="margin-top:6px;">{{ $user->role?->name ?? 'Δεν έχει οριστεί' }}</h3>
                </div>
                <div class="list-item">
                    <span class="muted">Τμήμα</span>
                    <h3 style="margin-top:6px;">{{ $user->department?->name ?? 'Δεν έχει οριστεί' }}</h3>
                </div>
                <div class="list-item">
                    <span class="muted">Θέση</span>
                    <h3 style="margin-top:6px;">{{ $user->position?->title ?? 'Δεν έχει οριστεί' }}</h3>
                </div>
            </div>
        </section>
    </main>
</div>
@endsection
