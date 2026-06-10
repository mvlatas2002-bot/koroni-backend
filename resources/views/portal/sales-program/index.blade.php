@extends('portal.layout', ['title' => 'Πρόγραμμα Πωλητών | Koroni Portal'])

@php
    $isToday = $selectedDateInput === now('Europe/Athens')->format('Y-m-d');
    $activeViewUrl = fn (string $view) => route('portal.sales-program.index', ['view' => $view, 'date' => $selectedDateInput]);
@endphp

@section('body')
<div class="shell sales-program-page">
    @include('portal.partials.sidebar', ['user' => $user])

    <main class="content">
        <header class="topbar">
            <div>
                <div class="eyebrow">Πωλητές</div>
                <h1>Πρόγραμμα πωλητών</h1>
                <p class="muted" style="margin-top:8px;">Ημερήσιες στάσεις, περιοχές και ένδειξη έναρξης πεδίου.</p>
            </div>
        </header>

        @if (session('status'))
            <div class="notice-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="sales-toolbar">
            <div class="sales-segment">
                <a class="{{ $viewMode === 'today' ? 'active' : '' }}" href="{{ $activeViewUrl('today') }}">Πρόγραμμα ημέρας</a>
                <a class="{{ $viewMode === 'plan' ? 'active' : '' }}" href="{{ $activeViewUrl('plan') }}">Πρόγραμμα εβδομάδας</a>
            </div>

            <form method="GET" action="{{ route('portal.sales-program.index') }}" class="sales-date-form">
                <input type="hidden" name="view" value="{{ $viewMode }}">
                <label>
                    Ημερομηνία
                    <input type="date" name="date" value="{{ $selectedDateInput }}" onchange="this.form.submit()">
                </label>
            </form>
        </section>

        @if ($viewMode === 'today')
            <section class="sales-hero panel">
                <div>
                    <div class="eyebrow">{{ $dayLabel }}</div>
                    <h2>{{ $selectedDateLabel }}</h2>
                </div>
                <div class="sales-stat-strip">
                    <span><strong>{{ $stats['reps'] }}</strong> πωλητές</span>
                    <span class="green"><strong>{{ $stats['started'] }}</strong> έχουν ξεκινήσει</span>
                    <span><strong>{{ $stats['stops'] }}</strong> στάσεις</span>
                </div>
            </section>

            @if ($todayCards->isEmpty())
                <section class="panel">
                    <div class="empty">
                        <div>
                            <strong>Καθαρή εικόνα</strong>
                            <p style="margin-top:8px;">Δεν υπάρχει πρόγραμμα πωλητών για αυτή την ημέρα.</p>
                        </div>
                    </div>
                </section>
            @else
                <section class="sales-day-grid">
                    @foreach ($todayCards as $card)
                        @php
                            $rep = $card['rep'];
                            $status = $card['status'];
                            $started = $status?->started_at && ! $status?->ended_at;
                            $ended = $status?->ended_at;
                            $canControlDay = $rep->id === $user->id && $isToday;
                        @endphp

                        <article class="sales-day-card panel">
                            <div class="sales-card-head">
                                <div>
                                    <span class="sales-dot {{ $started ? 'on' : ($ended ? 'done' : '') }}"></span>
                                    <strong>{{ $rep->name }}</strong>
                                    <p class="muted">
                                        @if ($started)
                                            Ξεκίνησε {{ $status->started_at->timezone('Europe/Athens')->format('H:i') }}
                                        @elseif ($ended)
                                            Ολοκληρώθηκε {{ $status->ended_at->timezone('Europe/Athens')->format('H:i') }}
                                        @else
                                            Δεν έχει πατήσει έναρξη
                                        @endif
                                    </p>
                                </div>

                                @if ($canControlDay)
                                    @if (! $status?->started_at || $status?->ended_at)
                                        <form method="POST" action="{{ route('portal.sales-program.day.start') }}">
                                            @csrf
                                            <input type="hidden" name="schedule_date" value="{{ $selectedDateInput }}">
                                            <button class="sales-primary-action" type="submit">Έναρξη ημέρας</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('portal.sales-program.day.end') }}">
                                            @csrf
                                            <input type="hidden" name="schedule_date" value="{{ $selectedDateInput }}">
                                            <button class="button" type="submit">Κλείσιμο</button>
                                        </form>
                                    @endif
                                @endif
                            </div>

                            <div class="sales-stop-list">
                                @foreach ($card['stops'] as $stop)
                                    <div class="sales-stop">
                                        <span>{{ $loop->iteration }}</span>
                                        <div>
                                            <strong>{{ $stop->area }}</strong>
                                            @if ($stop->customer_label)
                                                <p>{{ $stop->customer_label }}</p>
                                            @endif
                                            @if ($stop->note)
                                                <small>{{ $stop->note }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </section>
            @endif
        @else
            <section class="panel">
                <div class="panel-head">
                    <div>
                        <div class="eyebrow">Πλάνο εβδομάδας</div>
                        <h2>Στάσεις ανά πωλητή και ημέρα</h2>
                        <p class="muted" style="margin-top:8px;">Το πρόγραμμα ημέρας χρησιμοποιεί πρώτα ειδικές στάσεις ημερομηνίας και μετά αυτό το εβδομαδιαίο πλάνο.</p>
                    </div>
                </div>

                @if ($canManageAll)
                    <form method="POST" action="{{ route('portal.sales-program.areas.store') }}" class="sales-area-form">
                        @csrf
                        <label>
                            Νέα περιοχή
                            <input name="label" maxlength="120" placeholder="π.χ. Ηράκλειο Κέντρο">
                        </label>
                        <button class="button" type="submit">Προσθήκη</button>
                    </form>
                @endif

                <datalist id="sales-area-options">
                    @foreach ($areaOptions as $area)
                        <option value="{{ $area }}"></option>
                    @endforeach
                </datalist>

                <div class="sales-plan-list">
                    @foreach ($salesReps as $rep)
                        <details class="sales-rep-plan" @if($loop->first) open @endif>
                            <summary>
                                <span>
                                    <strong>{{ $rep->name }}</strong>
                                    <small>{{ $rep->email }}</small>
                                </span>
                                <span>{{ collect($weekdays)->sum(fn ($weekday) => ($templateStops->get($rep->id, collect())->get($weekday, collect()))->count()) }} στάσεις</span>
                            </summary>

                            <div class="sales-week-grid">
                                @foreach ($weekdays as $weekday)
                                    @php
                                        $dayStops = $templateStops->get($rep->id, collect())->get($weekday, collect());
                                        $canEditRep = $canManageAll || $rep->id === $user->id;
                                    @endphp

                                    <div class="sales-week-card">
                                        <div class="sales-week-title">
                                            <strong>{{ $weekday }}</strong>
                                            <span>{{ $dayStops->count() }} στάσεις</span>
                                        </div>

                                        @foreach ($dayStops as $stop)
                                            <form method="POST" action="{{ route('portal.sales-program.stops.store') }}" class="sales-stop-form compact">
                                                @csrf
                                                <input type="hidden" name="stop_id" value="{{ $stop->id }}">
                                                <input type="hidden" name="sales_rep_id" value="{{ $rep->id }}">
                                                <input type="hidden" name="day_label" value="{{ $weekday }}">
                                                <input type="hidden" name="sort_order" value="{{ $stop->sort_order }}">
                                                <input list="sales-area-options" name="area" value="{{ $stop->area }}" maxlength="120" @disabled(! $canEditRep)>
                                                <input name="customer_label" value="{{ $stop->customer_label }}" maxlength="160" placeholder="Πελάτης / σημείο" @disabled(! $canEditRep)>
                                                <input name="note" value="{{ $stop->note }}" maxlength="220" placeholder="Σημείωση" @disabled(! $canEditRep)>
                                                @if ($canEditRep)
                                                    <button class="button" type="submit">Αποθήκευση</button>
                                                @endif
                                            </form>

                                            @if ($canEditRep)
                                                <form method="POST" action="{{ route('portal.sales-program.stops.destroy', $stop) }}" class="sales-delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit">Αφαίρεση στάσης</button>
                                                </form>
                                            @endif
                                        @endforeach

                                        @if ($canEditRep)
                                            <form method="POST" action="{{ route('portal.sales-program.stops.store') }}" class="sales-stop-form">
                                                @csrf
                                                <input type="hidden" name="sales_rep_id" value="{{ $rep->id }}">
                                                <input type="hidden" name="day_label" value="{{ $weekday }}">
                                                <input type="hidden" name="sort_order" value="{{ $dayStops->count() + 1 }}">
                                                <input list="sales-area-options" name="area" maxlength="120" placeholder="Περιοχή" required>
                                                <input name="customer_label" maxlength="160" placeholder="Πελάτης / σημείο">
                                                <input name="note" maxlength="220" placeholder="Σημείωση">
                                                <button class="sales-primary-action" type="submit">Νέα στάση</button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @endforeach
                </div>
            </section>
        @endif
    </main>
</div>
@endsection
