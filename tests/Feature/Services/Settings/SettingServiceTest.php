<?php

use App\Models\Setting;
use App\Data\Settings\SettingData;
use App\Services\Settings\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(SettingService::class);
});

/*
|--------------------------------------------------------------------------
| GET
|--------------------------------------------------------------------------
*/

test('it can get an existing setting value', function () {
    Setting::factory()->create([
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Dynamic CMS',
        'type' => 'string',
    ]);

    expect(
        $this->service->get('general.site_name')
    )->toBe('Dynamic CMS');
});

test('it returns default value when setting does not exist', function () {
    expect(
        $this->service->get(
            'general.unknown_setting',
            'Default Value',
        )
    )->toBe('Default Value');
});

test('it returns null when setting does not exist and no default is provided', function () {
    expect(
        $this->service->get('general.unknown_setting')
    )->toBeNull();
});

test('it returns correctly casted setting values', function () {
    Setting::factory()->create([
        'group' => 'general',
        'key' => 'maintenance_mode',
        'value' => true,
        'type' => 'boolean',
    ]);

    expect(
        $this->service->get('general.maintenance_mode')
    )->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| SET
|--------------------------------------------------------------------------
*/

test('it can create a new setting', function () {
    $setting = $this->service->set(
        'general.site_name',
        'Dynamic CMS',
    );

    expect($setting)
        ->toBeInstanceOf(Setting::class);

    $this->assertDatabaseHas('settings', [
        'group' => 'general',
        'key' => 'site_name',
    ]);
});

test('it can update an existing setting', function () {
    $setting = Setting::factory()->create([
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Old Name',
        'type' => 'string',
    ]);

    $updated = $this->service->set(
        'general.site_name',
        'New Name',
    );

    expect($updated->id)->toBe($setting->id);

    expect(
        Setting::query()
            ->where('group', 'general')
            ->where('key', 'site_name')
            ->count()
    )->toBe(1);

    expect(
        $this->service->get('general.site_name')
    )->toBe('New Name');
});

test('it preserves the specified setting type', function () {
    $this->service->set(
        'general.items_per_page',
        20,
        'integer',
    );

    $setting = Setting::query()
        ->where('group', 'general')
        ->where('key', 'items_per_page')
        ->firstOrFail();

    expect($setting->type)->toBe('integer');

    expect($this->service->get(
        'general.items_per_page'
    ))->toBe(20);
});

test('it can store boolean settings', function () {
    $this->service->set(
        'general.maintenance_mode',
        true,
        'boolean',
    );

    expect(
        $this->service->get('general.maintenance_mode')
    )->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| GROUP
|--------------------------------------------------------------------------
*/

test('it can get all settings from a group', function () {
    Setting::factory()->create([
        'group' => 'general',
        'key' => 'site_name',
    ]);

    Setting::factory()->create([
        'group' => 'general',
        'key' => 'site_email',
    ]);

    Setting::factory()->create([
        'group' => 'other',
        'key' => 'other_setting',
    ]);

    $settings = $this->service->group('general');

    expect($settings)
        ->toHaveCount(2);

    expect(
        $settings->pluck('key')->all()
    )->toContain(
        'site_name',
        'site_email',
    );
});

test('it returns an empty collection when group does not exist', function () {
    $settings = $this->service->group('unknown');

    expect($settings)
        ->toBeEmpty();
});

/*
|--------------------------------------------------------------------------
| FORGET
|--------------------------------------------------------------------------
*/

test('it can delete an existing setting', function () {
    $setting = Setting::factory()->create([
        'group' => 'general',
        'key' => 'site_name',
    ]);

    expect(
        $this->service->forget('general.site_name')
    )->toBeTrue();

    expect(
        Setting::query()->find($setting->id)
    )->toBeNull();
});

test('it returns false when deleting a setting that does not exist', function () {
    expect(
        $this->service->forget('general.unknown_setting')
    )->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| SECURITY / VALIDATION
|--------------------------------------------------------------------------
*/

test('it rejects setting keys without group and key format', function () {
    $this->service->get('invalid_key');
})->throws(
    InvalidArgumentException::class,
    'The setting key must use group.key format.',
);

test('it rejects empty group name', function () {
    $this->service->get('.site_name');
})->throws(InvalidArgumentException::class);

test('it rejects empty setting key', function () {
    $this->service->get('general.');
})->throws(InvalidArgumentException::class);

test('it rejects multiple separators in setting key', function () {
    $this->service->get('general.site.name');
})->throws(InvalidArgumentException::class);

/*
|--------------------------------------------------------------------------
| DTO / SETTING DATA
|--------------------------------------------------------------------------
*/
test('it can create a new setting using setting data', function () {
    $data = new SettingData(
        group: 'general',
        key: 'site_name',
        value: 'Website Arpus',
        type: 'string',
        isPublic: true,
    );

    $setting = $this->service->setData($data);

    expect($setting)
        ->toBeInstanceOf(Setting::class)
        ->and($setting->group)->toBe('general')
        ->and($setting->key)->toBe('site_name')
        ->and($setting->value)->toBe('Website Arpus')
        ->and($setting->type)->toBe('string')
        ->and($setting->is_public)->toBeTrue();

    $this->assertDatabaseHas('settings', [
        'group' => 'general',
        'key' => 'site_name',
        'type' => 'string',
        'is_public' => true,
    ]);
});

test('it can update an existing setting using setting data', function () {
    $existing = Setting::factory()->create([
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Website Lama',
        'type' => 'string',
        'is_public' => false,
    ]);

    $data = new SettingData(
        group: 'general',
        key: 'site_name',
        value: 'Website Arpus',
        type: 'string',
        isPublic: true,
    );

    $updated = $this->service->setData($data);

    expect($updated)
        ->toBeInstanceOf(Setting::class)
        ->and($updated->id)->toBe($existing->id)
        ->and($updated->value)->toBe('Website Arpus')
        ->and($updated->type)->toBe('string')
        ->and($updated->is_public)->toBeTrue();

    expect(
        Setting::query()
            ->where('group', 'general')
            ->where('key', 'site_name')
            ->count()
    )->toBe(1);

    expect(
        $this->service->get('general.site_name')
    )->toBe('Website Arpus');
});
