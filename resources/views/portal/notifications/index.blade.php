@extends('portal.layout', ['title' => 'Κέντρο ειδοποιήσεων | Koroni Portal'])

@section('body')
<div class="shell">
    @include('portal.partials.sidebar', ['user' => $user])

    <main class="content notifications-page">
        <header class="topbar">
            <div>
                <div class="eyebrow">Ειδοποιήσεις</div>
                <h1>Κέντρο ειδοποιήσεων</h1>
                <p class="muted" style="margin-top:10px;font-size:17px;">Εδώ συγκεντρώνονται οι εγκρίσεις, αποφάσεις και σημαντικές ενημερώσεις σου.</p>
            </div>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('portal.notifications.mark-all-read') }}">
                    @csrf
                    <button class="button" type="submit">Όλα διαβασμένα</button>
                </form>
            @endif
        </header>

        @if (session('status'))
            <div class="notice-success">{{ session('status') }}</div>
        @endif

        <section class="notification-settings surface">
            <div>
                <div class="eyebrow">Push σε κινητό και υπολογιστή</div>
                <h2>Ενεργοποίηση σε αυτή τη συσκευή</h2>
                <p class="muted">Το καμπανάκι μέσα στο portal μένει πάντα ενεργό. Το push χρειάζεται άδεια από τον browser και ενεργοποιείται ξεχωριστά σε κάθε κινητό ή υπολογιστή.</p>
            </div>

            <div class="notification-settings-actions"
                data-push-panel
                data-push-ready="{{ $pushReady ? '1' : '0' }}"
                data-vapid-public-key="{{ $vapidPublicKey }}"
                data-push-enabled="{{ $preference->push_enabled ? '1' : '0' }}"
            >
                <div class="notification-status" data-push-status>
                    {{ $pushReady ? ($preference->push_enabled ? 'Push ενεργό' : 'Push ανενεργό') : 'Το push δεν έχει ρυθμιστεί ακόμα στο server' }}
                </div>
                <button class="button primary-action" type="button" data-push-enable @disabled(!$pushReady)>Ενεργοποίηση push</button>
                <button class="button" type="button" data-push-disable @disabled(!$preference->push_enabled)>Απενεργοποίηση</button>
                <button class="button" type="button" data-push-test @disabled(!$preference->push_enabled)>Δοκιμή push</button>
            </div>

            <div class="active-devices">
                <strong>{{ $activeDevices->count() }} ενεργές συσκευές</strong>
                @if ($activeDevices->isNotEmpty())
                    <div class="device-list">
                        @foreach ($activeDevices as $device)
                            <span>{{ str($device->user_agent ?: 'Άγνωστη συσκευή')->limit(72) }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <section class="surface">
            <div class="panel-head">
                <div>
                    <div class="eyebrow">Ροή</div>
                    <h2>{{ $unreadCount }} αδιάβαστες</h2>
                </div>
            </div>

            @if ($notifications->isEmpty())
                <div class="empty">
                    <div>
                        <strong>Καθαρή εικόνα</strong>
                        <p style="margin-top:8px;">Δεν υπάρχουν ειδοποιήσεις ακόμα.</p>
                    </div>
                </div>
            @else
                <div class="notification-list">
                    @foreach ($notifications as $notification)
                        <a class="notification-row {{ $notification->is_read ? '' : 'unread' }}" href="{{ route('portal.notifications.open', $notification) }}">
                            <span class="notification-dot"></span>
                            <span>
                                <strong>{{ $notification->title }}</strong>
                                <small>{{ $notification->message }}</small>
                            </span>
                            <time>{{ $notification->created_at->diffForHumans() }}</time>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    </main>
</div>
@endsection
