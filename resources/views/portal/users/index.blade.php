@extends('portal.layout', ['title' => 'Χρήστες | Koroni Portal'])

@section('body')
<div class="shell">
    @include('portal.partials.sidebar', ['user' => $user])

    <main class="content">
        <header class="topbar">
            <div>
                <div class="eyebrow">Οργάνωση</div>
                <h1>Χρήστες, θέσεις και προϊστάμενοι</h1>
                <p class="muted" style="margin-top:10px;font-size:17px;">
                    Η βάση για το οργανόγραμμα και τις εγκρίσεις από κάτω προς τα πάνω.
                </p>
            </div>
            <a class="button" href="{{ route('portal.users.create') }}">Νέος χρήστης</a>
        </header>

        @if (session('status'))
            <div class="surface" style="margin-bottom:18px;border-color:#b7efd9;color:var(--green);font-weight:900;">
                {{ session('status') }}
            </div>
        @endif

        <section class="surface">
            <div class="panel-head">
                <div>
                    <div class="eyebrow">Άνθρωποι</div>
                    <h2>Ποιος ανήκει πού</h2>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Χρήστης</th>
                            <th>Ρόλος και θέση</th>
                            <th>Προϊστάμενος</th>
                            <th>Δεύτερη έγκριση</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $portalUser)
                            <tr>
                                <td>
                                    <strong>{{ $portalUser->name }}</strong>
                                    <div class="muted">{{ $portalUser->email }}</div>
                                </td>
                                <td>
                                    <strong>{{ $portalUser->role?->name ?? '-' }}</strong>
                                    <div class="muted">{{ $portalUser->department?->name ?? '-' }} · {{ $portalUser->position?->title ?? '-' }}</div>
                                </td>
                                <td>{{ $portalUser->manager?->name ?? '-' }}</td>
                                <td>{{ $portalUser->secondaryApprover?->name ?? '-' }}</td>
                                <td><a class="button" href="{{ route('portal.users.edit', $portalUser) }}">Επεξεργασία</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
@endsection
