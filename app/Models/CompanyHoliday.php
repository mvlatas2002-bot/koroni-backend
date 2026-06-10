<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'holiday_date',
    'name',
    'type',
    'is_paid',
])]
class CompanyHoliday extends Model
{
    protected function casts(): array
    {
        return [
            'holiday_date' => 'date',
            'is_paid' => 'boolean',
        ];
    }
}
