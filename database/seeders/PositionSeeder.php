<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\OrgLevel;
use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            'director' => 80,
            'manager' => 60,
            'operations' => 50,
            'specialist' => 40,
            'employee' => 20,
        ];

        $positions = [
            ['code' => 'operational_director', 'title' => 'Διοίκηση', 'department' => 'OPERATIONAL_DIRECTORATE', 'level' => 'director', 'is_managerial' => true],
            ['code' => 'commercial_director', 'title' => 'Διοίκηση', 'department' => 'COMMERCIAL_DIRECTORATE', 'level' => 'director', 'is_managerial' => true],

            ['code' => 'logistic_manager', 'title' => 'Logistic Manager', 'department' => 'LOGISTICS_FUNCTION', 'level' => 'manager', 'is_managerial' => true],
            ['code' => 'accounting_manager', 'title' => 'Accounting Manager', 'department' => 'ACCOUNTING_DEPT', 'level' => 'manager', 'is_managerial' => true],
            ['code' => 'operations_manager', 'title' => 'Operations Manager', 'department' => 'OPERATIONS_DEPT', 'level' => 'manager', 'is_managerial' => true],
            ['code' => 'it_manager', 'title' => 'IT Manager', 'department' => 'IT_DEPT', 'level' => 'manager', 'is_managerial' => true],
            ['code' => 'sales_manager', 'title' => 'Sales Manager', 'department' => 'COMMERCIAL_DEPT', 'level' => 'manager', 'is_managerial' => true],
            ['code' => 'technical_manager', 'title' => 'Υπεύθυνος', 'department' => 'TECHNICAL_TEAM', 'level' => 'manager', 'is_managerial' => true],
            ['code' => 'procurement_manager', 'title' => 'Procurement Manager', 'department' => 'PROCUREMENT_DEPT', 'level' => 'manager', 'is_managerial' => true],
            ['code' => 'customer_supervisor', 'title' => 'Προϊστάμενη', 'department' => 'CUSTOMER_DEPT', 'level' => 'manager', 'is_managerial' => true],
            ['code' => 'invoicing_supervisor', 'title' => 'Προϊσταμένη', 'department' => 'INVOICING_DEPT', 'level' => 'manager', 'is_managerial' => true],
            ['code' => 'receiving_supervisor', 'title' => 'Προϊστάμενος', 'department' => 'RECEIVING_DEPT', 'level' => 'manager', 'is_managerial' => true],

            ['code' => 'operations_staff', 'title' => 'Μέλος', 'department' => 'OPERATIONS_DEPT', 'level' => 'operations', 'is_managerial' => false],
            ['code' => 'accounting_staff', 'title' => 'Μέλος', 'department' => 'ACCOUNTING_DEPT', 'level' => 'specialist', 'is_managerial' => false],
            ['code' => 'it_staff', 'title' => 'Μέλος', 'department' => 'IT_DEPT', 'level' => 'specialist', 'is_managerial' => false],
            ['code' => 'sales_ops_staff', 'title' => 'Μέλος', 'department' => 'SALES_OPS_DEPT', 'level' => 'specialist', 'is_managerial' => false],
            ['code' => 'customer_staff', 'title' => 'Μέλος', 'department' => 'CUSTOMER_DEPT', 'level' => 'employee', 'is_managerial' => false],
            ['code' => 'sales_rep', 'title' => 'Πωλητής', 'department' => 'SALES_DEPT', 'level' => 'employee', 'is_managerial' => false],
            ['code' => 'technical_staff', 'title' => 'Μέλος', 'department' => 'TECHNICAL_TEAM', 'level' => 'employee', 'is_managerial' => false],
            ['code' => 'procurement_staff', 'title' => 'Μέλος', 'department' => 'PROCUREMENT_DEPT', 'level' => 'employee', 'is_managerial' => false],
            ['code' => 'invoicing_staff', 'title' => 'Μέλος', 'department' => 'INVOICING_DEPT', 'level' => 'employee', 'is_managerial' => false],
            ['code' => 'movement_staff', 'title' => 'Μέλος', 'department' => 'MOVEMENT_OFFICE', 'level' => 'employee', 'is_managerial' => false],
            ['code' => 'receiving_staff', 'title' => 'Χειριστής Κλαρκ', 'department' => 'RECEIVING_DEPT', 'level' => 'employee', 'is_managerial' => false],
            ['code' => 'warehouse_staff', 'title' => 'Αποθηκάριος', 'department' => 'WAREHOUSEMEN_TEAM', 'level' => 'employee', 'is_managerial' => false],
            ['code' => 'driver', 'title' => 'Οδηγός', 'department' => 'DRIVERS_TEAM', 'level' => 'employee', 'is_managerial' => false],
        ];

        $activeCodes = collect($positions)->pluck('code')->all();

        foreach ($positions as $position) {
            $department = Department::where('code', $position['department'])->firstOrFail();
            $rank = $levels[$position['level']];
            $orgLevel = OrgLevel::where('rank', $rank)->firstOrFail();

            Position::updateOrCreate(
                ['code' => $position['code']],
                [
                    'title' => $position['title'],
                    'code' => $position['code'],
                    'department_id' => $department->id,
                    'level' => $rank,
                    'org_level_id' => $orgLevel->id,
                    'is_managerial' => $position['is_managerial'],
                    'is_active' => true,
                ]
            );
        }

        Position::whereNotIn('code', $activeCodes)->update(['is_active' => false]);
    }
}
