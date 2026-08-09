<?php

use App\Http\Controllers\Tenant\DocumentController;
use App\Http\Controllers\Tenant\HomeController;
use App\Http\Controllers\Tenant\MaintenanceController;
use App\Http\Controllers\Tenant\MyHomeController;
use App\Http\Controllers\Tenant\NoticeController;
use App\Http\Controllers\Tenant\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'organization', 'role:tenant'])
    ->prefix('tenant')
    ->name('tenant.')
    ->group(function () {
        Route::get('/home', [HomeController::class, 'index'])->name('home');
        Route::get('/my-home', [MyHomeController::class, 'index'])->name('my-home');

        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        Route::get('/payments/{payment}/receipt', [PaymentController::class, 'downloadReceipt'])->name('payments.receipt');

        Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
        Route::get('/maintenance/create', [MaintenanceController::class, 'create'])->name('maintenance.create');
        Route::post('/maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
        Route::get('/maintenance/{maintenance}', [MaintenanceController::class, 'show'])->name('maintenance.show');
        Route::post('/maintenance/{maintenance}/comments', [MaintenanceController::class, 'storeComment'])->name('maintenance.comments.store');
        Route::get('/maintenance/{maintenance}/photos/{index}', [MaintenanceController::class, 'showPhoto'])->name('maintenance.photos.show');

        Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');

        Route::get('/notices', [NoticeController::class, 'index'])->name('notices.index');
        Route::get('/notices/{notice}', [NoticeController::class, 'show'])->name('notices.show');
    });
