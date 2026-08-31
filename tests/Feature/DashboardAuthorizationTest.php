<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::findOrCreate('dashboard.view');
});

test('guest cannot access dashboard', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
});

test('authenticated user without dashboard permission cannot access dashboard', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/dashboard');

    $response->assertForbidden();
});

test('authenticated user with dashboard permission can access dashboard', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('dashboard.view');

    $response = $this
        ->actingAs($user)
        ->get('/dashboard');

    $response->assertOk();
});
