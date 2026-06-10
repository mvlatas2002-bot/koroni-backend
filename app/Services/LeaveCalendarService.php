<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\CompanyHoliday;
use App\Models\LeaveBalance;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class LeaveCalendarService
{
    public function visibleUsersFor(User $viewer): EloquentCollection
    {
        $viewer->loadMissing(['role', 'department']);

        $canSeeCompany = $this->canSeeCompanyCalendar($viewer);

        return User::with(['role', 'department'])
            ->where('is_active', true)
            ->when(!$canSeeCompany, fn ($query) => $query->where('department_id', $viewer->department_id))
            ->orderBy('name')
            ->get();
    }

    public function canSeeCompanyCalendar(User $viewer): bool
    {
        $viewer->loadMissing(['role', 'department']);

        $roleCode = $viewer->role?->code;
        $departmentCode = $viewer->department?->code;

        return in_array($roleCode, [
            'OPERATIONS_ADMIN',
            'SYSTEM_ADMIN',
            'MANAGEMENT',
            'COMMERCIAL_DIRECTOR',
            'SUPERVISOR',
        ], true) || in_array($departmentCode, [
            'OPERATIONS_DEPT',
            'ACCOUNTING_DEPT',
            'LOGISTICS_FUNCTION',
        ], true);
    }

    public function canManageBalances(User $viewer): bool
    {
        $viewer->loadMissing(['role', 'department']);

        return in_array($viewer->role?->code, ['OPERATIONS_ADMIN', 'SYSTEM_ADMIN', 'MANAGEMENT'], true)
            || $viewer->department?->code === 'ACCOUNTING_DEPT';
    }

    public function workingDatesBetween(string|Carbon $startsOn, string|Carbon $endsOn): array
    {
        $start = Carbon::parse($startsOn)->startOfDay();
        $end = Carbon::parse($endsOn)->startOfDay();

        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        $holidays = CompanyHoliday::query()
            ->whereBetween('holiday_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->mapWithKeys(fn (CompanyHoliday $holiday) => [$holiday->holiday_date->toDateString() => $holiday->name]);

        $charged = [];
        $excluded = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dateKey = $date->toDateString();

            if ($date->isWeekend()) {
                $excluded[$dateKey] = 'Σαββατοκύριακο';
                continue;
            }

            if ($holidays->has($dateKey)) {
                $excluded[$dateKey] = $holidays[$dateKey];
                continue;
            }

            $charged[] = $dateKey;
        }

        return [
            'charged_dates' => $charged,
            'excluded_dates' => $excluded,
            'charged_days' => count($charged),
        ];
    }

    public function monthCalendar(Carbon|string|null $month = null): array
    {
        $current = $month ? Carbon::parse($month)->startOfMonth() : now()->startOfMonth();
        $start = $current->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $end = $current->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $holidays = CompanyHoliday::whereBetween('holiday_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn (CompanyHoliday $holiday) => $holiday->holiday_date->toDateString());

        $weeks = [];
        $week = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $key = $date->toDateString();
            $holiday = $holidays->get($key);
            $week[] = [
                'date' => $key,
                'day' => $date->day,
                'is_current_month' => $date->isSameMonth($current),
                'is_today' => $date->isToday(),
                'is_weekend' => $date->isWeekend(),
                'holiday' => $holiday?->name,
                'is_working' => !$date->isWeekend() && !$holiday,
            ];

            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }
        }

        return [
            'month' => $current,
            'previous' => $current->copy()->subMonth(),
            'next' => $current->copy()->addMonth(),
            'weeks' => $weeks,
        ];
    }

    public function eventsFor(User $viewer, Carbon|string|null $month = null): Collection
    {
        $calendar = $this->monthCalendar($month);
        $monthStart = $calendar['month']->copy()->startOfMonth();
        $monthEnd = $calendar['month']->copy()->endOfMonth();
        $visibleUsers = $this->visibleUsersFor($viewer);
        $visibleUserIds = $visibleUsers->pluck('id');

        $events = collect();

        CompanyHoliday::whereBetween('holiday_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get()
            ->each(function (CompanyHoliday $holiday) use ($events) {
                $events->push([
                    'date' => $holiday->holiday_date->toDateString(),
                    'type' => 'holiday',
                    'title' => $holiday->name,
                    'meta' => 'Αργία',
                    'status' => 'holiday',
                ]);
            });

        $visibleUsers
            ->filter(fn (User $user) => $user->birth_date !== null)
            ->each(function (User $user) use ($events, $monthStart) {
                if ((int) $user->birth_date->format('m') !== $monthStart->month) {
                    return;
                }

                $events->push([
                    'date' => Carbon::create($monthStart->year, $monthStart->month, (int) $user->birth_date->format('d'))->toDateString(),
                    'type' => 'birthday',
                    'title' => $user->name,
                    'meta' => 'Γενέθλια',
                    'status' => 'birthday',
                ]);
            });

        ApprovalRequest::with(['requester.department'])
            ->where('workflow_type', 'leave')
            ->whereIn('status', ['pending', 'approved'])
            ->whereIn('requester_id', $visibleUserIds)
            ->whereDate('starts_on', '<=', $monthEnd->toDateString())
            ->whereDate('ends_on', '>=', $monthStart->toDateString())
            ->get()
            ->each(function (ApprovalRequest $request) use ($events, $monthStart, $monthEnd) {
                $start = $request->starts_on->copy()->max($monthStart);
                $end = $request->ends_on->copy()->min($monthEnd);

                for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                    $events->push([
                        'date' => $date->toDateString(),
                        'type' => 'leave',
                        'title' => $request->requester->name,
                        'meta' => $request->status === 'approved' ? 'Εγκρίθηκε' : 'Σε αναμονή',
                        'status' => $request->status,
                        'request_id' => $request->id,
                    ]);
                }
            });

        return $events->groupBy('date');
    }

    public function balanceFor(User $user, ?int $year = null): array
    {
        $year ??= now()->year;
        $balance = LeaveBalance::firstOrCreate(
            ['user_id' => $user->id, 'year' => $year],
            ['annual_entitlement' => 22, 'manual_adjustment' => 0]
        );

        $today = now()->toDateString();
        $approvedLeaves = ApprovalRequest::where('workflow_type', 'leave')
            ->where('requester_id', $user->id)
            ->where('status', 'approved')
            ->whereYear('starts_on', '<=', $year)
            ->whereYear('ends_on', '>=', $year)
            ->get();

        $usedToDate = 0;
        $futureScheduled = 0;

        foreach ($approvedLeaves as $leave) {
            $range = $this->workingDatesBetween(
                max($leave->starts_on->toDateString(), "{$year}-01-01"),
                min($leave->ends_on->toDateString(), "{$year}-12-31")
            );

            foreach ($range['charged_dates'] as $date) {
                if ($date <= $today) {
                    $usedToDate++;
                } else {
                    $futureScheduled++;
                }
            }
        }

        $entitlement = (float) $balance->annual_entitlement + (float) $balance->manual_adjustment;

        return [
            'record' => $balance,
            'year' => $year,
            'annual_entitlement' => (float) $balance->annual_entitlement,
            'manual_adjustment' => (float) $balance->manual_adjustment,
            'total_entitlement' => $entitlement,
            'used_to_date' => $usedToDate,
            'future_scheduled' => $futureScheduled,
            'remaining_now' => $entitlement - $usedToDate,
            'remaining_after_scheduled' => $entitlement - $usedToDate - $futureScheduled,
        ];
    }

    public function balanceRowsFor(User $viewer, ?int $year = null): Collection
    {
        return $this->visibleUsersFor($viewer)
            ->map(fn (User $user) => [
                'user' => $user,
                'balance' => $this->balanceFor($user, $year),
            ]);
    }
}
