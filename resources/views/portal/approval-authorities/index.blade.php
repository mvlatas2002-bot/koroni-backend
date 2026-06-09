@extends('portal.layout', ['title' => 'Κανόνες εκπτώσεων | Koroni Portal'])

@php
    $authorityLabels = [
        'functional_approver' => 'Λειτουργικός εγκριτής',
        'management' => 'Διοίκηση',
        'role_based' => 'Role based',
    ];
@endphp

@section('body')
<div class="shell">
    @include('portal.partials.sidebar', ['user' => $user])

    <main class="content">
        <header class="topbar">
            <div>
                <div class="eyebrow">Rule book</div>
                <h1>Κανόνες εγκρίσεων εκπτώσεων</h1>
                <p class="muted" style="margin-top:10px;font-size:17px;">
                    Από εδώ ο admin αλλάζει ποιος εγκρίνει κάθε ποσοστό έκπτωσης, χωρίς αλλαγή κώδικα.
                </p>
            </div>
        </header>

        @if (session('status'))
            <div class="surface notice-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="error" style="margin-bottom:18px;">{{ $errors->first() }}</div>
        @endif

        <section class="surface">
            <div class="panel-head">
                <div>
                    <div class="eyebrow">Νέος κανόνας</div>
                    <h2>Προσθήκη γραμμής approval matrix</h2>
                </div>
                <span class="info-dot" data-tip="Αν δεν ορίσεις συγκεκριμένο εγκριτή, το σύστημα θα ψάξει ενεργό χρήστη με τον επιλεγμένο ρόλο.">?</span>
            </div>

            <form method="post" action="{{ route('portal.approval-authorities.store') }}" class="portal-grid five-col">
                @csrf
                @include('portal.approval-authorities.partials.fields', ['rule' => null])
                <div class="unit-submit">
                    <button class="button primary-action" type="submit">Προσθήκη</button>
                </div>
            </form>
        </section>

        <section class="surface unit-list">
            <div class="panel-head">
                <div>
                    <div class="eyebrow">Ενεργοί και ανενεργοί κανόνες</div>
                    <h2>Τρέχον rulebook</h2>
                </div>
                <span class="pill">{{ $rules->count() }} κανόνες</span>
            </div>

            <div class="unit-rows">
                @foreach ($rules as $rule)
                    <form method="post" action="{{ route('portal.approval-authorities.update', $rule) }}" class="unit-row">
                        @csrf
                        @method('PUT')
                        @include('portal.approval-authorities.partials.fields', ['rule' => $rule])
                        <div class="unit-meta">
                            <span>{{ $rule->is_active ? 'Ενεργός' : 'Ανενεργός' }}</span>
                            <span>{{ $rule->approver?->name ?? ($rule->required_role_code ?: 'Χωρίς εγκριτή') }}</span>
                        </div>
                        <button class="button" type="submit">Αποθήκευση</button>
                    </form>
                @endforeach
            </div>
        </section>
    </main>
</div>
@endsection
