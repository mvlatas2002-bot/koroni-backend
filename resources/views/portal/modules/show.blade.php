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
        </header>

        @if ($moduleKey === 'APPROVALS')
            <section class="panel">
                <div class="panel-head">
                    <div>
                        <div class="eyebrow">Εγκρίσεις</div>
                        <h2>Αιτήσεις και αποφάσεις</h2>
                    </div>
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
