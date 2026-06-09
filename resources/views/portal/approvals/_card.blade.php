@php
    $statusLabels = [
        'pending' => 'Σε εξέλιξη',
        'approved' => 'Εγκρίθηκε',
        'rejected' => 'Απορρίφθηκε',
    ];
    $statusClass = [
        'pending' => 'amber',
        'approved' => 'green',
        'rejected' => 'red',
    ][$approvalRequest->status] ?? '';
@endphp

<a class="list-item" href="{{ route('portal.approvals.show', $approvalRequest) }}" style="display:block;">
    <div class="list-row">
        <div class="truncate">
            <strong>{{ $approvalRequest->title }}</strong>
            <div class="muted truncate" style="margin-top:5px;">
                {{ $approvalRequest->request_code }} · {{ $approvalRequest->requester->name }}
            </div>
        </div>
        <span class="pill {{ $statusClass }}">{{ $statusLabels[$approvalRequest->status] ?? $approvalRequest->status }}</span>
    </div>
    @if ($approvalRequest->currentApprover)
        <div class="muted" style="margin-top:10px;font-size:13px;">
            Περιμένει {{ $approvalRequest->currentApprover->name }}
        </div>
    @endif
</a>
