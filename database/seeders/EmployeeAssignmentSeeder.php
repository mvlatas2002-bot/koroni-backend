<?php

namespace Database\Seeders;

use App\Models\EmployeeAssignment;
use App\Models\EmployeeProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $profilesByUserId = [];

        User::with(['department', 'position', 'manager', 'secondaryApprover', 'actingManager'])
            ->orderBy('id')
            ->get()
            ->each(function (User $user) use (&$profilesByUserId) {
                $profilesByUserId[$user->id] = EmployeeProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'full_name' => $user->name,
                        'email' => $user->email,
                        'employment_type' => 'internal',
                        'employment_status' => $user->employment_status ?: 'active',
                        'is_external_collaborator' => false,
                        'is_active' => $user->is_active,
                        'annual_leave_allowance' => 22,
                    ]
                );
            });

        User::with(['department', 'position', 'manager', 'secondaryApprover', 'actingManager'])
            ->orderBy('id')
            ->get()
            ->each(function (User $user) use ($profilesByUserId) {
                if (! $user->department_id) {
                    return;
                }

                $profile = $profilesByUserId[$user->id] ?? null;

                if (! $profile) {
                    return;
                }

                EmployeeAssignment::updateOrCreate(
                    [
                        'employee_profile_id' => $profile->id,
                        'is_primary' => true,
                    ],
                    [
                        'department_id' => $user->department_id,
                        'position_id' => $user->position_id,
                        'direct_manager_profile_id' => $user->manager_id ? ($profilesByUserId[$user->manager_id]->id ?? null) : null,
                        'secondary_approver_profile_id' => $user->secondary_approver_id ? ($profilesByUserId[$user->secondary_approver_id]->id ?? null) : null,
                        'acting_manager_profile_id' => $user->acting_manager_id ? ($profilesByUserId[$user->acting_manager_id]->id ?? null) : null,
                        'is_active' => $user->is_active,
                        'effective_from' => now()->toDateString(),
                        'effective_to' => null,
                    ]
                );
            });
    }
}
