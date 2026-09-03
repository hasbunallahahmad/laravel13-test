<?php

declare(strict_types=1);

use App\Models\Content;
use App\Models\User;
use App\Policies\ContentPolicy;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Permission::findOrCreate('content.view');
    Permission::findOrCreate('content.create');
    Permission::findOrCreate('content.update');
    Permission::findOrCreate('content.delete');
    Permission::findOrCreate('content.publish');
    Permission::findOrCreate('content.restore');
    Permission::findOrCreate('content.force-delete');
});

test('user with content view permission can view content', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('content.view');

    $content = Content::factory()->create();

    expect(
        app(ContentPolicy::class)->view($user, $content)
    )->toBeTrue();
});

test('user without content view permission cannot view content', function () {
    $user = User::factory()->create();

    $content = Content::factory()->create();

    expect(
        app(ContentPolicy::class)->view($user, $content)
    )->toBeFalse();
});

test('user with content create permission can create content', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('content.create');

    expect(
        app(ContentPolicy::class)->create($user)
    )->toBeTrue();
});

test('user without content create permission cannot create content', function () {
    $user = User::factory()->create();

    expect(
        app(ContentPolicy::class)->create($user)
    )->toBeFalse();
});

test('user with content update permission can update content', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('content.update');

    $content = Content::factory()->create();

    expect(
        app(ContentPolicy::class)->update($user, $content)
    )->toBeTrue();
});

test('user with content delete permission can delete content', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('content.delete');

    $content = Content::factory()->create();

    expect(
        app(ContentPolicy::class)->delete($user, $content)
    )->toBeTrue();
});

test('user with content publish permission can publish content', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('content.publish');

    $content = Content::factory()->create();

    expect(
        app(ContentPolicy::class)->publish($user, $content)
    )->toBeTrue();
});

test('user with content view permission can view content list', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('content.view');

    expect(
        app(ContentPolicy::class)->viewAny($user)
    )->toBeTrue();
});

test('user without content view permission cannot view content list', function () {
    $user = User::factory()->create();

    expect(
        app(ContentPolicy::class)->viewAny($user)
    )->toBeFalse();
});

test('user without content update permission cannot update content', function () {
    $user = User::factory()->create();

    $content = Content::factory()->create();

    expect(
        app(ContentPolicy::class)->update($user, $content)
    )->toBeFalse();
});

test('user without content delete permission cannot delete content', function () {
    $user = User::factory()->create();

    $content = Content::factory()->create();

    expect(
        app(ContentPolicy::class)->delete($user, $content)
    )->toBeFalse();
});

test('user with content restore permission can restore content', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('content.restore');

    $content = Content::factory()->create();

    expect(
        app(ContentPolicy::class)->restore($user, $content)
    )->toBeTrue();
});

test('user without content restore permission cannot restore content', function () {
    $user = User::factory()->create();

    $content = Content::factory()->create();

    expect(
        app(ContentPolicy::class)->restore($user, $content)
    )->toBeFalse();
});

test('user with content force delete permission can force delete content', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('content.force-delete');

    $content = Content::factory()->create();

    expect(
        app(ContentPolicy::class)->forceDelete($user, $content)
    )->toBeTrue();
});

test('user without content force delete permission cannot force delete content', function () {
    $user = User::factory()->create();

    $content = Content::factory()->create();

    expect(
        app(ContentPolicy::class)->forceDelete($user, $content)
    )->toBeFalse();
});

test('user without content publish permission cannot publish content', function () {
    $user = User::factory()->create();

    $content = Content::factory()->create();

    expect(
        app(ContentPolicy::class)->publish($user, $content)
    )->toBeFalse();
});
