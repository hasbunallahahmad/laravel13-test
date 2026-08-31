<?php

use App\Services\Settings\SettingsManager;
use App\Models\Setting;

test('settings helper resolves the settings manager', function () {
    $settings = settings();

    expect($settings)
        ->toBeInstanceOf(SettingsManager::class);
});

test('settings helper can retrieve a setting value', function () {
    Setting::factory()->create([
        'group' => 'general',
        'key' => 'site_name',
        'value' => 'Website Arpus',
        'type' => 'string',
    ]);

    expect(
        settings('general.site_name')
    )->toBe('Website Arpus');
});

test('settings helper returns default value when setting does not exist', function () {
    expect(
        settings('general.unknown_setting', 'Default Value')
    )->toBe('Default Value');
});

test('settings helper returns null when setting does not exist without default', function () {
    expect(
        settings('general.unknown_setting')
    )->toBeNull();
});
