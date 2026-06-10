<?php

namespace Database\Seeders;

use App\Models\LeaveBalance;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeaveBalanceSeeder extends Seeder
{
    public function run(): void
    {
        $year = now()->year;

        User::query()
            ->where('is_active', true)
            ->each(function (User $user) use ($year) {
                LeaveBalance::firstOrCreate(
                    ['user_id' => $user->id, 'year' => $year],
                    ['annual_entitlement' => 22, 'manual_adjustment' => 0]
                );
            });
    }
}
