<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PortalProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('portal.profile.edit', [
            'user' => $request->user()->load(['role', 'department', 'position', 'manager', 'secondaryApprover']),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:40'],
            'profile_notes' => ['nullable', 'string', 'max:1000'],
            'current_password' => ['nullable', 'required_with:new_password', 'current_password'],
            'new_password' => ['nullable', 'confirmed', Password::min(1)],
        ]);

        $user->fill([
            'name' => $data['name'],
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'birth_date' => $data['birth_date'] ?? null,
            'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
            'profile_notes' => $data['profile_notes'] ?? null,
        ]);

        if (! empty($data['new_password'])) {
            $user->password = Hash::make($data['new_password']);
        }

        $user->save();

        EmployeeProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $user->name,
                'email' => $user->email,
                'employment_type' => 'internal',
                'employment_status' => $user->employment_status ?: 'active',
                'is_external_collaborator' => false,
                'is_active' => $user->is_active,
                'annual_leave_allowance' => 22,
            ]
        );

        return redirect()
            ->route('portal.profile.edit')
            ->with('status', 'Το προφίλ σου ενημερώθηκε και αποθηκεύτηκε στη βάση.');
    }
}
