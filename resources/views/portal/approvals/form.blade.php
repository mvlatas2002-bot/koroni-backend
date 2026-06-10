@php
    $selectedWorkflow = old('workflow_type', $type ?? request('type', 'general'));
    $isLeave = $selectedWorkflow === 'leave';
    $isDiscount = $selectedWorkflow === 'discount';
    $pageTitle = match ($selectedWorkflow) {
        'leave' => 'Νέα αίτηση άδειας',
        'discount' => 'Νέα αίτηση έκπτωσης',
        default => 'Νέα αίτηση έγκρισης',
    };
    $pageSubtitle = match ($selectedWorkflow) {
        'leave' => 'Συμπλήρωσε ημερομηνίες και λόγο άδειας. Η ροή ανεβαίνει στον προϊστάμενο και όπου χρειάζεται σε HR/Operations.',
        'discount' => 'Συμπλήρωσε πελάτη, προϊόντα και τιμές. Το ποσοστό υπολογίζεται αυτόματα και το rulebook βρίσκει τον σωστό εγκριτή.',
        default => 'Γενικό αίτημα που χρειάζεται απόφαση από υπεύθυνο.',
    };
@endphp

@extends('portal.layout', ['title' => $pageTitle . ' | Koroni Portal'])

@section('body')
<div class="shell">
    @include('portal.partials.sidebar', ['user' => $user])

    <main class="content">
        <header class="topbar">
            <div>
                <div class="eyebrow">{{ $isLeave ? 'Άδειες' : ($isDiscount ? 'Εκπτώσεις' : 'Εγκρίσεις') }}</div>
                <h1>{{ $pageTitle }}</h1>
                <p class="muted" style="margin-top:10px;font-size:17px;">{{ $pageSubtitle }}</p>
            </div>
        </header>

        @if ($errors->any())
            <div class="error" style="margin-bottom:18px;">{{ $errors->first() }}</div>
        @endif

        <form class="surface approval-form {{ $isLeave ? 'approval-leave' : '' }} {{ $isDiscount ? 'approval-discount' : '' }}" method="post" action="{{ route('portal.approvals.store') }}" data-discount-form>
            @csrf
            <input type="hidden" name="workflow_type" value="{{ $selectedWorkflow }}">

            @if ($isLeave && $leaveCalendar)
                @php
                    $rangeWeekdays = ['Δευ', 'Τρι', 'Τετ', 'Πεμ', 'Παρ', 'Σαβ', 'Κυρ'];
                    $oldStart = old('starts_on');
                    $oldEnd = old('ends_on');
                @endphp

                <section class="leave-request-picker" data-leave-picker data-initial-start="{{ $oldStart }}" data-initial-end="{{ $oldEnd }}">
                    <div class="calendar-toolbar">
                        <a class="button" href="{{ route('portal.approvals.create', ['type' => 'leave', 'month' => $leaveCalendar['previous']->format('Y-m')]) }}">Προηγούμενος</a>
                        <div>
                            <div class="eyebrow">Επιλογή ημερών</div>
                            <h2>{{ $leaveCalendar['month']->translatedFormat('F Y') }}</h2>
                            <p class="muted">Πάτα μία ημέρα για μονοήμερη άδεια ή πάτα αρχή και τέλος για διάστημα.</p>
                        </div>
                        <a class="button" href="{{ route('portal.approvals.create', ['type' => 'leave', 'month' => $leaveCalendar['next']->format('Y-m')]) }}">Επόμενος</a>
                    </div>

                    <div class="leave-picker-summary">
                        <span><strong data-leave-range-label>Δεν έχεις επιλέξει ημέρες</strong><small>Επιλεγμένο διάστημα</small></span>
                        <span><strong data-leave-days-label>0</strong><small>Χρεώσιμες εργάσιμες</small></span>
                        @if ($leaveBalance)
                            <span><strong>{{ number_format($leaveBalance['remaining_now'], 1) }}</strong><small>Διαθέσιμες σήμερα</small></span>
                        @endif
                    </div>

                    <div class="leave-weekdays">
                        @foreach ($rangeWeekdays as $weekday)
                            <span>{{ $weekday }}</span>
                        @endforeach
                    </div>

                    <div class="leave-range-grid">
                        @foreach ($leaveCalendar['weeks'] as $week)
                            @foreach ($week as $day)
                                <button
                                    class="leave-range-day {{ !$day['is_current_month'] ? 'muted-day' : '' }} {{ $day['is_today'] ? 'today' : '' }}"
                                    type="button"
                                    data-date="{{ $day['date'] }}"
                                    data-working="{{ $day['is_working'] ? '1' : '0' }}"
                                    data-note="{{ $day['holiday'] ?: ($day['is_weekend'] ? 'Σαββατοκύριακο' : '') }}"
                                >
                                    <strong>{{ $day['day'] }}</strong>
                                    @if ($day['holiday'])
                                        <span>{{ $day['holiday'] }}</span>
                                    @elseif ($day['is_weekend'])
                                        <span>ΣΚ</span>
                                    @else
                                        <span>εργάσιμη</span>
                                    @endif
                                </button>
                            @endforeach
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($isLeave)
                <div class="portal-grid two-even">
                    <div class="field">
                        <label>Τύπος άδειας</label>
                        <select name="title" required>
                            @foreach ($leaveTypes ?? [] as $leaveType)
                                <option value="{{ $leaveType }}" @selected(old('title', 'Κανονική άδεια') === $leaveType)>{{ $leaveType }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>Σύντομη αιτιολογία</label>
                        <input name="description" value="{{ old('description') }}" placeholder="Προαιρετικά, π.χ. οικογενειακός λόγος">
                    </div>
                    <div class="field">
                        <label>Από</label>
                        <input name="starts_on" type="date" value="{{ old('starts_on') }}" required>
                    </div>
                    <div class="field">
                        <label>Έως</label>
                        <input name="ends_on" type="date" value="{{ old('ends_on') }}" required>
                    </div>
                </div>
            @elseif ($isDiscount)
                <section class="portal-grid two-col">
                    <div class="surface flush">
                        <div class="portal-grid two-even">
                            <div class="field">
                                <label>Ημερομηνία αίτησης</label>
                                <input name="request_date" type="date" value="{{ old('request_date', now()->toDateString()) }}" required>
                            </div>
                            <div class="field">
                                <label>Επωνυμία πελάτη</label>
                                <input name="customer_name" value="{{ old('customer_name') }}" placeholder="π.χ. Κρήτη Market" required>
                            </div>
                            <div class="field">
                                <label>Κωδικός πελάτη</label>
                                <input name="customer_code" value="{{ old('customer_code') }}" placeholder="π.χ. CUST-10045" required>
                                <small>Χρησιμοποιείται για αναζήτηση και έλεγχο ιστορικού.</small>
                            </div>
                            <div class="field">
                                <label>Κατηγορία λόγου</label>
                                <select name="reason_category" required>
                                    <option value="">Επιλογή κατηγορίας</option>
                                    @foreach ($reasonCategories as $value => $label)
                                        <option value="{{ $value }}" @selected(old('reason_category') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field">
                                <label>Κανονική τιμή (€)</label>
                                <input name="regular_price" type="number" min="0" step="0.01" value="{{ old('regular_price') }}" placeholder="0.00" data-regular-price required>
                            </div>
                            <div class="field">
                                <label>Ζητούμενη τιμή (€)</label>
                                <input name="requested_price" type="number" min="0" step="0.01" value="{{ old('requested_price') }}" placeholder="0.00" data-requested-price required>
                                <small>Δεν μπορεί να είναι μεγαλύτερη από την κανονική τιμή.</small>
                            </div>
                        </div>

                        <div class="field">
                            <label>Σύνοψη προϊόντων</label>
                            <textarea name="product_summary" rows="4" placeholder="Προϊόντα, ποσότητες, συσκευασίες ή άλλο εμπορικό πλαίσιο που χρειάζεται ο εγκριτής." required>{{ old('product_summary') }}</textarea>
                        </div>

                        <div class="portal-grid two-even">
                            <div class="field">
                                <label>Extra note / αιτιολόγηση</label>
                                <textarea name="reason" rows="4" placeholder="Σύντομη εξήγηση για το συγκεκριμένο αίτημα.">{{ old('reason') }}</textarea>
                            </div>
                            <div class="field">
                                <label>Εσωτερικό σχόλιο</label>
                                <textarea name="comments" rows="4" placeholder="Προαιρετικά σχόλια για εγκριτές ή operations.">{{ old('comments') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <aside class="surface">
                        <div class="panel-head">
                            <div>
                                <div class="eyebrow">Γρήγορη εικόνα</div>
                                <h2>Υπολογισμός και routing</h2>
                            </div>
                        </div>

                        <div class="compact-list">
                            <div class="list-item">
                                <strong>Υπολογισμένη έκπτωση</strong>
                                <div class="muted" style="margin-top:6px;font-size:26px;font-weight:950;color:var(--ink);" data-discount-preview>0.00%</div>
                            </div>
                            <div class="list-item">
                                <strong>Επόμενος έλεγχος</strong>
                                <div class="muted" style="margin-top:6px;" data-routing-preview>Συμπλήρωσε τιμές</div>
                            </div>
                            <div class="list-item">
                                <strong>Κανόνας</strong>
                                <div class="muted" style="margin-top:6px;">Rulebook εκπτώσεων</div>
                            </div>
                        </div>
                    </aside>
                </section>
            @else
                <div class="portal-grid two-even">
                    <div class="field">
                        <label>Τίτλος</label>
                        <input name="title" value="{{ old('title') }}" required>
                    </div>
                    <div class="field">
                        <label>Ποσό</label>
                        <input name="amount" type="number" min="0" step="0.01" value="{{ old('amount') }}" placeholder="Προαιρετικά">
                    </div>
                </div>
                <div class="field">
                    <label>Περιγραφή</label>
                    <textarea name="description" rows="5">{{ old('description') }}</textarea>
                </div>
            @endif

            <div class="action-row" style="margin-top:22px;">
                @if ($isDiscount)
                    <button class="button" type="submit" name="intent" value="draft">Αποθήκευση ως προσχέδιο</button>
                    <button class="button primary-action" type="submit" name="intent" value="submit">Υποβολή για έγκριση</button>
                @else
                    <button class="button primary-action" type="submit">
                        {{ $isLeave ? 'Υποβολή άδειας' : 'Υποβολή αίτησης' }}
                    </button>
                @endif
            </div>
        </form>
    </main>
</div>

@if ($isDiscount)
    <script>
        (() => {
            const form = document.querySelector('[data-discount-form]');
            if (!form) return;

            const regular = form.querySelector('[data-regular-price]');
            const requested = form.querySelector('[data-requested-price]');
            const discountPreview = form.querySelector('[data-discount-preview]');
            const routingPreview = form.querySelector('[data-routing-preview]');

            const update = () => {
                const regularValue = Number(regular.value || 0);
                const requestedValue = Number(requested.value || 0);
                let discount = 0;

                if (regularValue > 0 && requestedValue > 0 && requestedValue <= regularValue) {
                    discount = ((regularValue - requestedValue) / regularValue) * 100;
                }

                discountPreview.textContent = `${discount.toFixed(2)}%`;

                if (!regularValue || !requestedValue) {
                    routingPreview.textContent = 'Συμπλήρωσε τιμές';
                } else if (requestedValue > regularValue) {
                    routingPreview.textContent = 'Η ζητούμενη τιμή είναι μεγαλύτερη από την κανονική';
                } else if (discount <= 4) {
                    routingPreview.textContent = 'Αυτόματη έγκριση πωλητή';
                } else if (discount < 15) {
                    routingPreview.textContent = 'Εμπορική έγκριση';
                } else {
                    routingPreview.textContent = 'Έγκριση διοίκησης';
                }
            };

            regular.addEventListener('input', update);
            requested.addEventListener('input', update);
            update();
        })();
    </script>
@endif
@endsection
