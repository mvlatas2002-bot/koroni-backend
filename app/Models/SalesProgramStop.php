<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'sales_rep_id',
    'day_label',
    'schedule_date',
    'area',
    'customer_label',
    'note',
    'sort_order',
    'is_active',
])]
class SalesProgramStop extends Model
{
    protected function casts(): array
    {
        return [
            'schedule_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function salesRep(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_rep_id');
    }
}
