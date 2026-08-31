<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Authorization
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    Permission::findOrCreate('settings.view');
    Permission::findOrCreate('settings.update');
});

test('guest cannot access the settings index', function () {
    $response = $this->get('/admin/settings');

    $response->assertRedirect('/login');
});

test('authenticated user without settings view permission cannot access settings', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/admin/settings');

    $response->assertForbidden();
});

test('user with settings view permission can access settings index', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('settings.view');

    $response = $this
        ->actingAs($user)
        ->get('/admin/settings');

    $response->assertOk();
});

test('guest cannot update a setting', function () {
    $response = $this->put('/admin/settings/general/site_name', [
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Website Arpus',
        'type' => 'string',
        'is_public' => true,
    ]);

    $response->assertRedirect('/login');
});

test('authenticated user without settings update permission cannot update a setting', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->put('/admin/settings/general/site_name', [
            'group' => 'general',
            'key' => 'site_name',
            'value' => 'Website Arpus',
            'type' => 'string',
            'is_public' => true,
        ]);

    $response->assertForbidden();
});

test('user with settings update permission can update a setting', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('settings.update');

    $response = $this
        ->actingAs($user)
        ->put('/admin/settings/general/site_name', [
            'group' => 'general',
            'key' => 'site_name',
            'value' => 'Website Arpus Kota Semarang',
            'type' => 'string',
            'is_public' => true,
        ]);

    $response->assertOk();

    $this->assertDatabaseHas('settings', [
        'group' => 'general',
        'key' => 'site_name',
    ]);
});

test('user with settings update permission updates an existing setting without creating a duplicate', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('settings.update');

    $existing = Setting::factory()->create([
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Old Website Name',
        'type' => 'string',
        'is_public' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->put('/admin/settings/general/site_name', [
            'group' => 'general',
            'key' => 'site_name',
            'value' => 'New Website Name',
            'type' => 'string',
            'is_public' => true,
        ]);

    $response->assertOk();

    $this->assertDatabaseCount('settings', 1);

    $this->assertDatabaseHas('settings', [
        'id' => $existing->id,
        'group' => 'general',
        'key' => 'site_name',
    ]);

    expect(
        $existing->fresh()->value
    )->toBe('New Website Name');
});

test('user with settings update permission cannot submit an unsupported setting type', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('settings.update');

    $response = $this
        ->actingAs($user)
        ->put('/admin/settings/general/site_name', [
            'group' => 'general',
            'key' => 'site_name',
            'value' => 'Dynamic CMS',
            'type' => 'unsupported',
            'is_public' => true,
        ]);

    $response->assertSessionHasErrors('type');

    $this->assertDatabaseMissing('settings', [
        'group' => 'general',
        'key' => 'site_name',
    ]);
});

test('user with settings update permission cannot submit an invalid integer value', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('settings.update');

    $response = $this
        ->actingAs($user)
        ->put('/admin/settings/general/items_per_page', [
            'group' => 'general',
            'key' => 'items_per_page',
            'value' => 'not-a-number',
            'type' => 'integer',
            'is_public' => true,
        ]);

    $response->assertSessionHasErrors('value');

    $this->assertDatabaseMissing('settings', [
        'group' => 'general',
        'key' => 'items_per_page',
    ]);
});

test('user with settings update permission cannot submit an invalid boolean value', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('settings.update');

    $response = $this
        ->actingAs($user)
        ->put('/admin/settings/general/maintenance_mode', [
            'group' => 'general',
            'key' => 'maintenance_mode',
            'value' => 'invalid-boolean',
            'type' => 'boolean',
            'is_public' => false,
        ]);

    $response->assertSessionHasErrors('value');

    $this->assertDatabaseMissing('settings', [
        'group' => 'general',
        'key' => 'maintenance_mode',
    ]);
});

test('user with settings update permission cannot submit invalid json', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('settings.update');

    $response = $this
        ->actingAs($user)
        ->put('/admin/settings/general/theme_config', [
            'group' => 'general',
            'key' => 'theme_config',
            'value' => '{"theme":"dark"',
            'type' => 'json',
            'is_public' => false,
        ]);

    $response->assertSessionHasErrors('value');

    $this->assertDatabaseMissing('settings', [
        'group' => 'general',
        'key' => 'theme_config',
    ]);
});

test('user with settings view permission can see settings in the index view', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('settings.view');

    $setting = Setting::factory()->create([
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Dynamic CMS',
        'type' => 'string',
        'is_public' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->get('/admin/settings');

    $response->assertOk();

    $response->assertViewIs('admin.settings.index');

    $response->assertViewHas('settings', function ($settings) use ($setting) {
        return $settings->contains(
            fn($item) => $item->id === $setting->id
        );
    });
});
