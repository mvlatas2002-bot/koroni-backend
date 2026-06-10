<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LeaveCalendarService;
use App\Support\PortalAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalLeaveCalendarController extends Controller
{
    public function index(Request $request, LeaveCalendarService $calendar): View
    {
        $user = $request->user()->load(['role', 'department', 'manager', 'secondaryApprover', 'actingManager']);
        $month = $request->query('month');

        return view('portal.leave-calendar.index', [
            'user' => $user,
            'calendar' => $calendar->monthCalendar($month),
            'eventsByDate' => $calendar->eventsFor($user, $month),
            'myBalance' => $calendar->balanceFor($user),
            'balanceRows' => $calendar->canManageBalances($user) ? $calendar->balanceRowsFor($user) : collect(),
            'canManageBalances' => $calendar->canManageBalances($user),
            'canSeeCompanyCalendar' => $calendar->canSeeCompanyCalendar($user),
            'permissions' => PortalAccess::permissions($user),
        ]);
    }

    public function updateBalance(Request $request, User $user, LeaveCalendarService $calendar): RedirectResponse
    {
        $viewer = $request->user()->load(['role', 'department']);
        abort_unless($calendar->canManageBalances($viewer), 403);

        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'annual_entitlement' => ['required', 'numeric', 'min:0', 'max:99'],
            'manual_adjustment' => ['nullable', 'numeric', 'min:-99', 'max:99'],
            'notes' => ['nullable', 'string', 'max:180'],
        ]);

        $user->leaveBalances()->updateOrCreate(
            ['year' => (int) $data['year']],
            [
                'annual_entitlement' => $data['annual_entitlement'],
                'manual_adjustment' => $data['manual_adjustment'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ]
        );

        return back()->with('status', 'Το υπόλοιπο άδειας ενημερώθηκε.');
    }
}
