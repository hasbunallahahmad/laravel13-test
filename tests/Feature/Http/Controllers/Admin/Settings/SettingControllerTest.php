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

test('setting update uses group and key from route parameters', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('settings.update');

    $response = $this
        ->actingAs($user)
        ->put('/admin/settings/general/site_name', [
            'group' => 'system',
            'key' => 'maintenance_mode',
            'value' => 'Website Aman',
            'type' => 'string',
            'is_public' => true,
        ]);

    $response->assertOk();

    $this->assertDatabaseHas('settings', [
        'group' => 'general',
        'key' => 'site_name',
    ]);

    $this->assertDatabaseMissing('settings', [
        'group' => 'system',
        'key' => 'maintenance_mode',
    ]);
});

test('settings index displays settings sections', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('settings.view');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.settings.index'));

    $response->assertOk();

    $response->assertSee('Settings');
    $response->assertSee('General');
    $response->assertSee('Appearance');
    $response->assertSee('Social Media');
    $response->assertSee('System');
});

test('settings index displays stored setting values', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('settings.view');

    Setting::factory()->create([
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Website Arpus Kota Semarang',
        'type' => 'string',
        'is_public' => false,
    ]);

    Setting::factory()->create([
        'group' => 'system',
        'key' => 'maintenance_mode',
        'value' => true,
        'type' => 'boolean',
        'is_public' => false,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.settings.index'));

    $response->assertOk();

    $response->assertSee('Site Name');
    $response->assertSee('Website Arpus Kota Semarang');

    $response->assertSee('Maintenance Mode');
    $response->assertSee('Enabled');
});

test('user without settings update permission cannot see edit controls', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('settings.view');

    Setting::factory()->create([
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Dynamic CMS',
        'type' => 'string',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.settings.index'));

    $response->assertOk();

    $response->assertDontSee('Edit');
});

test('user with settings update permission can see edit controls', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'settings.view',
        'settings.update',
    ]);

    Setting::factory()->create([
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Dynamic CMS',
        'type' => 'string',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.settings.index'));

    $response->assertOk();

    $response->assertSee('Edit');
});

/*
|--------------------------------------------------------------------------
| Edit Setting
|--------------------------------------------------------------------------
*/

test('guest cannot access the edit setting page', function () {
    $response = $this->get(
        '/admin/settings/general/site_name/edit'
    );

    $response->assertRedirect('/login');
});

test('authenticated user without settings update permission cannot access the edit setting page', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('settings.view');

    Setting::factory()->create([
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Dynamic CMS',
        'type' => 'string',
    ]);

    $response = $this
        ->actingAs($user)
        ->get('/admin/settings/general/site_name/edit');

    $response->assertForbidden();
});

test('user with settings update permission can access the edit setting page', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'settings.view',
        'settings.update',
    ]);

    $setting = Setting::factory()->create([
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Dynamic CMS',
        'type' => 'string',
        'is_public' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->get('/admin/settings/general/site_name/edit');

    $response->assertOk();

    $response->assertViewIs('admin.settings.edit');

    $response->assertViewHas('setting', function ($viewSetting) use ($setting) {
        return $viewSetting->id === $setting->id;
    });
});

test('edit setting page returns not found for a missing setting', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'settings.view',
        'settings.update',
    ]);

    $response = $this
        ->actingAs($user)
        ->get('/admin/settings/general/does_not_exist/edit');

    $response->assertNotFound();
});

test('edit setting page uses group and key from route parameters', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'settings.view',
        'settings.update',
    ]);

    Setting::factory()->create([
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'General Website',
        'type' => 'string',
    ]);

    Setting::factory()->create([
        'group' => 'system',
        'key' => 'site_name',
        'value' => 'System Website',
        'type' => 'string',
    ]);

    $response = $this
        ->actingAs($user)
        ->get('/admin/settings/system/site_name/edit');

    $response->assertOk();

    $response->assertViewHas('setting', function ($setting) {
        return $setting->group === 'system'
            && $setting->key === 'site_name'
            && $setting->value === 'System Website';
    });
});

/*
|--------------------------------------------------------------------------
| Edit Setting Form
|--------------------------------------------------------------------------
*/

test('edit setting page displays a string setting form', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'settings.view',
        'settings.update',
    ]);

    Setting::factory()->create([
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Website Arpus Kota Semarang',
        'type' => 'string',
        'is_public' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->get('/admin/settings/general/site_name/edit');

    $response->assertOk();

    $response->assertSee('Edit Setting');

    $response->assertSee('Site Name');

    $response->assertSee('Website Arpus Kota Semarang');

    $response->assertSee('Save Changes');
});

/*
|--------------------------------------------------------------------------
| Update Existing Setting From Edit Form
|--------------------------------------------------------------------------
*/

test('user with settings update permission can update an existing setting using only the value', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('settings.update');

    Setting::factory()->create([
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Website Lama',
        'type' => 'string',
        'is_public' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->put('/admin/settings/general/site_name', [
            'value' => 'Website Arpus Kota Semarang',
        ]);

    $response->assertOk();

    $this->assertDatabaseHas('settings', [
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Website Arpus Kota Semarang',
        'type' => 'string',
        'is_public' => true,
    ]);
});
test('existing setting metadata cannot be changed through the update request', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('settings.update');

    $setting = Setting::factory()->create([
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Website Lama',
        'type' => 'string',
        'is_public' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->put('/admin/settings/general/site_name', [
            'value' => 'Website Arpus Kota Semarang',

            // Attempt to manipulate existing metadata.
            'type' => 'json',
            'is_public' => false,
        ]);

    $response->assertOk();

    $setting->refresh();

    expect($setting->value)
        ->toBe('Website Arpus Kota Semarang');

    expect($setting->type)
        ->toBe('string');

    expect($setting->is_public)
        ->toBeTrue();
});
test('edit setting page displays a number input for an integer setting', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'settings.view',
        'settings.update',
    ]);

    Setting::factory()->create([
        'group' => 'general',
        'key' => 'items_per_page',
        'value' => 10,
        'type' => 'integer',
        'is_public' => false,
    ]);

    $response = $this
        ->actingAs($user)
        ->get('/admin/settings/general/items_per_page/edit');

    $response->assertOk();

    $response->assertSee('Items Per Page');

    $response->assertSee('type="number"', false);

    $response->assertSee('value="10"', false);

    $response->assertSee('Save Changes');
});

test('edit setting page displays a boolean control for a boolean setting', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'settings.view',
        'settings.update',
    ]);

    Setting::factory()->create([
        'group' => 'system',
        'key' => 'maintenance_mode',
        'value' => true,
        'type' => 'boolean',
        'is_public' => false,
    ]);

    $response = $this
        ->actingAs($user)
        ->get('/admin/settings/system/maintenance_mode/edit');

    $response->assertOk();

    $response->assertSee('Maintenance Mode');

    $response->assertSee('type="checkbox"', false);

    $response->assertSee('Save Changes');
});

test('existing boolean setting can be updated from true to false', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('settings.update');

    $setting = Setting::factory()->create([
        'group' => 'system',
        'key' => 'maintenance_mode',
        'value' => true,
        'type' => 'boolean',
        'is_public' => false,
    ]);

    $response = $this
        ->actingAs($user)
        ->put('/admin/settings/system/maintenance_mode', [
            'value' => '0',
        ]);

    $response->assertOk();

    $setting->refresh();

    expect($setting->value)->toBeFalse();
});

test('existing boolean setting can be updated from false to true', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('settings.update');

    $setting = Setting::factory()->create([
        'group' => 'system',
        'key' => 'maintenance_mode',
        'value' => false,
        'type' => 'boolean',
        'is_public' => false,
    ]);

    $response = $this
        ->actingAs($user)
        ->put('/admin/settings/system/maintenance_mode', [
            'value' => '1',
        ]);

    $response->assertOk();

    $setting->refresh();

    expect($setting->value)->toBeTrue();
});

test('edit setting page displays a textarea for a json setting', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'settings.view',
        'settings.update',
    ]);

    Setting::factory()
        ->json([
            'title' => 'Dinas Arsip dan Perpustakaan',
            'city' => 'Semarang',
        ])
        ->create([
            'group' => 'general',
            'key' => 'organization_profile',
            'is_public' => true,
        ]);

    $response = $this
        ->actingAs($user)
        ->get('/admin/settings/general/organization_profile/edit');

    $response->assertOk();

    $response->assertSee('Organization Profile');

    $response->assertSee('<textarea', false);

    $response->assertSee('city');

    $response->assertSee('Save Changes');
});

test('existing json setting can be updated with valid json', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('settings.update');

    $setting = Setting::factory()
        ->json([
            'title' => 'Old Title',
        ])
        ->create([
            'group' => 'general',
            'key' => 'organization_profile',
            'is_public' => true,
        ]);

    $newValue = json_encode([
        'title' => 'Dinas Arsip dan Perpustakaan Kota Semarang',
        'city' => 'Semarang',
    ]);

    $response = $this
        ->actingAs($user)
        ->put('/admin/settings/general/organization_profile', [
            'value' => $newValue,
        ]);

    $response->assertOk();

    $setting->refresh();

    expect($setting->value)->toBe([
        'title' => 'Dinas Arsip dan Perpustakaan Kota Semarang',
        'city' => 'Semarang',
    ]);
});

test('existing json setting cannot be updated with invalid json', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('settings.update');

    $setting = Setting::factory()
        ->json([
            'title' => 'Original Title',
        ])
        ->create([
            'group' => 'general',
            'key' => 'organization_profile',
        ]);

    $invalidJson = '{"title": "Invalid JSON"';

    $response = $this
        ->actingAs($user)
        ->put('/admin/settings/general/organization_profile', [
            'value' => $invalidJson,
        ]);

    $response->assertRedirect();

    $response->assertSessionHasErrors('value');

    $setting->refresh();

    expect($setting->value)->toBe([
        'title' => 'Original Title',
    ]);
});
