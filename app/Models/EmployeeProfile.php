<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EmployeeProfile extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'employment_type',
        'employment_status',
        'is_external_collaborator',
        'is_active',
        'annual_leave_allowance',
    ];

    protected $casts = [
        'is_external_collaborator' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(EmployeeAssignment::class);
    }

    public function primaryAssignment(): HasOne
    {
        return $this->hasOne(EmployeeAssignment::class)->where('is_primary', true)->where('is_active', true);
    }
}
