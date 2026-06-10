@php
    $month = $calendar['month'];
    $monthLabel = $month->translatedFormat('F Y');
    $weekdays = ['Δευ', 'Τρι', 'Τετ', 'Πεμ', 'Παρ', 'Σαβ', 'Κυρ'];
    $balance = $myBalance;
@endphp

@extends('portal.layout', ['title' => 'Ημερολόγιο αδειών | Koroni Portal'])

@section('body')
<div class="shell">
    @include('portal.partials.sidebar', ['user' => $user])

    <main class="content leave-calendar-page">
        <header class="topbar">
            <div>
                <div class="eyebrow">Άδειες</div>
                <h1>Ημερολόγιο αδειών</h1>
                <p class="muted" style="margin-top:10px;font-size:17px;">
                    {{ $canSeeCompanyCalendar ? 'Εταιρική εικόνα αδειών, αργιών και γενεθλίων.' : 'Βλέπεις τις άδειες του τμήματός σου, αργίες και γενέθλια.' }}
                </p>
            </div>
        </header>

        @if (session('status'))
            <div class="surface notice-success">{{ session('status') }}</div>
        @endif

        <section class="leave-hero">
            <div>
                <div class="eyebrow">Υπόλοιπο {{ $balance['year'] }}</div>
                <h2>{{ number_format($balance['remaining_now'], 1) }} ημέρες διαθέσιμες</h2>
                <p class="muted">Οι ημέρες χρεώνονται μόνο όταν φτάνει η ημερομηνία της εγκεκριμένης άδειας.</p>
            </div>

            <div class="leave-balance-strip">
                <span><strong>{{ number_format($balance['total_entitlement'], 1) }}</strong> ετήσιες</span>
                <span><strong>{{ number_format($balance['used_to_date'], 1) }}</strong> χρεωμένες</span>
                <span><strong>{{ number_format($balance['future_scheduled'], 1) }}</strong> μελλοντικές</span>
            </div>
        </section>

        <section class="surface leave-calendar-surface">
            <div class="calendar-toolbar">
                <a class="button" href="{{ route('portal.leave-calendar.index', ['month' => $calendar['previous']->format('Y-m')]) }}">Προηγούμενος</a>
                <div>
                    <div class="eyebrow">Μήνας</div>
                    <h2>{{ $monthLabel }}</h2>
                </div>
                <a class="button" href="{{ route('portal.leave-calendar.index', ['month' => $calendar['next']->format('Y-m')]) }}">Επόμενος</a>
            </div>

            <div class="leave-weekdays">
                @foreach ($weekdays as $weekday)
                    <span>{{ $weekday }}</span>
                @endforeach
            </div>

            <div class="leave-calendar-grid">
                @foreach ($calendar['weeks'] as $week)
                    @foreach ($week as $day)
                        @php
                            $events = $eventsByDate->get($day['date'], collect());
                        @endphp
                        <article class="leave-day {{ !$day['is_current_month'] ? 'muted-day' : '' }} {{ $day['is_today'] ? 'today' : '' }} {{ $day['is_weekend'] ? 'weekend' : '' }}">
                            <div class="leave-day-head">
                                <strong>{{ $day['day'] }}</strong>
                                @if ($day['holiday'])
                                    <span class="calendar-chip holiday">Αργία</span>
                                @elseif ($day['is_weekend'])
                                    <span class="calendar-chip quiet">ΣΚ</span>
                                @endif
                            </div>

                            @if ($day['holiday'])
                                <div class="calendar-event holiday">{{ $day['holiday'] }}</div>
                            @endif

                            @foreach ($events->take(4) as $event)
                                @if ($event['type'] === 'holiday')
                                    @continue
                                @endif

                                <div class="calendar-event {{ $event['type'] }} {{ $event['status'] }}">
                                    <strong>{{ $event['title'] }}</strong>
                                    <span>{{ $event['meta'] }}</span>
                                </div>
                            @endforeach

                            @if ($events->count() > 4)
                                <div class="calendar-more">+{{ $events->count() - 4 }} ακόμα</div>
                            @endif
                        </article>
                    @endforeach
                @endforeach
            </div>
        </section>

        @if ($canManageBalances)
            <section class="surface leave-admin-surface">
                <div class="panel-head">
                    <div>
                        <div class="eyebrow">Operations / Λογιστήριο</div>
                        <h2>Υπόλοιπα αδειών</h2>
                    </div>
                    <span class="pill">{{ $balanceRows->count() }} χρήστες</span>
                </div>

                <div class="leave-balance-table">
                    @foreach ($balanceRows as $row)
                        @php
                            $rowUser = $row['user'];
                            $rowBalance = $row['balance'];
                        @endphp
                        <form method="post" action="{{ route('portal.leave-balances.update', $rowUser) }}" class="leave-balance-row">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="year" value="{{ $rowBalance['year'] }}">

                            <div class="leave-person">
                                <strong>{{ $rowUser->name }}</strong>
                                <span>{{ $rowUser->department?->name ?? 'Χωρίς τμήμα' }}</span>
                            </div>

                            <label>
                                <span>Ετήσιες</span>
                                <input name="annual_entitlement" type="number" step="0.5" min="0" max="99" value="{{ $rowBalance['annual_entitlement'] }}">
                            </label>

                            <label>
                                <span>Διόρθωση</span>
                                <input name="manual_adjustment" type="number" step="0.5" min="-99" max="99" value="{{ $rowBalance['manual_adjustment'] }}">
                            </label>

                            <div class="leave-mini-metrics">
                                <span>{{ number_format($rowBalance['used_to_date'], 1) }} χρεωμένες</span>
                                <strong>{{ number_format($rowBalance['remaining_now'], 1) }} υπόλοιπο</strong>
                            </div>

                            <button class="button" type="submit">Αποθήκευση</button>
                        </form>
                    @endforeach
                </div>
            </section>
        @endif
    </main>
</div>
@endsection
