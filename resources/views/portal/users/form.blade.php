@extends('portal.layout', ['title' => ($mode === 'create' ? 'Νέος χρήστης' : 'Επεξεργασία χρήστη') . ' | Koroni Portal'])

@section('body')
<div class="shell">
    @include('portal.partials.sidebar', ['user' => $user])

    <main class="content">
        <header class="topbar">
            <div>
                <div class="eyebrow">Οργάνωση</div>
                <h1>{{ $mode === 'create' ? 'Νέος χρήστης' : 'Επεξεργασία χρήστη' }}</h1>
                <p class="muted" style="margin-top:10px;font-size:17px;">
                    Τα στοιχεία εδώ επηρεάζουν πραγματικά πρόσβαση, οργανόγραμμα και ροές εγκρίσεων.
                </p>
            </div>
            <a class="button" href="{{ route('portal.users.index') }}">Πίσω</a>
        </header>

        @if ($errors->any())
            <div class="error" style="margin-bottom:18px;">
                {{ $errors->first() }}
            </div>
        @endif

        <form class="surface user-form-shell" method="POST" action="{{ $mode === 'create' ? route('portal.users.store') : route('portal.users.update', $portalUser) }}">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <input type="hidden" name="employment_status" value="{{ old('employment_status', $portalUser->employment_status ?? 'active') }}">
            <input type="hidden" name="is_active" value="1">

            <section class="profile-section">
                <div class="panel-head">
                    <div>
                        <div class="eyebrow">Βασικά στοιχεία</div>
                        <h2>Ποιος είναι ο χρήστης</h2>
                    </div>
                </div>

                <div class="portal-grid two-even">
                    <div class="field">
                        <label>Ονοματεπώνυμο</label>
                        <input name="name" value="{{ old('name', $portalUser->name) }}" required autocomplete="name">
                    </div>
                    <div class="field">
                        <label>Email σύνδεσης</label>
                        <input name="email" type="email" value="{{ old('email', $portalUser->email) }}" required autocomplete="email">
                    </div>
                    <div class="field">
                        <label>Όνομα</label>
                        <input name="first_name" value="{{ old('first_name', $portalUser->first_name) }}">
                    </div>
                    <div class="field">
                        <label>Επώνυμο</label>
                        <input name="last_name" value="{{ old('last_name', $portalUser->last_name) }}">
                    </div>
                    <div class="field">
                        <label>
                            Κωδικός
                            @if ($mode === 'edit')
                                <span class="info-dot" data-tip="Άφησέ το κενό αν δεν θέλεις αλλαγή κωδικού.">?</span>
                            @endif
                        </label>
                        <input name="password" type="password" value="" @required($mode === 'create') autocomplete="new-password">
                    </div>
                </div>
            </section>

            <section class="profile-section">
                <div class="panel-head">
                    <div>
                        <div class="eyebrow">Ρόλος και θέση</div>
                        <h2>Πού ανήκει</h2>
                    </div>
                    <span class="info-dot" data-tip="Ο ρόλος δίνει πρόσβαση. Το τμήμα και η θέση τροφοδοτούν οργανόγραμμα, λίστες και εγκρίσεις.">?</span>
                </div>

                <div class="portal-grid three-col">
                    <div class="field">
                        <label>Ρόλος πρόσβασης</label>
                        <select name="role_id" required>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected((int) old('role_id', $portalUser->role_id) === $role->id)>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label>Οργανωτική μονάδα</label>
                        <select name="department_id" data-department-select>
                            <option value="">Χωρίς οργανωτική μονάδα</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected((int) old('department_id', $portalUser->department_id) === $department->id)>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                        <small>Εμφανίζονται διευθύνσεις, τμήματα και ομάδες. Δεν εμφανίζεται ο κεντρικός κόμβος εταιρείας.</small>
                    </div>

                    <div class="field">
                        <label>Θέση</label>
                        <select name="position_id" data-position-select>
                            <option value="">Χωρίς θέση</option>
                            @foreach ($positions as $position)
                                <option value="{{ $position->id }}" data-department-id="{{ $position->department_id }}" @selected((int) old('position_id', $portalUser->position_id) === $position->id)>
                                    {{ $position->title }} @if($position->department) - {{ $position->department->name }} @endif
                                </option>
                            @endforeach
                        </select>
                        <small>Η λίστα θέσεων μαζεύεται αυτόματα με βάση το τμήμα.</small>
                    </div>
                </div>
            </section>

            <section class="profile-section">
                <div class="panel-head">
                    <div>
                        <div class="eyebrow">Εγκρίσεις</div>
                        <h2>Ποιος αποφασίζει</h2>
                    </div>
                    <span class="info-dot" data-tip="Ο προϊστάμενος είναι το πρώτο επίπεδο έγκρισης. Ο αντικαταστάτης χρησιμοποιείται όταν χρειάζεται προσωρινή κάλυψη.">?</span>
                </div>

                <div class="portal-grid three-col">
                    <div class="field">
                        <label>Προϊστάμενος</label>
                        <select name="manager_id">
                            <option value="">Δεν έχει οριστεί</option>
                            @foreach ($managers as $manager)
                                <option value="{{ $manager->id }}" @selected((int) old('manager_id', $portalUser->manager_id) === $manager->id)>
                                    {{ $manager->name }} @if($manager->position) - {{ $manager->position->title }} @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label>Δεύτερη έγκριση</label>
                        <select name="secondary_approver_id">
                            <option value="">Δεν έχει οριστεί</option>
                            @foreach ($managers as $manager)
                                <option value="{{ $manager->id }}" @selected((int) old('secondary_approver_id', $portalUser->secondary_approver_id) === $manager->id)>
                                    {{ $manager->name }} @if($manager->position) - {{ $manager->position->title }} @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label>Αντικαταστάτης</label>
                        <select name="acting_manager_id">
                            <option value="">Δεν έχει οριστεί</option>
                            @foreach ($managers as $manager)
                                <option value="{{ $manager->id }}" @selected((int) old('acting_manager_id', $portalUser->acting_manager_id) === $manager->id)>
                                    {{ $manager->name }} @if($manager->department) - {{ $manager->department->name }} @endif
                                </option>
                            @endforeach
                        </select>
                        <small>Κάθε νέος ενεργός χρήστης εμφανίζεται εδώ μετά την αποθήκευση.</small>
                    </div>
                </div>
            </section>

            <button class="button primary-action" type="submit">
                Αποθήκευση χρήστη
            </button>
        </form>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const departmentSelect = document.querySelector('[data-department-select]');
        const positionSelect = document.querySelector('[data-position-select]');

        if (!departmentSelect || !positionSelect) {
            return;
        }

        const options = Array.from(positionSelect.options);

        const refreshPositions = () => {
            const departmentId = departmentSelect.value;
            const currentValue = positionSelect.value;

            options.forEach((option) => {
                const optionDepartment = option.dataset.departmentId;
                const shouldShow = !option.value || !departmentId || optionDepartment === departmentId;
                option.hidden = !shouldShow;
                option.disabled = !shouldShow;
            });

            const selected = options.find((option) => option.value === currentValue);
            if (selected && selected.disabled) {
                positionSelect.value = '';
            }
        };

        departmentSelect.addEventListener('change', refreshPositions);
        refreshPositions();
    });
</script>
@endsection
