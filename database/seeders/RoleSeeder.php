<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'code' => 'STANDARD_USER',
                'name' => 'Βασικός χρήστης',
                'description' => 'Κανονική πρόσβαση εργαζόμενου χωρίς διοικητικά δικαιώματα.',
                'is_system' => true,
            ],
            [
                'code' => 'SALES_REP',
                'name' => 'Πωλητής',
                'description' => 'Πρόσβαση πωλητή σε πρόγραμμα ημέρας και προσωπικές αιτήσεις.',
                'is_system' => true,
            ],
            [
                'code' => 'SUPERVISOR',
                'name' => 'Προϊστάμενος',
                'description' => 'Πρόσβαση προϊσταμένου για ομάδα, εγκρίσεις και εταιρικές ενημερώσεις.',
                'is_system' => true,
            ],
            [
                'code' => 'COMMERCIAL_DIRECTOR',
                'name' => 'Εμπορικός διευθυντής',
                'description' => 'Πρόσβαση εμπορικής διεύθυνσης, πωλήσεων και εγκρίσεων.',
                'is_system' => true,
            ],
            [
                'code' => 'MANAGEMENT',
                'name' => 'Διοίκηση',
                'description' => 'Πρόσβαση διοίκησης σε συνολική εικόνα και εγκρίσεις.',
                'is_system' => true,
            ],
            [
                'code' => 'OPERATIONS_ADMIN',
                'name' => 'Operations Admin',
                'description' => 'Διαχείριση λειτουργικών δομών, SOPs και governance.',
                'is_system' => true,
            ],
            [
                'code' => 'SYSTEM_ADMIN',
                'name' => 'System Admin',
                'description' => 'Πλήρης τεχνική και διαχειριστική πρόσβαση πλατφόρμας.',
                'is_system' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['code' => $role['code']], $role);
        }
    }
}
