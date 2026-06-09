<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAssignment extends Model
{
    protected $fillable = [
        'employee_profile_id',
        'department_id',
        'position_id',
        'direct_manager_profile_id',
        'secondary_approver_profile_id',
        'acting_manager_profile_id',
        'is_primary',
        'is_active',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function directManagerProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'direct_manager_profile_id');
    }

    public function secondaryApproverProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'secondary_approver_profile_id');
    }

    public function actingManagerProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'acting_manager_profile_id');
    }
}
