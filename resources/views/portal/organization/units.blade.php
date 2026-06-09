@extends('portal.layout', ['title' => 'Οργανωτικές μονάδες | Koroni Portal'])

@section('body')
<div class="shell">
    @include('portal.partials.sidebar', ['user' => $user])

    <main class="content">
        <header class="topbar">
            <div>
                <div class="eyebrow">Διαχείριση οργάνωσης</div>
                <h1>Οργανωτικές μονάδες</h1>
                <p class="muted" style="margin-top:10px;font-size:17px;">
                    Εδώ αλλάζουν ονομασίες, τύποι και σχέσεις τμημάτων χωρίς κώδικα.
                </p>
            </div>
        </header>

        @if (session('status'))
            <div class="notice-success" style="margin-bottom:18px;">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="error" style="margin-bottom:18px;">{{ $errors->first() }}</div>
        @endif

        <section class="surface user-form-shell">
            <div class="panel-head">
                <div>
                    <div class="eyebrow">Νέα μονάδα</div>
                    <h2>Προσθήκη στη δομή</h2>
                </div>
            </div>

            <form method="POST" action="{{ route('portal.organization.units.store') }}" class="portal-grid five-col">
                @csrf
                <div class="field">
                    <label>Όνομα</label>
                    <input name="name" value="{{ old('name') }}" required>
                </div>
                <div class="field">
                    <label>Κωδικός</label>
                    <input name="code" value="{{ old('code') }}" required placeholder="NEW_TEAM">
                </div>
                <div class="field">
                    <label>Τύπος</label>
                    <select name="org_type" required>
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" @selected(old('org_type', 'TEAM') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Ανήκει κάτω από</label>
                    <select name="parent_id">
                        <option value="">Κορυφαίο επίπεδο</option>
                        @foreach ($parentOptions as $parent)
                            <option value="{{ $parent->id }}" @selected((int) old('parent_id') === $parent->id)>{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field unit-submit">
                    <label>&nbsp;</label>
                    <button class="button primary-action" type="submit">Προσθήκη</button>
                </div>
            </form>
        </section>

        <section class="surface unit-list">
            <div class="panel-head">
                <div>
                    <div class="eyebrow">Υπάρχουσα δομή</div>
                    <h2>Επεξεργασία μονάδων</h2>
                </div>
                <span class="pill">{{ $departments->count() }} μονάδες</span>
            </div>

            <div class="unit-rows">
                @foreach ($departments as $department)
                    <form method="POST" action="{{ route('portal.organization.units.update', $department) }}" class="unit-row">
                        @csrf
                        @method('PUT')

                        <div class="field">
                            <label>Όνομα</label>
                            <input name="name" value="{{ old('name', $department->name) }}" required>
                        </div>
                        <div class="field">
                            <label>Κωδικός</label>
                            <input name="code" value="{{ old('code', $department->code) }}" required>
                        </div>
                        <div class="field">
                            <label>Τύπος</label>
                            <select name="org_type" required>
                                @foreach ($types as $value => $label)
                                    <option value="{{ $value }}" @selected($department->org_type === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Γονέας</label>
                            <select name="parent_id">
                                <option value="">Κορυφαίο επίπεδο</option>
                                @foreach ($parentOptions->where('id', '!=', $department->id) as $parent)
                                    <option value="{{ $parent->id }}" @selected($department->parent_id === $parent->id)>{{ $parent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Κατάσταση</label>
                            <select name="is_active">
                                <option value="1" @selected($department->is_active)>Ενεργή</option>
                                <option value="0" @selected(! $department->is_active)>Ανενεργή</option>
                            </select>
                        </div>
                        <div class="unit-meta">
                            <span>{{ $department->users_count }} χρήστες</span>
                            <span>{{ $department->positions_count }} θέσεις</span>
                        </div>
                        <button class="button" type="submit">Αποθήκευση</button>
                    </form>
                @endforeach
            </div>
        </section>
    </main>
</div>
@endsection
