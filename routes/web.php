<?php

use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Settings\SettingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MediaPickerController;

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

        Route::get('/dashboard', [DashboardController::class, 'index'])
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

        Route::get('/menus/{menu}/edit', [MenuController::class, 'edit'])
            ->middleware('permission:menus.view')
            ->name('menus.edit');

        Route::post('/menus', [MenuController::class, 'store'])
            ->middleware('permission:menus.view')
            ->name('menus.store');

        Route::patch('/menus/{menu}', [MenuController::class, 'update'])
            ->middleware('permission:menus.view')
            ->name('menus.update');

        Route::delete('/menus/{menu}', [MenuController::class, 'destroy'])
            ->middleware('permission:menus.view')
            ->name('menus.destroy');

        Route::get('/menus/trash', [MenuController::class, 'trash'])
            ->middleware('permission:menus.view')
            ->name('menus.trash');

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

        Route::get('/media/picker', [MediaPickerController::class, 'index'])
            ->middleware('permission:media.view')
            ->name('media.picker');

        Route::get('/media/trash', [MediaController::class, 'trash'])
            ->middleware('permission:media.view')
            ->name('media.trash');

        Route::get('/media/{media}', [MediaController::class, 'show'])
            ->middleware('permission:media.view')
            ->name('media.show');

        Route::get('/media/{media}/edit', [MediaController::class, 'edit'])
            ->middleware('permission:media.view|media.update')
            ->name('media.edit');

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

        Route::get('/media/{media}/preview', [MediaController::class, 'preview'])
            ->middleware('permission:media.view')
            ->name('media.preview');

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

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        Route::get('/users', [UserController::class, 'index'])
            ->middleware('permission:users.view')
            ->name('users.index');

        Route::get('/users/create', [UserController::class, 'create'])
            ->middleware('permission:users.create')
            ->name('users.create');

        Route::post('/users', [UserController::class, 'store'])
            ->middleware('permission:users.create')
            ->name('users.store');

        Route::get('/users/{user}/edit', [UserController::class, 'edit'])
            ->middleware('permission:users.update')
            ->name('users.edit');

        Route::patch('/users/{user}', [UserController::class, 'update'])
            ->middleware('permission:users.update')
            ->name('users.update');

        Route::delete('/users/{user}', [UserController::class, 'destroy'])
            ->middleware('permission:users.delete')
            ->name('users.destroy');

        Route::get('/users/trash', [UserController::class, 'trash'])
            ->middleware('permission:users.view')
            ->name('users.trash');

        Route::patch('/users/{user}/restore', [UserController::class, 'restore'])
            ->middleware('permission:users.update')
            ->name('users.restore');

        Route::delete('/users/{user}/force-destroy', [UserController::class, 'forceDestroy'])
            ->middleware('permission:users.force-delete')
            ->name('users.force-destroy');
    });


/*
|--------------------------------------------------------------------------
| User Settings
|--------------------------------------------------------------------------
*/
require __DIR__ . '/settings.php';
