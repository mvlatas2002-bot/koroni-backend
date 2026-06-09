@extends('portal.layout', ['title' => 'Λειτουργίες | Koroni Portal'])

@section('body')
<div class="shell">
    @include('portal.partials.sidebar', ['user' => $user])

    <main class="content">
        <header class="topbar">
            <div>
                <div class="eyebrow">Λειτουργίες</div>
                <h1>Τι μπορεί να κάνει το portal</h1>
                <p class="muted" style="margin-top:10px;font-size:17px;">
                    Οι βασικές περιοχές εργασίας, χωρίς τεχνικές λεπτομέρειες και εσωτερικούς μετρητές.
                </p>
            </div>
            <span class="info-dot" data-tip="Τα τεχνικά registry στοιχεία μένουν στον κώδικα. Εδώ φαίνεται μόνο ό,τι έχει νόημα για τον άνθρωπο που δουλεύει στο portal.">?</span>
        </header>

        <section class="portal-grid three-col">
            @foreach ($modules->where('included', true) as $module)
                <a class="panel" href="{{ route('portal.modules.show', strtolower($module['key'])) }}">
                    <div class="eyebrow">{{ $module['category'] }}</div>
                    <h2 style="margin-top:8px;">{{ $module['title'] }}</h2>
                    <p class="muted" style="margin-top:10px;line-height:1.55;">{{ $module['summary'] }}</p>
                    <span class="pill" style="margin-top:18px;">Άνοιγμα</span>
                </a>
            @endforeach
        </section>
    </main>
</div>
@endsection
