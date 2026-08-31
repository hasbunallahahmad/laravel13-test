<?php

declare(strict_types=1);

use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Permission::findOrCreate('dashboard.view');
    Permission::findOrCreate('settings.view');
});

test('authenticated user with dashboard permission can access admin dashboard', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('dashboard.view');

    $response = $this
        ->actingAs($user)
        ->get('/admin/dashboard');

    $response->assertOk();
});

test('guest cannot access admin dashboard', function () {
    $response = $this->get('/admin/dashboard');

    $response->assertRedirect(route('login'));
});

test('authenticated user without dashboard permission cannot access admin dashboard', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/admin/dashboard');

    $response->assertForbidden();
});

test('admin dashboard renders the admin shell', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('dashboard.view');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.dashboard'));

    $response->assertOk();

    $response->assertSee('Admin Dashboard');
    $response->assertSee('Dashboard');
});

test('admin navigation shows settings only to authorized users', function () {
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

test('admin navigation shows settings to users with settings permission', function () {
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

test('admin dashboard does not render settings navigation without permission', function () {
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

test('admin dashboard renders settings navigation with permission', function () {
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
