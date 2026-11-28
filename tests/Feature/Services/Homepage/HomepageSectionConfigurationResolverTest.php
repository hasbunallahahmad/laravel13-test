<?php

declare(strict_types=1);

use App\Models\HomepageSection;
use App\Services\Homepage\HomepageSectionConfigurationResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns all registered sections in the configured order', function () {
    HomepageSection::query()->forceCreate([
        'key' => 'news',
        'sort_order' => 10,
    ]);

    HomepageSection::query()->forceCreate([
        'key' => 'hero',
        'sort_order' => 20,
    ]);

    $sections = app(HomepageSectionConfigurationResolver::class)->resolve();

    expect(array_column($sections, 'key'))
        ->toBe(['news', 'hero', 'services', 'agenda', 'publications', 'profile']);
});

it('uses registry defaults when a database record does not exist', function () {
    $sections = app(HomepageSectionConfigurationResolver::class)->resolve();

    $hero = collect($sections)->firstWhere('key', 'hero');

    expect($hero)->toMatchArray([
        'key' => 'hero',
        'is_enabled' => true,
        'sort_order' => 10,
        'config_version' => 1,
        'settings' => [],
    ]);
});

it('uses database configuration for registered sections', function () {
    HomepageSection::query()->forceCreate([
        'key' => 'news',
        'is_enabled' => false,
        'sort_order' => 5,
        'config_version' => 2,
        'settings' => [],
    ]);

    $sections = app(HomepageSectionConfigurationResolver::class)->resolve();

    $news = collect($sections)->firstWhere('key', 'news');

    expect($news)->toMatchArray([
        'key' => 'news',
        'is_enabled' => false,
        'sort_order' => 5,
        'config_version' => 2,
        'settings' => [],
    ]);
});

it('ignores database records that are not registered', function () {
    HomepageSection::query()->forceCreate([
        'key' => 'unknown-section',
        'is_enabled' => true,
        'sort_order' => 1,
    ]);

    $sections = app(HomepageSectionConfigurationResolver::class)->resolve();

    expect(array_column($sections, 'key'))
        ->toHaveCount(6)
        ->not->toContain('unknown-section');
});

it('returns disabled sections without removing them', function () {
    HomepageSection::query()->forceCreate([
        'key' => 'hero',
        'is_enabled' => false,
    ]);

    $sections = app(HomepageSectionConfigurationResolver::class)->resolve();

    $hero = collect($sections)->firstWhere('key', 'hero');

    expect($hero)->not->toBeNull()
        ->and($hero['is_enabled'])->toBeFalse();
});

it('uses registry order as a deterministic tie breaker', function () {
    HomepageSection::query()->forceCreate([
        'key' => 'news',
        'sort_order' => 10,
    ]);

    HomepageSection::query()->forceCreate([
        'key' => 'hero',
        'sort_order' => 10,
    ]);

    $sections = app(HomepageSectionConfigurationResolver::class)->resolve();

    $keys = collect($sections)
        ->where('sort_order', 10)
        ->pluck('key')
        ->all();

    expect($keys)->toBe(['hero', 'news']);
});

it('returns settings as an array', function () {
    HomepageSection::query()->forceCreate([
        'key' => 'news',
        'settings' => [],
    ]);

    $sections = app(HomepageSectionConfigurationResolver::class)->resolve();

    $news = collect($sections)->firstWhere('key', 'news');

    expect($news['settings'])->toBeArray()
        ->and($news['settings'])->toBe([]);
});


it('falls back to registry defaults when database settings are invalid', function () {
    HomepageSection::query()->forceCreate([
        'key' => 'news',
        'is_enabled' => true,
        'sort_order' => 15,
        'config_version' => 1,
        'settings' => [
            'blade' => 'admin.dashboard',
            'script' => 'alert(1)',
        ],
    ]);

    $sections = app(
        HomepageSectionConfigurationResolver::class,
    )->resolve();

    $news = collect($sections)->firstWhere('key', 'news');

    expect($news)->toMatchArray([
        'key' => 'news',
        'is_enabled' => true,
        'sort_order' => 15,
        'config_version' => 1,
        'settings' => [],
    ]);
});


it('logs a warning when database settings are invalid', function () {
    HomepageSection::query()->forceCreate([
        'key' => 'news',
        'is_enabled' => true,
        'sort_order' => 15,
        'config_version' => 1,
        'settings' => [
            'script' => 'alert(1)',
        ],
    ]);

    \Illuminate\Support\Facades\Log::shouldReceive('warning')
        ->once()
        ->with(
            'Invalid homepage section settings; registry defaults applied.',
            [
                'section_key' => 'news',
                'config_version' => 1,
            ],
        );

    app(
        \App\Services\Homepage\HomepageSectionConfigurationResolver::class,
    )->resolve();
});
