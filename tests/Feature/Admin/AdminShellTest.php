<?php

declare(strict_types=1);

use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Permission::findOrCreate('dashboard.view');
    Permission::findOrCreate('settings.view');
    Permission::findOrCreate('content.view');
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
