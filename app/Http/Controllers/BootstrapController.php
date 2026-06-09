<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\AuthenticatedUserPayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BootstrapController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->load(['role', 'department', 'position', 'manager', 'secondaryApprover', 'actingManager']);

        return response()->json(AuthenticatedUserPayload::bootstrap($user));
    }
}
