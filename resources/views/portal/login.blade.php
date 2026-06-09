@extends('portal.layout', ['title' => 'Σύνδεση | Koroni Portal'])

@section('body')
<main class="login-shell">
    <section class="login-card">
        <div class="login-hero">
            <div>
                <div class="brand" style="border:0;padding:0;">
                    <div class="brand-mark">KP</div>
                    <div>
                        <strong>Koroni Portal</strong>
                        <span>Operations, approvals και οργανωτική δομή</span>
                    </div>
                </div>
                <div style="margin-top:62px;">
                    <div class="eyebrow" style="color:rgba(255,255,255,.58);">Internal workspace</div>
                    <h1 style="max-width:640px;">Ένα καθαρό portal για την καθημερινή λειτουργία.</h1>
                    <p style="margin-top:18px;max-width:620px;color:rgba(255,255,255,.68);font-size:18px;line-height:1.65;">
                        Χρήστες, ρόλοι, τμήματα, θέσεις και προϊστάμενοι έρχονται από το Laravel backend και τη Neon βάση, όχι από φυτεμένα στοιχεία.
                    </p>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-top:38px;">
                <span class="pill" style="background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.14);color:white;">{{ $userCount }} χρήστες</span>
                <span class="pill" style="background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.14);color:white;">{{ $roleCount }} ρόλοι</span>
                <span class="pill" style="background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.14);color:white;">{{ $departmentCount }} μονάδες</span>
            </div>
        </div>

        <form method="POST" action="{{ route('portal.login.submit') }}" class="login-form">
            @csrf
            <div class="eyebrow">Σύνδεση</div>
            <h2 style="margin-top:8px;">Μπες στο portal</h2>
            <p class="muted" style="margin-top:10px;line-height:1.55;">
                Demo σύνδεση με πραγματικό seeded χρήστη από τη νέα Laravel βάση.
            </p>

            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <div class="field">
                <label>Email</label>
                <input name="email" type="email" value="giannis.kostakis@koronisa.local" required autofocus>
            </div>

            <div class="field">
                <label>Κωδικός</label>
                <input name="password" type="password" value="57" required>
            </div>

            <button class="button" type="submit" style="width:100%;margin-top:22px;background:var(--navy);border-color:var(--navy);color:white;">
                Σύνδεση
            </button>

            <p class="muted" style="margin-top:16px;font-size:13px;line-height:1.55;">
                Προεπιλογή: Γιάννης Κωστάκης, ρόλος πωλητή. Για πλήρη διαχείριση: manos.vlatas@koronisa.local / 50.
            </p>
        </form>
    </section>
</main>
@endsection
