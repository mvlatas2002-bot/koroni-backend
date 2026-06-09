<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::where('email', 'like', '%@koronisa.local')->update(['is_active' => false]);
        User::where('email', 'test@example.com')->delete();

        $users = [
            ['key' => 'dimitris_vlatas', 'name' => 'Δημήτρης Βλατάς', 'role' => 'MANAGEMENT', 'department' => 'OPERATIONAL_DIRECTORATE', 'position' => 'operational_director'],
            ['key' => 'giannis_vlatas', 'name' => 'Γιάννης Βλατάς', 'role' => 'MANAGEMENT', 'department' => 'COMMERCIAL_DIRECTORATE', 'position' => 'commercial_director'],

            ['key' => 'alexandros_venianakis', 'name' => 'Αλέξανδρος Βενιανάκης', 'role' => 'SUPERVISOR', 'department' => 'LOGISTICS_FUNCTION', 'position' => 'logistic_manager', 'manager' => 'dimitris_vlatas'],
            ['key' => 'chrysa_petimezaki', 'name' => 'Χρύσα Πετιμεζάκη', 'role' => 'SUPERVISOR', 'department' => 'INVOICING_DEPT', 'position' => 'invoicing_supervisor', 'manager' => 'alexandros_venianakis'],
            ['key' => 'giorgos_papadakis', 'name' => 'Γιώργος Παπαδάκης', 'role' => 'SUPERVISOR', 'department' => 'RECEIVING_DEPT', 'position' => 'receiving_supervisor', 'manager' => 'alexandros_venianakis'],
            ['key' => 'faidra_papazoglou', 'name' => 'Φαίδρα Παπάζογλου', 'role' => 'SUPERVISOR', 'department' => 'ACCOUNTING_DEPT', 'position' => 'accounting_manager', 'manager' => 'dimitris_vlatas'],
            ['key' => 'manolis_chronakis', 'name' => 'Μανώλης Χρονάκης', 'role' => 'OPERATIONS_ADMIN', 'department' => 'OPERATIONS_DEPT', 'position' => 'operations_manager', 'manager' => 'dimitris_vlatas'],
            ['key' => 'apostolos_typakianakis', 'name' => 'Απόστολος Τυμπακιανάκης', 'role' => 'SUPERVISOR', 'department' => 'IT_DEPT', 'position' => 'it_manager', 'manager' => 'dimitris_vlatas'],

            ['key' => 'antonis_tsafantakis', 'name' => 'Αντώνης Τσαφαντάκης', 'role' => 'COMMERCIAL_DIRECTOR', 'department' => 'COMMERCIAL_DEPT', 'position' => 'sales_manager', 'manager' => 'giannis_vlatas'],
            ['key' => 'roula_papadaki', 'name' => 'Ρούλα Παπαδάκη', 'role' => 'SUPERVISOR', 'department' => 'CUSTOMER_DEPT', 'position' => 'customer_supervisor', 'manager' => 'antonis_tsafantakis'],
            ['key' => 'nikos_koutsoukos', 'name' => 'Νίκος Κουτσούκος', 'role' => 'SUPERVISOR', 'department' => 'TECHNICAL_TEAM', 'position' => 'technical_manager', 'manager' => 'giannis_vlatas'],
            ['key' => 'miltos_papadomanolakis', 'name' => 'Μίλτος Παπαδομανωλάκης', 'role' => 'SUPERVISOR', 'department' => 'PROCUREMENT_DEPT', 'position' => 'procurement_manager', 'manager' => 'giannis_vlatas'],
        ];

        $this->appendMembers($users, 'INVOICING_DEPT', 'invoicing_staff', 'chrysa_petimezaki', ['Βιβή Αρβανάκη']);
        $this->appendMembers($users, 'MOVEMENT_OFFICE', 'movement_staff', 'alexandros_venianakis', ['Κλινάκης Γιάννης', 'Καλτσούνης Νίκος']);
        $this->appendMembers($users, 'WAREHOUSEMEN_TEAM', 'warehouse_staff', 'alexandros_venianakis', [
            'Τσαμπουράκης Κώστας',
            'Χαριστάκης Ιωάννης',
            'Γρινεζάκης Κώστας',
            'Τάχλας Παναγιώτης',
            'Διαλεχτάκης Μιχάλης',
            'Ζαφείρης Δημήτριος',
            'Βαρβαρέσος Αναστάσιος',
            'Χανιωτάκης Κώστας',
            'Ζουμής Θεόδωρος',
            'Δημάκης Εμμανουήλ',
            'Ρωμανός Σπύρος',
            'Δουλγεράκης Γεώργιος',
            'Μηλιάκης',
            'Χαιρετής Ιωάννης',
        ]);
        $this->appendMembers($users, 'DRIVERS_TEAM', 'driver', 'alexandros_venianakis', [
            'Μαθιουδάκης Θεόδωρος',
            'Κόκκινος Σταμάτης',
            'Μιχάλακης Μανώλης',
            'Βαρβαρέσος Νώντας',
            'Παπαδόπουλος Ιωάννης',
            'Χατζηανδρέου Μπάμπης',
            'Σκεπάρης Δημήτρης',
            'Πυρουνάκης Αργύρης',
            'Ακασόγλου Παναγιώτης',
            'Στειακάκης Γιώργος',
            'Σουρανάκης Παναγιώτης',
            'Βαρδάκης Νώντας',
        ]);
        $this->appendMembers($users, 'RECEIVING_DEPT', 'receiving_staff', 'giorgos_papadakis', [
            'Παπαδάκης Ανδρέας',
            'Κασωτάκης Δημήτρης',
            'Κανάκης Ιωάννης',
            'Καραμπίνης Δημήτρης',
            'Βελβασάκης',
        ]);
        $this->appendMembers($users, 'ACCOUNTING_DEPT', 'accounting_staff', 'faidra_papazoglou', [
            'Στράτος Τσουρδαλάκης',
            'Στέλλα Κούλα',
            'Πόπη Ευτυχάκη',
        ]);
        $this->appendMembers($users, 'OPERATIONS_DEPT', 'operations_staff', 'manolis_chronakis', [
            ['name' => 'Μάνος Βλατάς', 'role' => 'SYSTEM_ADMIN'],
        ]);
        $this->appendMembers($users, 'IT_DEPT', 'it_staff', 'apostolos_typakianakis', ['Αντώνης Κονιδάκης']);

        $this->appendMembers($users, 'SALES_OPS_DEPT', 'sales_ops_staff', 'antonis_tsafantakis', [
            'Μαρία Ανδριγιαννάκη',
            'Μαρία Σκουλούδη',
        ]);
        $this->appendMembers($users, 'CUSTOMER_DEPT', 'customer_staff', 'roula_papadaki', [
            'Κρυστάλλη Λυδάκη',
            'Μαρίνος Μιχελάκης',
            'Κατερίνα Μπαλτάκη',
        ]);
        $this->appendMembers($users, 'SALES_DEPT', 'sales_rep', 'antonis_tsafantakis', [
            'Γιάννης Κωστάκης',
            'Γιάννης Τοιντάρης',
            'Γιώργος Καλογεράκης',
            'Γιώργος Κουνδουράκης',
            'Θωμάς Καντιδάκης',
            'Κλαίρη Κυριακάκη',
            'Λευτέρης Κοντογιάννης',
            'Μάριος Ζερβίδης',
            'Μιχαέλα Μαυρή',
            'Σπύρος Χαρατής',
            'Στέλιος Μεσσαριτάκης',
        ]);
        $this->appendMembers($users, 'TECHNICAL_TEAM', 'technical_staff', 'nikos_koutsoukos', ['Γιάννης Γωνιανάκης']);
        $this->appendMembers($users, 'PROCUREMENT_DEPT', 'procurement_staff', 'miltos_papadomanolakis', [
            'Ιωάννα Καρατσαλίδη',
            'Νίκος Κλάδος',
        ]);

        $created = [];
        $usedEmails = [];
        $credentials = [['#', 'name', 'email', 'password', 'role', 'department', 'manager']];

        foreach ($users as $index => $user) {
            $department = Department::where('code', $user['department'])->firstOrFail();
            $position = Position::where('code', $user['position'])->firstOrFail();
            $role = Role::where('code', $user['role'] ?? 'STANDARD_USER')->firstOrFail();
            $password = (string) ($index + 1);

            [$firstName, $lastName] = $this->splitName($user['name']);
            $email = $this->emailFor($user['name'], $usedEmails);

            $existing = User::where('email', $email)->first();

            if ($existing) {
                $existing->update([
                    'is_active' => true,
                    'employment_status' => $existing->employment_status ?: 'active',
                    'role_id' => $role->id,
                    'department_id' => $department->id,
                    'position_id' => $position->id,
                    'manager_id' => null,
                    'secondary_approver_id' => null,
                    'acting_manager_id' => null,
                    'email_verified_at' => $existing->email_verified_at ?: now(),
                ]);

                $created[$user['key']] = $existing->fresh();
            } else {
                $created[$user['key']] = User::create([
                    'name' => $user['name'],
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'is_active' => true,
                    'employment_status' => 'active',
                    'role_id' => $role->id,
                    'department_id' => $department->id,
                    'position_id' => $position->id,
                    'manager_id' => null,
                    'secondary_approver_id' => null,
                    'acting_manager_id' => null,
                    'email_verified_at' => now(),
                ]);
            }

            $credentials[] = [
                (string) ($index + 1),
                $user['name'],
                $email,
                $password,
                $role->code,
                $department->code,
                $user['manager'] ?? '',
            ];
        }

        foreach ($users as $user) {
            $model = $created[$user['key']];
            $manager = isset($user['manager']) ? ($created[$user['manager']] ?? null) : null;

            $model->update([
                'manager_id' => $manager?->id,
                'secondary_approver_id' => $this->secondaryApprover($user, $created)?->id,
                'acting_manager_id' => null,
            ]);
        }

        Storage::disk('local')->put('seed-credentials/koroni-users.csv', $this->toCsv($credentials));
    }

    private function appendMembers(array &$users, string $department, string $position, string $manager, array $members): void
    {
        foreach ($members as $index => $member) {
            $name = is_array($member) ? $member['name'] : $member;
            $role = is_array($member)
                ? ($member['role'] ?? 'STANDARD_USER')
                : ($position === 'sales_rep' ? 'SALES_REP' : 'STANDARD_USER');

            $users[] = [
                'key' => Str::slug($department.'-'.$position.'-'.$index),
                'name' => $name,
                'role' => $role,
                'department' => $department,
                'position' => $position,
                'manager' => $manager,
            ];
        }
    }

    private function secondaryApprover(array $user, array $created): ?User
    {
        $department = $user['department'] ?? null;

        if (in_array($department, ['COMMERCIAL_DEPT', 'SALES_OPS_DEPT', 'CUSTOMER_DEPT', 'SALES_DEPT', 'TECHNICAL_TEAM', 'PROCUREMENT_DEPT'], true)) {
            return $created['giannis_vlatas'] ?? null;
        }

        if (in_array($department, ['COMMERCIAL_DIRECTORATE', 'OPERATIONAL_DIRECTORATE'], true)) {
            return null;
        }

        return $created['dimitris_vlatas'] ?? null;
    }

    private function emailFor(string $name, array &$usedEmails): string
    {
        $local = trim(preg_replace('/[^a-z0-9]+/', '.', $this->latin($name)), '.');
        $local = $local !== '' ? $local : 'user';
        $email = $local.'@koronisa.local';
        $suffix = 2;

        while (in_array($email, $usedEmails, true)) {
            $email = $local.'.'.$suffix.'@koronisa.local';
            $suffix++;
        }

        $usedEmails[] = $email;

        return $email;
    }

    private function latin(string $value): string
    {
        $value = mb_strtolower($value);
        $value = strtr($value, [
            'ά' => 'α', 'έ' => 'ε', 'ή' => 'η', 'ί' => 'ι', 'ϊ' => 'ι', 'ΐ' => 'ι',
            'ό' => 'ο', 'ύ' => 'υ', 'ϋ' => 'υ', 'ΰ' => 'υ', 'ώ' => 'ω',
        ]);
        $value = str_replace(
            ['ου', 'αι', 'ει', 'οι', 'υι', 'αυ', 'ευ'],
            ['ou', 'ai', 'ei', 'oi', 'yi', 'av', 'ev'],
            $value
        );

        $map = [
            'α' => 'a', 'β' => 'v', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z',
            'η' => 'i', 'θ' => 'th', 'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm',
            'ν' => 'n', 'ξ' => 'x', 'ο' => 'o', 'π' => 'p', 'ρ' => 'r', 'σ' => 's',
            'ς' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'ch', 'ψ' => 'ps',
            'ω' => 'o',
        ];

        return strtr($value, $map);
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];

        if (count($parts) <= 1) {
            return [$name, null];
        }

        $firstName = array_shift($parts);

        return [$firstName, implode(' ', $parts) ?: null];
    }

    private function toCsv(array $rows): string
    {
        $lines = [];

        foreach ($rows as $row) {
            $lines[] = collect($row)
                ->map(fn ($value) => '"'.str_replace('"', '""', (string) $value).'"')
                ->implode(',');
        }

        return implode(PHP_EOL, $lines).PHP_EOL;
    }
}
