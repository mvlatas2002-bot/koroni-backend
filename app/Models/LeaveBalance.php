<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'year',
    'annual_entitlement',
    'manual_adjustment',
    'notes',
])]
class LeaveBalance extends Model
{
    protected function casts(): array
    {
        return [
            'annual_entitlement' => 'decimal:2',
            'manual_adjustment' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
