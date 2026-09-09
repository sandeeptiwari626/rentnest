<?php

use App\Http\Controllers\Landlord\DashboardController;
use App\Http\Controllers\Landlord\DocumentController;
use App\Http\Controllers\Landlord\ExpenseController;
use App\Http\Controllers\Landlord\LeaseController;
use App\Http\Controllers\Landlord\MaintenanceController;
use App\Http\Controllers\Landlord\NoticeController;
use App\Http\Controllers\Landlord\PaymentController;
use App\Http\Controllers\Landlord\PortalNoticeController;
use App\Http\Controllers\Landlord\PropertyController;
use App\Http\Controllers\Landlord\ReportController;
use App\Http\Controllers\Landlord\SettingsController;
use App\Http\Controllers\Landlord\TenantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Shared\NotificationController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class)->name('home');

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user?->isLandlord()) {
        return redirect()->route('landlord.dashboard');
    }

    if ($user?->isTenant()) {
        return redirect()->route('tenant.home');
    }

    return redirect()->route('login');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
});

Route::middleware(['auth', 'verified', 'organization', 'role:landlord'])
    ->prefix('landlord')
    ->name('landlord.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('properties/{property}/photos/{index}', [PropertyController::class, 'photo'])
            ->name('properties.photo');
        Route::resource('properties', PropertyController::class);

        Route::resource('tenants', TenantController::class);

        Route::post('leases/{lease}/activate', [LeaseController::class, 'activate'])->name('leases.activate');
        Route::post('leases/{lease}/terminate', [LeaseController::class, 'terminate'])->name('leases.terminate');
        Route::post('leases/{lease}/expire', [LeaseController::class, 'expire'])->name('leases.expire');
        Route::resource('leases', LeaseController::class);

        Route::get('payments/{payment}/receipt', [PaymentController::class, 'downloadReceipt'])
            ->name('payments.receipt');
        Route::get('payments/{payment}/proof', [PaymentController::class, 'proof'])
            ->name('payments.proof');
        Route::delete('payments/bulk-destroy', [PaymentController::class, 'bulkDestroy'])
            ->name('payments.bulk-destroy');
        Route::resource('payments', PaymentController::class)->except(['destroy']);

        Route::get('maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
        Route::get('maintenance/{maintenance}', [MaintenanceController::class, 'show'])->name('maintenance.show');
        Route::patch('maintenance/{maintenance}', [MaintenanceController::class, 'update'])->name('maintenance.update');
        Route::post('maintenance/{maintenance}/comments', [MaintenanceController::class, 'storeComment'])
            ->name('maintenance.comments.store');

        Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::get('documents/create', [DocumentController::class, 'create'])->name('documents.create');
        Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

        Route::resource('expenses', ExpenseController::class)->except(['edit', 'update']);

        Route::resource('notices', NoticeController::class)->except(['edit', 'update']);

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::get('portal-notice', [PortalNoticeController::class, 'index'])->name('portal-notice.index');
        Route::get('portal-notice/create', [PortalNoticeController::class, 'create'])->name('portal-notice.create');
        Route::post('portal-notice', [PortalNoticeController::class, 'store'])->name('portal-notice.store');
        Route::post('portal-notice/{portalNotice}/activate', [PortalNoticeController::class, 'activate'])
            ->name('portal-notice.activate');
    });

require __DIR__.'/tenant.php';
require __DIR__.'/auth.php';
