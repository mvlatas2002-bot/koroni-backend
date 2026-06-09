<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['key' => 'company', 'name' => 'ΚΟΡΩΝΗ Α.Ε.', 'code' => 'KORONI_AE', 'type' => 'LEGAL_ENTITY', 'parent' => null],

            ['key' => 'operational_directorate', 'name' => 'Λειτουργική Διεύθυνση', 'code' => 'OPERATIONAL_DIRECTORATE', 'type' => 'DIRECTORATE_FUNCTION', 'parent' => 'company'],
            ['key' => 'commercial_directorate', 'name' => 'Εμπορική Διεύθυνση', 'code' => 'COMMERCIAL_DIRECTORATE', 'type' => 'DIRECTORATE_FUNCTION', 'parent' => 'company'],

            ['key' => 'logistics', 'name' => 'Logistics / Αποθήκη', 'code' => 'LOGISTICS_FUNCTION', 'type' => 'DEPARTMENT', 'parent' => 'operational_directorate'],
            ['key' => 'accounting', 'name' => 'Τμήμα Λογιστηρίου', 'code' => 'ACCOUNTING_DEPT', 'type' => 'DEPARTMENT', 'parent' => 'operational_directorate'],
            ['key' => 'operations', 'name' => 'Τμήμα Operations', 'code' => 'OPERATIONS_DEPT', 'type' => 'DEPARTMENT', 'parent' => 'operational_directorate'],
            ['key' => 'it', 'name' => 'Τμήμα IT / Μηχανογράφηση', 'code' => 'IT_DEPT', 'type' => 'DEPARTMENT', 'parent' => 'operational_directorate'],

            ['key' => 'invoicing', 'name' => 'Τμήμα Τιμολόγησης', 'code' => 'INVOICING_DEPT', 'type' => 'TEAM', 'parent' => 'logistics'],
            ['key' => 'movement_office', 'name' => 'Γραφείο Κίνησης', 'code' => 'MOVEMENT_OFFICE', 'type' => 'TEAM', 'parent' => 'logistics'],
            ['key' => 'receiving', 'name' => 'Παραλαβές', 'code' => 'RECEIVING_DEPT', 'type' => 'TEAM', 'parent' => 'logistics'],
            ['key' => 'warehousemen', 'name' => 'Αποθηκάριοι', 'code' => 'WAREHOUSEMEN_TEAM', 'type' => 'TEAM', 'parent' => 'movement_office'],
            ['key' => 'drivers', 'name' => 'Οδηγοί', 'code' => 'DRIVERS_TEAM', 'type' => 'TEAM', 'parent' => 'movement_office'],

            ['key' => 'commercial', 'name' => 'Εμπορικό Τμήμα', 'code' => 'COMMERCIAL_DEPT', 'type' => 'DEPARTMENT', 'parent' => 'commercial_directorate'],
            ['key' => 'technical', 'name' => 'Τμήμα Τεχνικών', 'code' => 'TECHNICAL_TEAM', 'type' => 'DEPARTMENT', 'parent' => 'commercial_directorate'],
            ['key' => 'procurement', 'name' => 'Τμήμα Προμηθειών', 'code' => 'PROCUREMENT_DEPT', 'type' => 'DEPARTMENT', 'parent' => 'commercial_directorate'],

            ['key' => 'sales_ops', 'name' => 'Εμπορική Υποστήριξη / Sales Operations', 'code' => 'SALES_OPS_DEPT', 'type' => 'TEAM', 'parent' => 'commercial'],
            ['key' => 'customer_service', 'name' => 'Customer Service', 'code' => 'CUSTOMER_DEPT', 'type' => 'TEAM', 'parent' => 'commercial'],
            ['key' => 'sales', 'name' => 'Πωλήσεις', 'code' => 'SALES_DEPT', 'type' => 'TEAM', 'parent' => 'commercial'],
        ];

        $created = [];
        foreach ($departments as $department) {
            $parentId = $department['parent'] ? ($created[$department['parent']]->id ?? null) : null;
            $existing = Department::where('code', $department['code'])->first();

            if ($existing) {
                $existing->update([
                    'org_type' => $department['type'],
                    'parent_id' => $parentId,
                    'is_active' => true,
                ]);

                $created[$department['key']] = $existing;
                continue;
            }

            $created[$department['key']] = Department::create([
                'name' => $department['name'],
                'code' => $department['code'],
                'org_type' => $department['type'],
                'parent_id' => $parentId,
                'is_active' => true,
            ]);
        }

        // Admin-created units are intentionally preserved. They can be disabled from the portal UI.
    }
}
