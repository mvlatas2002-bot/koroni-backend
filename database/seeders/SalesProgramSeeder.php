<?php

namespace Database\Seeders;

use App\Models\SalesProgramArea;
use App\Models\SalesProgramStop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SalesProgramSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/sales_program_import.json');

        if (! File::exists($path)) {
            return;
        }

        $rows = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);
        $salesReps = User::query()
            ->where('is_active', true)
            ->whereHas('role', fn ($role) => $role->where('code', 'SALES_REP'))
            ->get();
        $repByKey = $salesReps->mapWithKeys(fn (User $user) => [$this->normalize($user->name) => $user]);

        $aliases = [
            'ΓΙΑΝΝΗΣ Κ.' => 'Γιάννης Κωστάκης',
            'ΓΙΑΝΝΗΣ Τ.' => 'Γιάννης Τοιντάρης',
            'ΓΙΩΡΓΟΣ 1' => 'Γιώργος Καλογεράκης',
            'ΘΩΜΑΣ' => 'Θωμάς Καντιδάκης',
            'ΛΕΥΤΕΡΗΣ' => 'Λευτέρης Κοντογιάννης',
            'ΜΑΡΙΟΣ' => 'Μάριος Ζερβίδης',
            'ΣΠΥΡΟΣ' => 'Σπύρος Χαρατής',
            'ΣΤΕΛΙΟΣ' => 'Στέλιος Μεσσαριτάκης',
        ];

        $stops = [];
        $templates = [];
        $areas = [];
        $salesRepIds = [];
        $dates = [];
        $now = now();

        foreach ($rows as $row) {
            $source = trim((string) ($row['workbook_stem'] ?? ''));
            $targetName = $aliases[$source] ?? $source;
            $salesRep = $repByKey->get($this->normalize($targetName));

            if (! $salesRep) {
                continue;
            }

            $area = trim((string) ($row['area'] ?? ''));
            $customer = trim((string) ($row['customer_label'] ?? ''));
            $date = trim((string) ($row['schedule_date'] ?? ''));
            $dayLabel = $this->canonicalDayLabel((string) ($row['day_label'] ?? ''));

            if ($area === '' || $date === '' || $dayLabel === '') {
                continue;
            }

            $scheduleDate = Carbon::createFromFormat('Y-m-d', $date)->toDateString();

            $stops[] = [
                'sales_rep_id' => $salesRep->id,
                'day_label' => $dayLabel,
                'schedule_date' => $scheduleDate,
                'area' => $area,
                'customer_label' => $customer !== '' ? $customer : null,
                'note' => null,
                'sort_order' => (int) ($row['sort_order'] ?? 0),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $templateKey = $salesRep->id.'|'.$dayLabel.'|'.(int) ($row['sort_order'] ?? 0);
            $templates[$templateKey] ??= [
                'sales_rep_id' => $salesRep->id,
                'day_label' => $dayLabel,
                'schedule_date' => null,
                'area' => $area,
                'customer_label' => $customer !== '' ? $customer : null,
                'note' => null,
                'sort_order' => (int) ($row['sort_order'] ?? 0),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $areas[$area] = ['label' => $area, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now];
            $salesRepIds[$salesRep->id] = $salesRep->id;
            $dates[$scheduleDate] = $scheduleDate;
        }

        if ($stops === []) {
            return;
        }

        SalesProgramStop::query()
            ->whereIn('sales_rep_id', array_values($salesRepIds))
            ->where(function ($query) use ($dates) {
                $query->whereIn('schedule_date', array_values($dates))->orWhereNull('schedule_date');
            })
            ->delete();

        collect(array_merge($stops, array_values($templates)))->chunk(500)->each(fn ($chunk) => SalesProgramStop::insert($chunk->all()));
        collect(array_values($areas))->chunk(500)->each(fn ($chunk) => SalesProgramArea::insertOrIgnore($chunk->all()));
    }

    private function normalize(string $value): string
    {
        $value = Str::lower($value);
        $value = strtr($value, [
            'ά' => 'α',
            'έ' => 'ε',
            'ή' => 'η',
            'ί' => 'ι',
            'ϊ' => 'ι',
            'ΐ' => 'ι',
            'ό' => 'ο',
            'ύ' => 'υ',
            'ϋ' => 'υ',
            'ΰ' => 'υ',
            'ώ' => 'ω',
            'ς' => 'σ',
        ]);

        return preg_replace('/[^a-z0-9α-ω]+/u', '', $value) ?: '';
    }

    private function canonicalDayLabel(string $value): string
    {
        return [
            'δευτερα' => 'Δευτέρα',
            'τριτη' => 'Τρίτη',
            'τεταρτη' => 'Τετάρτη',
            'πεμπτη' => 'Πέμπτη',
            'παρασκευη' => 'Παρασκευή',
            'σαββατο' => 'Σάββατο',
            'κυριακη' => 'Κυριακή',
        ][$this->normalize($value)] ?? trim($value);
    }
}
