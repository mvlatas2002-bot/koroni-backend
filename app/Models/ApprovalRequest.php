<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'request_code',
    'workflow_type',
    'title',
    'description',
    'requester_id',
    'status',
    'current_approver_id',
    'current_step_number',
    'amount',
    'discount_percent',
    'starts_on',
    'ends_on',
    'payload',
    'submitted_at',
    'decided_at',
])]
class ApprovalRequest extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'payload' => 'array',
            'submitted_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function currentApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_approver_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalStep::class)->orderBy('step_number');
    }

    public function pendingStep(): HasMany
    {
        return $this->hasMany(ApprovalStep::class)->where('status', 'pending')->orderBy('step_number');
    }
}
