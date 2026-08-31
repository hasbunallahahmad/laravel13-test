<?php

use App\Facades\Settings;
use App\Models\Setting;
use App\Services\Settings\SettingsManager;

test('settings facade resolves the settings manager', function () {
    $settings = Settings::getFacadeRoot();

    expect($settings)
        ->toBeInstanceOf(SettingsManager::class);
});

test('settings facade can retrieve a setting value', function () {
    Setting::factory()->create([
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Website Arpus',
        'type' => 'string',
    ]);

    expect(
        Settings::get('general.site_name')
    )->toBe('Website Arpus');
});

test('settings facade returns default value', function () {
    expect(
        Settings::get(
            'general.unknown_setting',
            'Default Value',
        )
    )->toBe('Default Value');
});

test('settings facade can create a setting', function () {
    Settings::set(
        'general.site_name',
        'Website Arpus',
    );

    $setting = Setting::query()
        ->where('group', 'general')
        ->where('key', 'site_name')
        ->first();

    expect($setting)->not->toBeNull();

    expect($setting->value)
        ->toBe('Website Arpus');
});

test('settings facade can delete a setting', function () {
    Setting::factory()->create([
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Website Arpus',
        'type' => 'string',
    ]);

    expect(
        Settings::delete('general.site_name')
    )->toBeTrue();

    expect(
        Setting::query()
            ->where('group', 'general')
            ->where('key', 'site_name')
            ->exists()
    )->toBeFalse();
});

test('settings facade can retrieve a settings group', function () {
    Setting::factory()->create([
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Website Arpus',
        'type' => 'string',
    ]);

    Setting::factory()->create([
        'group' => 'general',
        'key' => 'site_email',
        'value' => 'admin@example.com',
        'type' => 'string',
    ]);

    $settings = Settings::group('general');

    expect($settings)->toHaveCount(2);
});
