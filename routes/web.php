<?php

use App\Http\Controllers\Admin\Settings\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');


/*
|--------------------------------------------------------------------------
| User Dashboard
|--------------------------------------------------------------------------
|
| Dashboard utama aplikasi setelah login.
|
*/

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware([
    'auth',
    'permission:dashboard.view',
])->name('dashboard');


/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
|
| Seluruh area admin menggunakan prefix /admin.
|
*/

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Admin Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', function () {
            return view('admin.dashboard.index');
        })
            ->middleware('permission:dashboard.view')
            ->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Settings
        |--------------------------------------------------------------------------
        */

        Route::get('/settings', [SettingController::class, 'index'])
            ->middleware('permission:settings.view')
            ->name('settings.index');

        Route::put('/settings/{group}/{key}', [SettingController::class, 'update'])
            ->middleware('permission:settings.update')
            ->name('settings.update');
    });


/*
|--------------------------------------------------------------------------
| User Settings
|--------------------------------------------------------------------------
*/

require __DIR__ . '/settings.php';
