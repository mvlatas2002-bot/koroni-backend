@extends('portal.layout', ['title' => 'Το προφίλ μου | Koroni Portal'])

@php
    $initials = collect(preg_split('/\s+/u', $user->name) ?: [])
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->join('');
@endphp

@section('body')
<div class="shell">
    @include('portal.partials.sidebar', ['user' => $user])

    <main class="content profile-page">
        <header class="topbar">
            <div>
                <div class="eyebrow">Προσωπικός χώρος</div>
                <h1>Το προφίλ μου</h1>
                <p class="muted" style="margin-top:10px;font-size:17px;">
                    Εδώ αλλάζεις μόνο προσωπικά στοιχεία. Ρόλος, τμήμα, θέση και προϊστάμενος μένουν οργανωτικά στοιχεία.
                </p>
            </div>
        </header>

        @if (session('status'))
            <div class="surface notice-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="error" style="margin-bottom:18px;">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="profile-layout">
            <aside class="profile-identity">
                <div class="profile-orb">{{ $initials }}</div>
                <h2>{{ $user->name }}</h2>
                <p>{{ $user->role?->name ?? 'Χωρίς ρόλο' }}</p>

                <div class="profile-facts">
                    <div>
                        <span>Τμήμα</span>
                        <strong>{{ $user->department?->name ?? 'Δεν έχει οριστεί' }}</strong>
                    </div>
                    <div>
                        <span>Θέση</span>
                        <strong>{{ $user->position?->title ?? 'Δεν έχει οριστεί' }}</strong>
                    </div>
                    <div>
                        <span>Προϊστάμενος</span>
                        <strong>{{ $user->manager?->name ?? 'Δεν έχει οριστεί' }}</strong>
                    </div>
                    <div>
                        <span>Email σύνδεσης</span>
                        <strong>{{ $user->email }}</strong>
                    </div>
                </div>
            </aside>

            <form class="profile-form" method="POST" action="{{ route('portal.profile.update') }}">
                @csrf
                @method('PUT')

                <div class="profile-section">
                    <div>
                        <div class="eyebrow">Στοιχεία</div>
                        <h2>Προσωπική εικόνα</h2>
                    </div>
                    <div class="portal-grid two-even">
                        <div class="field">
                            <label>Ονοματεπώνυμο</label>
                            <input name="name" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="field">
                            <label>Email</label>
                            <input value="{{ $user->email }}" disabled>
                            <small>Το email είναι σταθερό αναγνωριστικό χρήστη.</small>
                        </div>
                        <div class="field">
                            <label>Όνομα</label>
                            <input name="first_name" value="{{ old('first_name', $user->first_name) }}">
                        </div>
                        <div class="field">
                            <label>Επώνυμο</label>
                            <input name="last_name" value="{{ old('last_name', $user->last_name) }}">
                        </div>
                        <div class="field">
                            <label>Τηλέφωνο</label>
                            <input name="phone" value="{{ old('phone', $user->phone) }}" placeholder="π.χ. 69...">
                        </div>
                        <div class="field">
                            <label>Ημερομηνία γέννησης</label>
                            <input name="birth_date" type="date" value="{{ old('birth_date', optional($user->birth_date)->format('Y-m-d')) }}">
                        </div>
                    </div>
                </div>

                <div class="profile-section">
                    <div>
                        <div class="eyebrow">Ασφάλεια</div>
                        <h2>Αλλαγή κωδικού</h2>
                    </div>
                    <div class="portal-grid two-even">
                        <div class="field">
                            <label>Τρέχων κωδικός</label>
                            <input name="current_password" type="password" autocomplete="current-password">
                        </div>
                        <div class="field">
                            <label>Νέος κωδικός</label>
                            <input name="new_password" type="password" autocomplete="new-password">
                        </div>
                        <div class="field">
                            <label>Επιβεβαίωση νέου κωδικού</label>
                            <input name="new_password_confirmation" type="password" autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <div class="profile-section">
                    <div>
                        <div class="eyebrow">Έκτακτη ανάγκη</div>
                        <h2>Χρήσιμες πληροφορίες</h2>
                    </div>
                    <div class="portal-grid two-even">
                        <div class="field">
                            <label>Επαφή ανάγκης</label>
                            <input name="emergency_contact_name" value="{{ old('emergency_contact_name', $user->emergency_contact_name) }}">
                        </div>
                        <div class="field">
                            <label>Τηλέφωνο επαφής</label>
                            <input name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $user->emergency_contact_phone) }}">
                        </div>
                        <div class="field" style="grid-column:1 / -1;">
                            <label>Σημειώσεις προφίλ</label>
                            <input name="profile_notes" value="{{ old('profile_notes', $user->profile_notes) }}" placeholder="Προαιρετικές χρήσιμες πληροφορίες">
                        </div>
                    </div>
                </div>

                <button class="button primary-action" type="submit">Αποθήκευση προφίλ</button>
            </form>
        </section>
    </main>
</div>
@endsection
