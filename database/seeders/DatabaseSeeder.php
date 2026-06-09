<?php

namespace Database\Seeders;

use App\Models\EmployeeAssignment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            OrgLevelSeeder::class,
            DepartmentSeeder::class,
            PositionSeeder::class,
        ]);

        if (User::where('email', 'like', '%@koronisa.local')->count() < 10) {
            $this->call(UserSeeder::class);
        }

        $this->call(ApprovalAuthoritySeeder::class);

        if (EmployeeAssignment::count() === 0) {
            $this->call(EmployeeAssignmentSeeder::class);
        }
    }
}
