<?php

declare(strict_types=1);

use App\Models\Content;
use App\Models\Media;
use App\Models\Menu;
use App\Models\User;

test('user with dashboard permission can access admin dashboard', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('dashboard.view');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.dashboard'));

    $response
        ->assertOk()
        ->assertViewIs('admin.dashboard.index');
});

test('user without dashboard permission cannot access admin dashboard', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.dashboard'));

    $response->assertForbidden();
});

test('guest cannot access admin dashboard', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertRedirect(route('login'));
});

test('admin dashboard displays content statistics', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('dashboard.view');

    $initialUserCount = User::query()->count();

    User::factory()->count(3)->create();

    Content::factory()
        ->count(5)
        ->for($user, 'author')
        ->create();

    Media::factory()
        ->count(7)
        ->for($user, 'uploader')
        ->create();

    Menu::factory()->count(4)->create();

    $expectedUserCount = $initialUserCount + 3;

    $response = $this
        ->actingAs($user)
        ->get(route('admin.dashboard'));

    $response
        ->assertOk()
        ->assertViewHas('statistics', function (array $statistics) use ($expectedUserCount): bool {
            return $statistics['users'] === $expectedUserCount
                && $statistics['contents'] === 5
                && $statistics['media'] === 7
                && $statistics['menus'] === 4;
        })
        ->assertSee('data-statistic="users"', false)
        ->assertSee('data-statistic="contents"', false)
        ->assertSee('data-statistic="media"', false)
        ->assertSee('data-statistic="menus"', false);
});
