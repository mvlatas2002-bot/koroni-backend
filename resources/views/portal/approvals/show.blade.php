@extends('portal.layout', ['title' => $approvalRequest->request_code])

@php
    $statusLabels = [
        'draft' => 'Προσχέδιο',
        'pending' => 'Σε εξέλιξη',
        'approved' => 'Εγκρίθηκε',
        'rejected' => 'Απορρίφθηκε',
    ];
    $payload = $approvalRequest->payload ?? [];
    $backType = $approvalRequest->workflow_type;
@endphp

@section('body')
<div class="shell">
    @include('portal.partials.sidebar', ['user' => $user])

    <main class="content">
        <header class="topbar">
            <div>
                <div class="eyebrow">{{ $approvalRequest->request_code }}</div>
                <h1>{{ $approvalRequest->title }}</h1>
            </div>
        </header>

        <div class="portal-grid two-col">
            <section class="surface">
                <div class="panel-head">
                    <div>
                        <div class="eyebrow">Αίτηση</div>
                        <h2>{{ $statusLabels[$approvalRequest->status] ?? $approvalRequest->status }}</h2>
                    </div>
                    <span class="pill {{ $approvalRequest->status === 'approved' ? 'green' : ($approvalRequest->status === 'rejected' ? 'red' : 'amber') }}">
                        {{ $statusLabels[$approvalRequest->status] ?? $approvalRequest->status }}
                    </span>
                </div>

                <div class="compact-list">
                    <div class="list-item">
                        <strong>Αιτών</strong>
                        <div class="muted" style="margin-top:6px;">{{ $approvalRequest->requester->name }}</div>
                    </div>

                    @if ($approvalRequest->description)
                        <div class="list-item">
                            <strong>Περιγραφή</strong>
                            <div class="muted" style="margin-top:6px;line-height:1.55;">{{ $approvalRequest->description }}</div>
                        </div>
                    @endif

                    @if ($approvalRequest->workflow_type === 'discount')
                        <div class="list-item">
                            <strong>Πελάτης</strong>
                            <div class="muted" style="margin-top:6px;line-height:1.55;">
                                {{ $payload['customer_name'] ?? 'Δεν συμπληρώθηκε' }}
                                @if (!empty($payload['customer_code']))
                                    · {{ $payload['customer_code'] }}
                                @endif
                            </div>
                        </div>
                        <div class="list-item">
                            <strong>Έκπτωση</strong>
                            <div class="muted" style="margin-top:6px;line-height:1.55;">
                                {{ $approvalRequest->discount_percent ?? '0.00' }}%
                                · Κανονική {{ $payload['regular_price'] ?? '-' }} €
                                · Ζητούμενη {{ $payload['requested_price'] ?? '-' }} €
                                · Διαφορά {{ $approvalRequest->amount ?? '-' }} €
                            </div>
                        </div>
                        <div class="list-item">
                            <strong>Σύνοψη προϊόντων</strong>
                            <div class="muted" style="margin-top:6px;line-height:1.55;">{{ $payload['product_summary'] ?? '-' }}</div>
                        </div>
                        <div class="list-item">
                            <strong>Λόγος</strong>
                            <div class="muted" style="margin-top:6px;line-height:1.55;">
                                {{ $payload['reason_category_label'] ?? '-' }}
                                @if (!empty($payload['reason']))
                                    <br>{{ $payload['reason'] }}
                                @endif
                                @if (!empty($payload['comments']))
                                    <br>{{ $payload['comments'] }}
                                @endif
                            </div>
                        </div>
                    @elseif ($approvalRequest->starts_on)
                        <div class="list-item">
                            <strong>Ημερομηνίες</strong>
                            <div class="muted" style="margin-top:6px;">
                                {{ $approvalRequest->starts_on->format('d/m/Y') }}
                                @if ($approvalRequest->ends_on)
                                    - {{ $approvalRequest->ends_on->format('d/m/Y') }}
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                @if ($canDecide)
                    <form method="post" action="{{ route('portal.approvals.decide', $approvalRequest) }}" style="margin-top:20px;">
                        @csrf
                        <div class="field">
                            <label>Σχόλιο απόφασης</label>
                            <textarea name="comments" rows="3"></textarea>
                        </div>
                        <div class="action-row" style="margin-top:14px;">
                            <button class="button" name="decision" value="approve" style="background:var(--green);color:white;" type="submit">Έγκριση</button>
                            <button class="button" name="decision" value="reject" style="background:var(--red);color:white;" type="submit">Απόρριψη</button>
                            <button class="button" name="decision" value="comment" type="submit">Σχόλιο</button>
                        </div>
                    </form>
                @endif
            </section>

            <aside class="surface">
                <div class="panel-head">
                    <div>
                        <div class="eyebrow">Ροή</div>
                        <h2>Ποιος αποφασίζει</h2>
                    </div>
                </div>

                <div class="compact-list">
                    @forelse ($approvalRequest->steps as $step)
                        <div class="list-item">
                            <div class="list-row">
                                <div>
                                    <strong>{{ $step->label }}</strong>
                                    <div class="muted" style="margin-top:5px;">
                                        {{ $step->approver?->name ?? $step->required_role_code ?? 'Δεν έχει οριστεί άτομο' }}
                                    </div>
                                </div>
                                <span class="pill {{ $step->status === 'approved' ? 'green' : ($step->status === 'rejected' ? 'red' : '') }}">
                                    {{ ['pending' => 'Αναμονή', 'approved' => 'OK', 'rejected' => 'Όχι', 'commented' => 'Σχόλιο'][$step->status] ?? $step->status }}
                                </span>
                            </div>
                            @if ($step->comments)
                                <div class="muted" style="margin-top:8px;">{{ $step->comments }}</div>
                            @endif
                        </div>
                    @empty
                        <div class="empty">Αυτόματη έγκριση χωρίς επιπλέον βήματα.</div>
                    @endforelse
                </div>
            </aside>
        </div>
    </main>
</div>
@endsection
