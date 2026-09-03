<?php

use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
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
        | Menus
        |--------------------------------------------------------------------------
        */
        Route::get('/menus', [MenuController::class, 'index'])
            ->middleware('permission:menus.view')
            ->name('menus.index');

        Route::get('/menus/create', [MenuController::class, 'create'])
            ->middleware('permission:menus.view')
            ->name('menus.create');

        Route::post('/menus', [MenuController::class, 'store'])
            ->middleware('permission:menus.view')
            ->name('menus.store');

        Route::patch('/menus/{menu}', [MenuController::class, 'update'])
            ->middleware('permission:menus.view')
            ->name('menus.update');

        Route::delete('/menus/{menu}', [MenuController::class, 'destroy'])
            ->middleware('permission:menus.view')
            ->name('menus.destroy');

        /*
        |--------------------------------------------------------------------------
        | Settings
        |--------------------------------------------------------------------------
        */

        Route::get('/settings', [SettingController::class, 'index'])
            ->middleware('permission:settings.view')
            ->name('settings.index');

        Route::get('/settings/{group}/{key}/edit', [SettingController::class, 'edit'])
            ->middleware('permission:settings.update')
            ->name('settings.edit');

        Route::put('/settings/{group}/{key}', [SettingController::class, 'update'])
            ->middleware('permission:settings.update')
            ->name('settings.update');

        /*
|--------------------------------------------------------------------------
| Media
|--------------------------------------------------------------------------
*/

        Route::get('/media', [MediaController::class, 'index'])
            ->middleware('permission:media.view')
            ->name('media.index');

        Route::post('/media', [MediaController::class, 'store'])
            ->middleware('permission:media.create')
            ->name('media.store');

        Route::get('/media/{media}', [MediaController::class, 'show'])
            ->middleware('permission:media.view')
            ->name('media.show');

        Route::patch('/media/{media}', [MediaController::class, 'update'])
            ->middleware('permission:media.update')
            ->name('media.update');

        Route::delete('/media/{media}', [MediaController::class, 'destroy'])
            ->middleware('permission:media.delete')
            ->name('media.destroy');

        Route::patch('/media/{media}/restore', [MediaController::class, 'restore'])
            ->middleware('permission:media.restore')
            ->withTrashed()
            ->name('media.restore');

        Route::delete('/media/{media}/force-delete', [MediaController::class, 'forceDestroy'])
            ->middleware('permission:media.force-delete')
            ->withTrashed()
            ->name('media.force-delete');

        /*
        |--------------------------------------------------------------------------
        | Content
        |--------------------------------------------------------------------------
        */

        Route::get('/contents', [ContentController::class, 'index'])
            ->middleware('permission:content.view')
            ->name('contents.index');

        Route::get('/contents/create', [ContentController::class, 'create'])
            ->middleware('permission:content.create')
            ->name('contents.create');

        Route::post('/contents', [ContentController::class, 'store'])
            ->middleware('permission:content.create')
            ->name('contents.store');

        Route::get('/contents/trash', [ContentController::class, 'trash'])
            ->middleware('permission:content.view')
            ->name('contents.trash');

        Route::get('/contents/{content}/edit', [ContentController::class, 'edit'])
            ->middleware('permission:content.update')
            ->name('contents.edit');

        Route::put('/contents/{content}', [ContentController::class, 'update'])
            ->middleware('permission:content.update')
            ->name('contents.update');

        Route::delete('/contents/{content}', [ContentController::class, 'destroy'])
            ->middleware('permission:content.delete')
            ->name('contents.destroy');

        Route::patch('/contents/{content}/restore', [ContentController::class, 'restore'])
            ->middleware('permission:content.restore')
            ->withTrashed()
            ->name('contents.restore');

        Route::delete('/contents/{content}/force', [ContentController::class, 'forceDestroy'])
            ->middleware('permission:content.force-delete')
            ->withTrashed()
            ->name('contents.force-destroy');

        Route::patch('/menus/{menu}/restore', [MenuController::class, 'restore'])
            ->middleware('permission:menus.view')
            ->withTrashed()
            ->name('menus.restore');
    });


/*
|--------------------------------------------------------------------------
| User Settings
|--------------------------------------------------------------------------
*/
require __DIR__ . '/settings.php';
