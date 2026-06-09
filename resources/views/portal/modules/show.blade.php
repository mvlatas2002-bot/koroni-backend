@extends('portal.layout', ['title' => $module['title'] . ' | Koroni Portal'])

@section('body')
<div class="shell">
    @include('portal.partials.sidebar', ['user' => $user])

    <main class="content">
        <header class="topbar">
            <div>
                <div class="eyebrow">{{ $module['category'] }}</div>
                <h1>{{ $module['title'] }}</h1>
                <p class="muted" style="margin-top:10px;font-size:17px;">{{ $module['summary'] }}</p>
            </div>
            <a class="button" href="{{ route('portal.modules.index') }}">Όλες οι λειτουργίες</a>
        </header>

        @if ($moduleKey === 'APPROVALS')
            <section class="panel">
                <div class="panel-head">
                    <div>
                        <div class="eyebrow">Εγκρίσεις</div>
                        <h2>Αιτήσεις και αποφάσεις</h2>
                    </div>
                    <span class="info-dot" data-tip="Οι αιτήσεις κινούνται με βάση προϊστάμενο, ρόλο και κανόνες έγκρισης. Ο χρήστης δεν χρειάζεται να βλέπει τεχνικά βήματα εδώ.">?</span>
                </div>
                <div class="action-row">
                    <a class="button" href="{{ route('portal.approvals.create') }}">Νέα αίτηση</a>
                    <a class="button" href="{{ route('portal.approvals.index') }}">Οι αιτήσεις μου</a>
                    <a class="button" href="{{ route('portal.approvals.pending') }}">Προς έγκριση</a>
                </div>
            </section>
        @else
            <section class="panel">
                <div class="empty">
                    <div>
                        <strong>Η λειτουργία θα χτιστεί βήμα-βήμα</strong>
                        <p style="margin-top:8px;">Θα μεταφέρουμε εδώ μόνο ό,τι είναι πραγματικά χρήσιμο από το παλιό portal.</p>
                    </div>
                </div>
            </section>
        @endif
    </main>
</div>
@endsection
