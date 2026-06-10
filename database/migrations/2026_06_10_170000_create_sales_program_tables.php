<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        if (! Schema::hasTable('sales_program_areas')) {
            Schema::create('sales_program_areas', function (Blueprint $table) {
                $table->id();
                $table->string('label', 120)->unique();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by_user_id')->nullable();
                $table->timestamps();

                $table->index(['is_active', 'label']);
            });
        }

        if (! Schema::hasTable('sales_program_stops')) {
            Schema::create('sales_program_stops', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sales_rep_id');
                $table->string('day_label', 20);
                $table->date('schedule_date')->nullable();
                $table->string('area', 120);
                $table->string('customer_label', 160)->nullable();
                $table->string('note', 220)->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['sales_rep_id', 'day_label', 'sort_order']);
                $table->index(['sales_rep_id', 'schedule_date', 'sort_order']);
                $table->index(['day_label', 'is_active']);
                $table->index(['schedule_date', 'is_active']);
                $table->index('area');
            });
        }

        if (! Schema::hasTable('sales_program_day_statuses')) {
            Schema::create('sales_program_day_statuses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sales_rep_id');
                $table->date('schedule_date');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->timestamps();

                $table->unique(['sales_rep_id', 'schedule_date']);
                $table->index('schedule_date');
            });
        }

        $this->seedImportedProgram();
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_program_day_statuses');
        Schema::dropIfExists('sales_program_stops');
        Schema::dropIfExists('sales_program_areas');
    }

    private function seedImportedProgram(): void
    {
        $path = database_path('seeders/data/sales_program_import.json');

        if (! File::exists($path)) {
            return;
        }

        $rows = json_decode(File::get($path), true);

        if (! is_array($rows) || $rows === []) {
            return;
        }

        $salesReps = DB::table('users')
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->where('users.is_active', true)
            ->where('roles.code', 'SALES_REP')
            ->select('users.id', 'users.name')
            ->get()
            ->mapWithKeys(fn ($user) => [$this->normalize($user->name) => $user]);

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

        $now = now();
        $stops = [];
        $templates = [];
        $areas = [];
        $salesRepIds = [];
        $dates = [];

        foreach ($rows as $row) {
            $source = trim((string) ($row['workbook_stem'] ?? ''));
            $targetName = $aliases[$source] ?? $source;
            $salesRep = $salesReps->get($this->normalize($targetName));
            $area = trim((string) ($row['area'] ?? ''));
            $customer = trim((string) ($row['customer_label'] ?? ''));
            $date = trim((string) ($row['schedule_date'] ?? ''));
            $dayLabel = $this->canonicalDayLabel((string) ($row['day_label'] ?? ''));

            if (! $salesRep || $area === '' || $date === '' || $dayLabel === '') {
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

        DB::table('sales_program_stops')
            ->whereIn('sales_rep_id', array_values($salesRepIds))
            ->where(function ($query) use ($dates) {
                $query->whereIn('schedule_date', array_values($dates))->orWhereNull('schedule_date');
            })
            ->delete();

        foreach (array_chunk(array_merge($stops, array_values($templates)), 500) as $chunk) {
            DB::table('sales_program_stops')->insert($chunk);
        }

        foreach (array_chunk(array_values($areas), 500) as $chunk) {
            DB::table('sales_program_areas')->insertOrIgnore($chunk);
        }
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
};
