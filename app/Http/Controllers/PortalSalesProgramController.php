<?php

namespace App\Http\Controllers;

use App\Models\SalesProgramArea;
use App\Models\SalesProgramDayStatus;
use App\Models\SalesProgramStop;
use App\Models\User;
use App\Support\PortalAccess;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PortalSalesProgramController extends Controller
{
    private const WEEKDAYS = [
        'Δευτέρα',
        'Τρίτη',
        'Τετάρτη',
        'Πέμπτη',
        'Παρασκευή',
        'Σάββατο',
        'Κυριακή',
    ];

    public function index(Request $request): View
    {
        $user = $request->user()->load(['role', 'department', 'position']);
        $permissions = PortalAccess::permissions($user);
        abort_unless($permissions['can_view_sales_program'], 403);

        $selectedDate = $this->selectedDate($request->string('date')->toString());
        $dayLabel = self::WEEKDAYS[$selectedDate->dayOfWeekIso - 1];
        $viewMode = $request->query('view') === 'plan' ? 'plan' : 'today';
        $canManageAll = (bool) $permissions['can_manage_all_sales_programs'];
        $salesReps = $this->salesReps($user, $canManageAll);
        $visibleRepIds = $salesReps->pluck('id');
        $areaOptions = $this->areaOptions();

        $todayCards = $this->todayCards($salesReps, $selectedDate, $dayLabel);
        $templateStops = SalesProgramStop::query()
            ->whereIn('sales_rep_id', $visibleRepIds)
            ->whereNull('schedule_date')
            ->where('is_active', true)
            ->orderBy('day_label')
            ->orderBy('sort_order')
            ->get()
            ->groupBy(['sales_rep_id', 'day_label']);

        return view('portal.sales-program.index', [
            'user' => $user,
            'navigation' => PortalAccess::navigation($user),
            'selectedDate' => $selectedDate,
            'selectedDateInput' => $selectedDate->format('Y-m-d'),
            'selectedDateLabel' => $selectedDate->locale('el')->isoFormat('dddd D MMMM YYYY'),
            'dayLabel' => $dayLabel,
            'weekdays' => self::WEEKDAYS,
            'viewMode' => $viewMode,
            'canManageAll' => $canManageAll,
            'salesReps' => $salesReps,
            'todayCards' => $todayCards,
            'templateStops' => $templateStops,
            'areaOptions' => $areaOptions,
            'stats' => [
                'reps' => $todayCards->count(),
                'started' => $todayCards->filter(fn (array $card) => $card['status']?->started_at && ! $card['status']?->ended_at)->count(),
                'stops' => $todayCards->sum(fn (array $card) => $card['stops']->count()),
            ],
        ]);
    }

    public function storeArea(Request $request): RedirectResponse
    {
        $user = $request->user()->load(['role', 'department', 'position']);
        abort_unless(PortalAccess::permissions($user)['can_manage_all_sales_programs'], 403);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:120'],
        ]);

        SalesProgramArea::updateOrCreate(
            ['label' => trim($validated['label'])],
            ['is_active' => true, 'created_by_user_id' => $user->id]
        );

        return back()->with('status', 'Η περιοχή προστέθηκε στο πρόγραμμα πωλητών.');
    }

    public function storeStop(Request $request): RedirectResponse
    {
        $user = $request->user()->load(['role', 'department', 'position']);
        $permissions = PortalAccess::permissions($user);
        abort_unless($permissions['can_view_sales_program'], 403);

        $validated = $request->validate([
            'stop_id' => ['nullable', 'integer'],
            'sales_rep_id' => ['required', 'integer', 'exists:users,id'],
            'day_label' => ['required', Rule::in(self::WEEKDAYS)],
            'schedule_date' => ['nullable', 'date_format:Y-m-d'],
            'area' => ['required', 'string', 'max:120'],
            'customer_label' => ['nullable', 'string', 'max:160'],
            'note' => ['nullable', 'string', 'max:220'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:99'],
        ]);

        $salesRepId = (int) $validated['sales_rep_id'];
        abort_unless($permissions['can_manage_all_sales_programs'] || $user->id === $salesRepId, 403);

        $payload = [
            'sales_rep_id' => $salesRepId,
            'day_label' => $validated['day_label'],
            'schedule_date' => $validated['schedule_date'] ?? null,
            'area' => trim($validated['area']),
            'customer_label' => ($validated['customer_label'] ?? null) ? trim($validated['customer_label']) : null,
            'note' => ($validated['note'] ?? null) ? trim($validated['note']) : null,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => true,
        ];

        if (! empty($validated['stop_id'])) {
            $stop = SalesProgramStop::findOrFail($validated['stop_id']);
            abort_unless($permissions['can_manage_all_sales_programs'] || $stop->sales_rep_id === $user->id, 403);
            $stop->update($payload);
        } else {
            SalesProgramStop::create($payload);
        }

        SalesProgramArea::firstOrCreate(['label' => $payload['area']], [
            'is_active' => true,
            'created_by_user_id' => $user->id,
        ]);

        return back()->with('status', 'Το πρόγραμμα πωλητή ενημερώθηκε.');
    }

    public function destroyStop(Request $request, SalesProgramStop $stop): RedirectResponse
    {
        $user = $request->user()->load(['role', 'department', 'position']);
        $permissions = PortalAccess::permissions($user);

        abort_unless($permissions['can_manage_all_sales_programs'] || $stop->sales_rep_id === $user->id, 403);

        $stop->delete();

        return back()->with('status', 'Η στάση αφαιρέθηκε από το πρόγραμμα.');
    }

    public function startDay(Request $request): RedirectResponse
    {
        $user = $request->user()->load(['role', 'department', 'position']);
        abort_unless(PortalAccess::permissions($user)['can_view_sales_program'], 403);

        $validated = $request->validate([
            'schedule_date' => ['required', 'date_format:Y-m-d'],
        ]);

        $date = $this->selectedDate($validated['schedule_date'])->format('Y-m-d');

        $status = SalesProgramDayStatus::query()
            ->where('sales_rep_id', $user->id)
            ->whereDate('schedule_date', $date)
            ->first() ?? new SalesProgramDayStatus([
                'sales_rep_id' => $user->id,
                'schedule_date' => $date,
            ]);

        $status->fill(['started_at' => now(), 'ended_at' => null])->save();

        return back()->with('status', 'Η ημέρα πεδίου ξεκίνησε.');
    }

    public function endDay(Request $request): RedirectResponse
    {
        $user = $request->user()->load(['role', 'department', 'position']);
        abort_unless(PortalAccess::permissions($user)['can_view_sales_program'], 403);

        $validated = $request->validate([
            'schedule_date' => ['required', 'date_format:Y-m-d'],
        ]);

        $date = $this->selectedDate($validated['schedule_date'])->format('Y-m-d');

        $status = SalesProgramDayStatus::query()
            ->where('sales_rep_id', $user->id)
            ->whereDate('schedule_date', $date)
            ->first() ?? new SalesProgramDayStatus([
                'sales_rep_id' => $user->id,
                'schedule_date' => $date,
            ]);

        $status->fill([
            'started_at' => $status->started_at ?? now(),
            'ended_at' => now(),
        ])->save();

        return back()->with('status', 'Η ημέρα πεδίου έκλεισε.');
    }

    private function selectedDate(?string $date): CarbonImmutable
    {
        try {
            return $date
                ? CarbonImmutable::createFromFormat('Y-m-d', $date, 'Europe/Athens')->startOfDay()
                : CarbonImmutable::now('Europe/Athens')->startOfDay();
        } catch (\Throwable) {
            return CarbonImmutable::now('Europe/Athens')->startOfDay();
        }
    }

    private function salesReps(User $user, bool $canManageAll): Collection
    {
        $query = User::query()
            ->with(['role', 'department', 'position'])
            ->where('is_active', true)
            ->whereHas('role', fn ($role) => $role->where('code', 'SALES_REP'))
            ->orderBy('name');

        if (! $canManageAll) {
            $query->where('id', $user->id);
        }

        return $query->get();
    }

    private function todayCards(Collection $salesReps, CarbonImmutable $date, string $dayLabel): Collection
    {
        $repIds = $salesReps->pluck('id');
        $dateValue = $date->format('Y-m-d');
        $exactStops = SalesProgramStop::query()
            ->whereIn('sales_rep_id', $repIds)
            ->whereDate('schedule_date', $dateValue)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('sales_rep_id');
        $templateStops = SalesProgramStop::query()
            ->whereIn('sales_rep_id', $repIds)
            ->whereNull('schedule_date')
            ->where('day_label', $dayLabel)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('sales_rep_id');
        $statuses = SalesProgramDayStatus::query()
            ->whereIn('sales_rep_id', $repIds)
            ->whereDate('schedule_date', $dateValue)
            ->get()
            ->keyBy('sales_rep_id');

        return $salesReps
            ->map(function (User $rep) use ($exactStops, $templateStops, $statuses) {
                $stops = $exactStops->get($rep->id);
                if ($stops === null || $stops->isEmpty()) {
                    $stops = $templateStops->get($rep->id, collect());
                }

                return [
                    'rep' => $rep,
                    'stops' => $stops,
                    'status' => $statuses->get($rep->id),
                ];
            })
            ->filter(fn (array $card) => $card['stops']->isNotEmpty() || $card['rep']->id === auth()->id())
            ->values();
    }

    private function areaOptions(): Collection
    {
        $savedAreas = SalesProgramArea::query()
            ->where('is_active', true)
            ->orderBy('label')
            ->pluck('label');
        $usedAreas = SalesProgramStop::query()
            ->where('is_active', true)
            ->distinct()
            ->orderBy('area')
            ->pluck('area');

        return $savedAreas->merge($usedAreas)->filter()->unique()->values();
    }
}
