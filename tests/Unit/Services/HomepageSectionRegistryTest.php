
<?php

use App\Services\Homepage\HomepageSectionRegistry;

it('registers the six supported homepage sections', function () {
    $registry = new HomepageSectionRegistry();

    expect($registry->all())
        ->toHaveKeys([
            'hero',
            'services',
            'news',
            'agenda',
            'publications',
            'profile',
        ])
        ->toHaveCount(6);
});

it('returns a section definition by its key', function () {
    $registry = new HomepageSectionRegistry();

    expect($registry->get('hero'))
        ->toBeArray()
        ->toHaveKeys([
            'key',
            'default_enabled',
            'default_sort_order',
            'config_version',
            'default_settings',
        ]);
});

it('returns null for an unknown section key', function () {
    $registry = new HomepageSectionRegistry();

    expect($registry->get('unknown-section'))->toBeNull();
});

it('does not register arbitrary section keys', function () {
    $registry = new HomepageSectionRegistry();

    expect($registry->has('hero'))->toBeTrue()
        ->and($registry->has('unknown-section'))->toBeFalse();
});


it('keeps every registered key consistent with its definition', function () {
    $registry = new HomepageSectionRegistry();

    foreach ($registry->all() as $key => $definition) {
        expect($definition['key'])->toBe($key);
    }
});

it('provides valid default configuration metadata for every section', function () {
    $registry = new HomepageSectionRegistry();

    foreach ($registry->all() as $definition) {
        expect($definition['default_enabled'])->toBeBool()
            ->and($definition['default_sort_order'])->toBeInt()
            ->and($definition['default_sort_order'])->toBeGreaterThan(0)
            ->and($definition['config_version'])->toBeInt()
            ->and($definition['config_version'])->toBeGreaterThan(0)
            ->and($definition['default_settings'])->toBeArray();
    }
});

it('assigns a unique sort order to every registered section', function () {
    $registry = new HomepageSectionRegistry();

    $sortOrders = array_column($registry->all(), 'default_sort_order');

    expect(array_unique($sortOrders))->toHaveCount(count($sortOrders));
});

it('keeps section definitions isolated from external array mutations', function () {
    $registry = new HomepageSectionRegistry();

    $sections = $registry->all();
    $sections['hero']['key'] = 'tampered';
    $sections['hero']['default_settings']['unexpected'] = true;

    $hero = $registry->get('hero');

    expect($hero['key'])->toBe('hero')
        ->and($hero['default_settings'])->toBe([]);
});

it('does not resolve section keys with different casing or whitespace', function () {
    $registry = new HomepageSectionRegistry();

    expect($registry->get('Hero'))->toBeNull()
        ->and($registry->get(' hero'))->toBeNull()
        ->and($registry->get('hero '))->toBeNull()
        ->and($registry->has('Hero'))->toBeFalse()
        ->and($registry->has(' hero'))->toBeFalse();
});

it('preserves the agreed default section order', function () {
    $registry = new HomepageSectionRegistry();

    expect(array_keys($registry->all()))->toBe([
        'hero',
        'services',
        'news',
        'agenda',
        'publications',
        'profile',
    ]);

    expect(array_values(array_column($registry->all(), 'default_sort_order')))
        ->toBe([10, 20, 30, 40, 50, 60]);
});
