<?php

namespace Database\Seeders;

use App\Models\ApprovalAuthority;
use App\Models\User;
use Illuminate\Database\Seeder;

class ApprovalAuthoritySeeder extends Seeder
{
    public function run(): void
    {
        $commercialApprover = User::where('email', 'antonis.tsafantakis@koronisa.local')
            ->orWhereHas('role', fn ($query) => $query->where('code', 'COMMERCIAL_DIRECTOR'))
            ->orderBy('name')
            ->first();

        $managementApprover = User::where('email', 'giannis.vlatas@koronisa.local')
            ->orWhereHas('role', fn ($query) => $query->where('code', 'MANAGEMENT'))
            ->orderBy('email', 'desc')
            ->first();

        $rules = [
            [
                'workflow_type' => 'discount',
                'authority_type' => 'functional_approver',
                'approver_id' => $commercialApprover?->id,
                'required_role_code' => 'COMMERCIAL_DIRECTOR',
                'min_percent' => 4,
                'max_percent' => 15,
                'min_inclusive' => false,
                'max_inclusive' => false,
                'label' => '> 4% και < 15% - Εμπορική έγκριση',
                'notes' => 'Οι εκπτώσεις πάνω από την αρμοδιότητα πωλητή πάνε στον εμπορικό εγκριτή.',
            ],
            [
                'workflow_type' => 'discount',
                'authority_type' => 'management',
                'approver_id' => $managementApprover?->id,
                'required_role_code' => 'MANAGEMENT',
                'min_percent' => 15,
                'max_percent' => null,
                'min_inclusive' => true,
                'max_inclusive' => true,
                'label' => '>= 15% - Έγκριση διοίκησης',
                'notes' => 'Το 15% ακριβώς και όλα τα μεγαλύτερα ποσοστά ανεβαίνουν στη διοίκηση.',
            ],
        ];

        foreach ($rules as $rule) {
            ApprovalAuthority::updateOrCreate(
                [
                    'workflow_type' => $rule['workflow_type'],
                    'authority_type' => $rule['authority_type'],
                    'department_id' => null,
                    'min_percent' => $rule['min_percent'],
                    'max_percent' => $rule['max_percent'],
                ],
                [
                    ...$rule,
                    'department_id' => null,
                    'effective_from' => now()->toDateString(),
                    'effective_to' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
