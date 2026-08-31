<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

test('authenticated users without dashboard permission cannot visit the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    $response->assertForbidden();
});

test('authenticated users with dashboard permission can visit the dashboard', function () {
    Permission::findOrCreate(
        'dashboard.view',
        'web',
    );

    $user = User::factory()->create();

    $user->givePermissionTo('dashboard.view');

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    $response->assertOk();
});
