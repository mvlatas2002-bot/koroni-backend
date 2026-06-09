<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
        'title',
        'code',
        'department_id',
        'level',
        'org_level_id',
        'is_managerial',
        'is_active',
    ];

    protected $casts = [
        'is_managerial' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function orgLevel(): BelongsTo
    {
        return $this->belongsTo(OrgLevel::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function employeeAssignments(): HasMany
    {
        return $this->hasMany(EmployeeAssignment::class);
    }
}
