<?php

declare(strict_types=1);

use App\Models\Content;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Permission::findOrCreate('content.view');
    Permission::findOrCreate('content.create');
    Permission::findOrCreate('content.update');
    Permission::findOrCreate('content.delete');
    Permission::findOrCreate('content.publish');
});

test('guest cannot access admin content', function () {
    $response = $this->get(route('admin.contents.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated user without content view permission cannot access content', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.index'));

    $response->assertForbidden();
});

test('user with content view permission can access content', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.view');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.index'));

    $response->assertOk();
});

test('user without content create permission cannot access create content page', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.view');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.create'));

    $response->assertForbidden();
});

test('user with content create permission can access create content page', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'content.view',
        'content.create',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.create'));

    $response->assertOk();
});

test('user without content update permission cannot access edit content page', function () {
    $user = User::factory()->create();

    $content = Content::factory()->create();

    $user->givePermissionTo('content.view');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.edit', $content));

    $response->assertForbidden();
});

test('user without content delete permission cannot delete content', function () {
    $user = User::factory()->create();

    $content = Content::factory()->create();

    $user->givePermissionTo('content.view');

    $response = $this
        ->actingAs($user)
        ->delete(route('admin.contents.destroy', $content));

    $response->assertForbidden();
});
