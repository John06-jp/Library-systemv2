<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeveloperBrandingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'developer'])->prefix('developer')->name('developer.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'developer'])->name('dashboard');

    Route::get('/branding', [DeveloperBrandingController::class, 'edit'])->name('branding.edit');
    Route::put('/branding', [DeveloperBrandingController::class, 'update'])->name('branding.update');
    Route::post('/branding/restore', [DeveloperBrandingController::class, 'restore'])->name('branding.restore');

    Route::get('/branding/activity', [DeveloperBrandingController::class, 'activity'])->name('branding.activity');
    Route::get('/branding/versions', [DeveloperBrandingController::class, 'versions'])->name('branding.versions');
    Route::post('/branding/versions/{version}/restore', [DeveloperBrandingController::class, 'restoreVersion'])
        ->name('branding.restore-version');
});