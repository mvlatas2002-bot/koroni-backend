<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'push_enabled',
    'email_enabled',
])]
class UserNotificationPreference extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'user_id';

    protected function casts(): array
    {
        return [
            'push_enabled' => 'boolean',
            'email_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
