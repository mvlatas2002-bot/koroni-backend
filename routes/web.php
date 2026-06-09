<?php

use App\Http\Controllers\PortalAuthController;
use App\Http\Controllers\PortalApprovalAuthorityController;
use App\Http\Controllers\PortalApprovalRequestController;
use App\Http\Controllers\PortalDashboardController;
use App\Http\Controllers\PortalDepartmentController;
use App\Http\Controllers\PortalModuleController;
use App\Http\Controllers\PortalOrganizationController;
use App\Http\Controllers\PortalProfileController;
use App\Http\Controllers\PortalUserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/health', function () {
    return response()->json([
        'ok' => true,
        'app' => config('app.name'),
        'time' => now()->toDateTimeString(),
    ]);
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [PortalAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [PortalAuthController::class, 'login'])->name('portal.login.submit');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', PortalDashboardController::class)->name('portal.dashboard');
    Route::get('/profile', [PortalProfileController::class, 'edit'])->name('portal.profile.edit');
    Route::put('/profile', [PortalProfileController::class, 'update'])->name('portal.profile.update');
    Route::get('/approval-requests', [PortalApprovalRequestController::class, 'index'])->name('portal.approvals.index');
    Route::get('/approval-requests/pending', [PortalApprovalRequestController::class, 'pending'])->name('portal.approvals.pending');
    Route::get('/approval-requests/create', [PortalApprovalRequestController::class, 'create'])->name('portal.approvals.create');
    Route::post('/approval-requests', [PortalApprovalRequestController::class, 'store'])->name('portal.approvals.store');
    Route::get('/approval-requests/{approvalRequest}', [PortalApprovalRequestController::class, 'show'])->name('portal.approvals.show');
    Route::post('/approval-requests/{approvalRequest}/decision', [PortalApprovalRequestController::class, 'decide'])->name('portal.approvals.decide');
    Route::get('/approval-authorities', [PortalApprovalAuthorityController::class, 'index'])->name('portal.approval-authorities.index');
    Route::post('/approval-authorities', [PortalApprovalAuthorityController::class, 'store'])->name('portal.approval-authorities.store');
    Route::put('/approval-authorities/{approvalAuthority}', [PortalApprovalAuthorityController::class, 'update'])->name('portal.approval-authorities.update');
    Route::get('/modules', [PortalModuleController::class, 'index'])->name('portal.modules.index');
    Route::get('/modules/{module}', [PortalModuleController::class, 'show'])->name('portal.modules.show');
    Route::get('/organization', PortalOrganizationController::class)->name('portal.organization.index');
    Route::get('/organization/units', [PortalDepartmentController::class, 'index'])->name('portal.organization.units');
    Route::post('/organization/units', [PortalDepartmentController::class, 'store'])->name('portal.organization.units.store');
    Route::put('/organization/units/{department}', [PortalDepartmentController::class, 'update'])->name('portal.organization.units.update');
    Route::get('/users', [PortalUserController::class, 'index'])->name('portal.users.index');
    Route::get('/users/create', [PortalUserController::class, 'create'])->name('portal.users.create');
    Route::post('/users', [PortalUserController::class, 'store'])->name('portal.users.store');
    Route::get('/users/{user}/edit', [PortalUserController::class, 'edit'])->name('portal.users.edit');
    Route::put('/users/{user}', [PortalUserController::class, 'update'])->name('portal.users.update');
    Route::post('/logout', [PortalAuthController::class, 'logout'])->name('portal.logout');
});
