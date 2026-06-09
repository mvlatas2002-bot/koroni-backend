<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'workflow_type',
    'authority_type',
    'department_id',
    'approver_id',
    'required_role_code',
    'min_percent',
    'max_percent',
    'min_inclusive',
    'max_inclusive',
    'effective_from',
    'effective_to',
    'is_active',
    'label',
    'notes',
])]
class ApprovalAuthority extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'min_percent' => 'decimal:2',
            'max_percent' => 'decimal:2',
            'min_inclusive' => 'boolean',
            'max_inclusive' => 'boolean',
            'effective_from' => 'date',
            'effective_to' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
