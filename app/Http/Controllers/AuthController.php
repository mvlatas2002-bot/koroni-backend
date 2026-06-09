<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\AuthenticatedUserPayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $user = User::with(['role', 'department', 'position', 'manager', 'secondaryApprover', 'actingManager'])
            ->where('email', $credentials['email'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Τα στοιχεία σύνδεσης δεν είναι σωστά.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Ο λογαριασμός είναι ανενεργός.'],
            ]);
        }

        $token = $user->createToken($credentials['device_name'] ?: 'api-client')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => AuthenticatedUserPayload::user($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->load(['role', 'department', 'position', 'manager', 'secondaryApprover', 'actingManager']);

        return response()->json([
            'user' => AuthenticatedUserPayload::user($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->currentAccessToken()?->delete();

        return response()->json([
            'ok' => true,
        ]);
    }
}
