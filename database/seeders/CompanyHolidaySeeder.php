<?php

namespace Database\Seeders;

use App\Models\CompanyHoliday;
use Illuminate\Database\Seeder;

class CompanyHolidaySeeder extends Seeder
{
    public function run(): void
    {
        $holidays = [
            ['holiday_date' => '2026-01-01', 'name' => 'Πρωτοχρονιά'],
            ['holiday_date' => '2026-01-06', 'name' => 'Θεοφάνεια'],
            ['holiday_date' => '2026-03-23', 'name' => 'Καθαρά Δευτέρα'],
            ['holiday_date' => '2026-03-25', 'name' => '25η Μαρτίου'],
            ['holiday_date' => '2026-04-10', 'name' => 'Μεγάλη Παρασκευή'],
            ['holiday_date' => '2026-04-13', 'name' => 'Δευτέρα του Πάσχα'],
            ['holiday_date' => '2026-05-01', 'name' => 'Πρωτομαγιά'],
            ['holiday_date' => '2026-06-01', 'name' => 'Αγίου Πνεύματος'],
            ['holiday_date' => '2026-08-15', 'name' => 'Κοίμηση Θεοτόκου'],
            ['holiday_date' => '2026-10-28', 'name' => '28η Οκτωβρίου'],
            ['holiday_date' => '2026-12-25', 'name' => 'Χριστούγεννα'],
            ['holiday_date' => '2026-12-26', 'name' => 'Σύναξη Θεοτόκου'],
        ];

        foreach ($holidays as $holiday) {
            CompanyHoliday::updateOrCreate(
                ['holiday_date' => $holiday['holiday_date']],
                ['name' => $holiday['name'], 'type' => 'public', 'is_paid' => true]
            );
        }
    }
}
