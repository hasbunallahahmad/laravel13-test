<?php

use App\Models\Setting;
use App\Services\Settings\SettingsManager;
use Database\Seeders\DefaultSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->settings = app(SettingsManager::class);
});

test('default settings seeder creates all required settings', function () {
    $this->seed(DefaultSettingsSeeder::class);

    expect(Setting::query()->count())->toBe(10);

    expect(
        Setting::query()
            ->where('group', 'general')
            ->where('key', 'site_name')
            ->exists()
    )->toBeTrue();

    expect(
        Setting::query()
            ->where('group', 'system')
            ->where('key', 'maintenance_mode')
            ->exists()
    )->toBeTrue();

    expect(
        Setting::query()
            ->where('group', 'social')
            ->where('key', 'instagram')
            ->exists()
    )->toBeTrue();
});

test('default website settings contain correct values', function () {
    $this->seed(DefaultSettingsSeeder::class);

    expect(
        $this->settings->get('general.site_name')
    )->toBe('Dinas Arsip dan Perpustakaan Kota Semarang');

    expect(
        $this->settings->get('general.site_description')
    )->toBe('Website resmi Dinas Arsip dan Perpustakaan Kota Semarang');
});

test('maintenance mode is stored and retrieved as boolean false', function () {
    $this->seed(DefaultSettingsSeeder::class);

    $setting = Setting::query()
        ->where('group', 'system')
        ->where('key', 'maintenance_mode')
        ->firstOrFail();

    expect($setting->type)->toBe('boolean');

    expect(
        $this->settings->get('system.maintenance_mode')
    )->toBeFalse();
});

test('nullable default settings remain null', function () {
    $this->seed(DefaultSettingsSeeder::class);

    expect(
        $this->settings->get('general.site_email')
    )->toBeNull();

    expect(
        $this->settings->get('appearance.logo')
    )->toBeNull();

    expect(
        $this->settings->get('social.instagram')
    )->toBeNull();
});

test('social media settings are public', function () {
    $this->seed(DefaultSettingsSeeder::class);

    $socialSettings = Setting::query()
        ->where('group', 'social')
        ->get();

    expect($socialSettings)->toHaveCount(3);

    expect(
        $socialSettings->every(
            fn(Setting $setting): bool => $setting->is_public === true
        )
    )->toBeTrue();
});

test('default settings seeder can run multiple times without duplicates', function () {
    $this->seed(DefaultSettingsSeeder::class);

    expect(Setting::query()->count())->toBe(10);

    $this->seed(DefaultSettingsSeeder::class);

    expect(Setting::query()->count())->toBe(10);

    expect(
        Setting::query()
            ->where('group', 'general')
            ->where('key', 'site_name')
            ->count()
    )->toBe(1);
});
