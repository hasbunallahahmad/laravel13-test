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
});

test('content index displays content data', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'content.view',
        'content.update',
        'content.delete',
    ]);

    $content = Content::factory()->create([
        'title' => 'Test Content',
        'slug' => 'test-content',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.index'));

    $response->assertOk();

    $response->assertSee('Test Content');
    $response->assertSee('test-content');
    $response->assertSee($content->type->value);
    // $response->assertSee('Draft');
    $response->assertSee(ucfirst($content->status->value));
});

test('content index displays create button for authorized user', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'content.view',
        'content.create',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.index'));

    $response->assertOk();

    $response->assertSee(route('admin.contents.create'));
});

test('content index hides create button without create permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.view');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.index'));

    $response->assertOk();

    $response->assertDontSee(route('admin.contents.create'));
});

test('content index shows edit action only with update permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.view');

    $content = Content::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.index'));

    $response->assertOk();

    $response->assertDontSee(
        route('admin.contents.edit', $content)
    );
});

test('content index shows edit action with update permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'content.view',
        'content.update',
    ]);

    $content = Content::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.index'));

    $response->assertOk();

    $response->assertSee(
        route('admin.contents.edit', $content)
    );
});

test('content index shows delete action with delete permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'content.view',
        'content.delete',
    ]);

    $content = Content::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.index'));

    $response->assertOk();

    $response->assertSee(
        route('admin.contents.destroy', $content)
    );
});

test('content index displays empty state when no content exists', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('content.view');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.contents.index'));

    $response->assertOk();

    $response->assertSee('No content found');
});
