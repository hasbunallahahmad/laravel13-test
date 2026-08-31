<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Settings\SettingController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware([
        'auth',
        'permission:dashboard.view',
    ])
    ->name('dashboard');

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::middleware('permission:settings.view')
            ->get('/settings', [SettingController::class, 'index'])
            ->name('settings.index');

        Route::middleware('permission:settings.update')
            ->put('/settings/{group}/{key}', [SettingController::class, 'update'])
            ->name('settings.update');
    });

// Route::prefix('admin')
//     ->middleware(['auth'])
//     ->group(function () {

//         Route::get('/settings', [SettingController::class, 'index'])
//             ->middleware('permission:settings.view');

//         Route::put('/settings/{group}/{key}', [SettingController::class, 'update'])
//             ->middleware('permission:settings.update');
//     });

require __DIR__ . '/settings.php';
