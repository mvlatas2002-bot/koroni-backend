<?php

namespace Database\Seeders;

use App\Models\OrgLevel;
use Illuminate\Database\Seeder;

class OrgLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['name' => 'Διοίκηση', 'rank' => 100, 'description' => 'Τελική ευθύνη και διοικητική απόφαση.'],
            ['name' => 'Διεύθυνση', 'rank' => 80, 'description' => 'Διευθυντική ευθύνη λειτουργικής ή εμπορικής περιοχής.'],
            ['name' => 'Προϊστάμενος / Υπεύθυνος', 'rank' => 60, 'description' => 'Άμεση ευθύνη ομάδας ή τμήματος.'],
            ['name' => 'Operations / Admin', 'rank' => 50, 'description' => 'Λειτουργική διαχείριση πλατφόρμας και διαδικασιών.'],
            ['name' => 'Εξειδικευμένος ρόλος', 'rank' => 40, 'description' => 'Εξειδικευμένη επιχειρησιακή ευθύνη.'],
            ['name' => 'Υπάλληλος', 'rank' => 20, 'description' => 'Κανονική θέση εργαζομένου.'],
        ];

        foreach ($levels as $level) {
            OrgLevel::updateOrCreate(
                ['rank' => $level['rank']],
                [...$level, 'is_active' => true],
            );
        }
    }
}
