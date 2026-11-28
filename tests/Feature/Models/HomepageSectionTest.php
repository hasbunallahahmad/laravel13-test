<?php

declare(strict_types=1);

use App\Models\HomepageSection;
use LogicException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores a homepage section with the expected attributes', function () {
    $section = HomepageSection::query()->forceCreate([
        'key' => 'hero',
        'is_enabled' => true,
        'sort_order' => 10,
        'config_version' => 1,
        'settings' => [
            'title' => 'Merawat Arsip, Mengembangkan Literasi',
        ],
    ]);

    expect($section->exists)->toBeTrue()
        ->and($section->key)->toBe('hero')
        ->and($section->is_enabled)->toBeTrue()
        ->and($section->sort_order)->toBe(10)
        ->and($section->config_version)->toBe(1)
        ->and($section->settings)->toBe([
            'title' => 'Merawat Arsip, Mengembangkan Literasi',
        ]);
});

it('casts attributes to their expected types', function () {
    $section = HomepageSection::query()->forceCreate([
        'key' => 'news',
        'is_enabled' => 1,
        'sort_order' => '30',
        'config_version' => '2',
        'settings' => ['limit' => 6],
    ]);

    expect($section->is_enabled)->toBeBool()
        ->and($section->sort_order)->toBeInt()
        ->and($section->config_version)->toBeInt()
        ->and($section->settings)->toBeArray();
});


it('uses database defaults for optional configuration attributes', function () {
    $section = HomepageSection::query()->forceCreate([
        'key' => 'profile',
    ]);

    $section->refresh();

    expect($section->is_enabled)->toBeTrue()
        ->and($section->sort_order)->toBe(0)
        ->and($section->config_version)->toBe(1)
        ->and($section->settings)->toBeNull();
});


it('does not allow key to be changed through mass assignment', function () {
    $section = HomepageSection::query()->forceCreate([
        'key' => 'hero',
    ]);

    $section->update([
        'key' => 'unknown-section',
    ]);

    expect($section->fresh()->key)->toBe('hero');
});

it('does not allow arbitrary keys to be mass assigned on creation', function () {
    $section = new HomepageSection([
        'key' => 'unknown-section',
        'is_enabled' => true,
    ]);

    expect($section->getAttribute('key'))->toBeNull();
});

it('enforces unique section keys at database level', function () {
    HomepageSection::query()->forceCreate([
        'key' => 'hero',
    ]);

    expect(fn() => HomepageSection::query()->forceCreate([
        'key' => 'hero',
    ]))->toThrow(QueryException::class);
});

it('returns only enabled sections through enabled scope', function () {
    HomepageSection::query()->forceCreate([
        'key' => 'hero',
        'is_enabled' => true,
    ]);

    HomepageSection::query()->forceCreate([
        'key' => 'news',
        'is_enabled' => false,
    ]);

    expect(HomepageSection::enabled()->pluck('key')->all())
        ->toBe(['hero']);
});

it('returns only disabled sections through disabled scope', function () {
    HomepageSection::query()->forceCreate([
        'key' => 'hero',
        'is_enabled' => true,
    ]);

    HomepageSection::query()->forceCreate([
        'key' => 'news',
        'is_enabled' => false,
    ]);

    expect(HomepageSection::disabled()->pluck('key')->all())
        ->toBe(['news']);
});

it('orders sections by sort order and then by id', function () {
    $first = HomepageSection::query()->forceCreate([
        'key' => 'hero',
        'sort_order' => 10,
    ]);

    $second = HomepageSection::query()->forceCreate([
        'key' => 'news',
        'sort_order' => 10,
    ]);

    $third = HomepageSection::query()->forceCreate([
        'key' => 'agenda',
        'sort_order' => 20,
    ]);

    expect(HomepageSection::ordered()->pluck('id')->all())
        ->toBe([
            $first->id,
            $second->id,
            $third->id,
        ]);
});

it('persists nested settings as JSON and restores them as arrays', function () {
    $settings = [
        'title' => 'Informasi Publik',
        'display' => [
            'limit' => 5,
            'show_date' => true,
        ],
        'filters' => ['news', 'announcement'],
    ];

    $section = HomepageSection::query()->forceCreate([
        'key' => 'news',
        'settings' => $settings,
    ]);

    expect($section->fresh()->settings)->toBe($settings);
});

it('allows settings to be null', function () {
    $section = HomepageSection::query()->forceCreate([
        'key' => 'hero',
        'settings' => null,
    ]);

    expect($section->fresh()->settings)->toBeNull();
});

it('updates operational attributes without changing its identity', function () {
    $section = HomepageSection::query()->forceCreate([
        'key' => 'services',
        'is_enabled' => true,
        'sort_order' => 20,
    ]);

    $section->update([
        'is_enabled' => false,
        'sort_order' => 25,
        'config_version' => 2,
        'settings' => ['layout' => 'editorial'],
    ]);

    $section->refresh();

    expect($section->key)->toBe('services')
        ->and($section->is_enabled)->toBeFalse()
        ->and($section->sort_order)->toBe(25)
        ->and($section->config_version)->toBe(2)
        ->and($section->settings)->toBe(['layout' => 'editorial']);
});



it('does not allow the section key to be changed directly after creation', function () {
    $section = HomepageSection::query()->forceCreate([
        'key' => 'hero',
    ]);

    $section->key = 'unknown-section';

    expect(fn() => $section->save())
        ->toThrow(LogicException::class);

    expect($section->fresh()->key)->toBe('hero');
});
