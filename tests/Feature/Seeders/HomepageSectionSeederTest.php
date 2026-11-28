<?php

declare(strict_types=1);

use App\Models\HomepageSection;
use App\Services\Homepage\HomepageSectionRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates all sections registered in the registry', function () {
    $this->seed(\Database\Seeders\HomepageSectionSeeder::class);

    $registry = app(HomepageSectionRegistry::class);

    expect(HomepageSection::query()->count())
        ->toBe(count($registry->all()));

    foreach ($registry->all() as $key => $definition) {
        $section = HomepageSection::query()
            ->where('key', $key)
            ->first();

        expect($section)->not->toBeNull()
            ->and($section->is_enabled)->toBe($definition['default_enabled'])
            ->and($section->sort_order)->toBe($definition['default_sort_order'])
            ->and($section->config_version)->toBe($definition['config_version'])
            ->and($section->settings)->toBe($definition['default_settings']);
    }
});

it('does not create duplicate sections when seeded repeatedly', function () {
    $this->seed(\Database\Seeders\HomepageSectionSeeder::class);
    $this->seed(\Database\Seeders\HomepageSectionSeeder::class);

    expect(HomepageSection::query()->count())
        ->toBe(count(app(HomepageSectionRegistry::class)->all()));
});

it('preserves administrator changes when seeded repeatedly', function () {
    $this->seed(\Database\Seeders\HomepageSectionSeeder::class);

    $section = HomepageSection::query()
        ->where('key', 'news')
        ->firstOrFail();

    $section->update([
        'is_enabled' => false,
        'sort_order' => 99,
        'config_version' => 2,
        'settings' => [
            'limit' => 3,
            'layout' => 'editorial',
        ],
    ]);

    $this->seed(\Database\Seeders\HomepageSectionSeeder::class);

    $section->refresh();

    expect($section->is_enabled)->toBeFalse()
        ->and($section->sort_order)->toBe(99)
        ->and($section->config_version)->toBe(2)
        ->and($section->settings)->toBe([
            'limit' => 3,
            'layout' => 'editorial',
        ]);
});

it('does not create sections that are not registered', function () {
    $this->seed(\Database\Seeders\HomepageSectionSeeder::class);

    expect(
        HomepageSection::query()
            ->where('key', 'unknown-section')
            ->exists()
    )->toBeFalse();
});
