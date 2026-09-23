<?php

declare(strict_types=1);

use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Permission::findOrCreate('dashboard.view');
    Permission::findOrCreate('settings.view');
    Permission::findOrCreate('content.view');
    Permission::findOrCreate('media.view');
    Permission::findOrCreate('menus.view');
});

test('guest cannot access admin dashboard', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertRedirect(route('login'));
});

test('authenticated user without dashboard permission cannot access admin dashboard', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.dashboard'));

    $response->assertForbidden();
});

test('authenticated user with dashboard permission can access admin dashboard', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('dashboard.view');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.dashboard'));

    $response->assertOk();
});

test('admin dashboard renders admin shell', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('dashboard.view');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.dashboard'));

    $response->assertOk();

    $response->assertSee('Admin Dashboard');
    $response->assertSee('Dashboard');
});

test('admin dashboard does not render user app shell', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('dashboard.view');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.dashboard'));

    $response->assertOk();

    $response->assertDontSee('APP LAYOUT TEST');
});

test('settings navigation is hidden without settings permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('dashboard.view');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.dashboard'));

    $response->assertOk();

    $response->assertDontSee(
        'href="' . route('admin.settings.index') . '"',
        false,
    );
});

test('settings navigation is visible with settings permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'dashboard.view',
        'settings.view',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.dashboard'));

    $response->assertOk();

    $response->assertSee(
        'href="' . route('admin.settings.index') . '"',
        false,
    );
});

test('content navigation is hidden without content permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('dashboard.view');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.dashboard'));

    $response->assertOk();

    $response->assertDontSee(
        'href="' . route('admin.contents.index') . '"',
        false,
    );
});

test('content navigation is visible with content permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'dashboard.view',
        'content.view',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.dashboard'));

    $response->assertOk();

    $response->assertSee(
        'href="' . route('admin.contents.index') . '"',
        false,
    );
});

test('user menu displays account settings link', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('dashboard.view');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.dashboard'));

    $response->assertOk();

    $response->assertSee('Account Settings');
    $response->assertSee(
        'href="' . route('profile.edit') . '"',
        false,
    );
});

test('media navigation is hidden without media permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('dashboard.view');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.dashboard'));

    $response->assertOk();

    $response->assertDontSee(
        'href="' . route('admin.media.index') . '"',
        false,
    );
});

test('media navigation is visible with media permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'dashboard.view',
        'media.view',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.dashboard'));

    $response->assertOk();

    $response->assertSee(
        'href="' . route('admin.media.index') . '"',
        false,
    );
});

test('menu navigation is hidden without menus permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('dashboard.view');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.dashboard'));

    $response->assertOk();

    $response->assertDontSee(
        'href="' . route('admin.menus.index') . '"',
        false,
    );
});

test('menu navigation is visible with menus permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'dashboard.view',
        'menus.view',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.dashboard'));

    $response->assertOk();

    $response->assertSee(
        'href="' . route('admin.menus.index') . '"',
        false,
    );
});

test('media index renders admin shell', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'dashboard.view',
        'media.view',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.media.index'));

    $response->assertOk();

    $response->assertSee('Media Library');
    $response->assertSee('Dashboard');
    $response->assertSee('Settings');
});

test('menu index renders admin shell', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'dashboard.view',
        'menus.view',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.menus.index'));

    $response->assertOk();

    $response->assertSee('Menu Management');
    $response->assertSee('Dashboard');
    $response->assertSee('Settings');
});
