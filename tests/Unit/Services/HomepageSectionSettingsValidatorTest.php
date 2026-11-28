<?php

declare(strict_types=1);

use App\Services\Homepage\HomepageSectionRegistry;
use App\Services\Homepage\HomepageSectionSettingsValidator;

it('accepts empty settings for registered sections', function () {
    $validator = new HomepageSectionSettingsValidator(
        app(HomepageSectionRegistry::class),
    );

    expect($validator->validate('hero', []))->toBe([]);
});

it('rejects unknown section keys', function () {
    $validator = new HomepageSectionSettingsValidator(
        app(HomepageSectionRegistry::class),
    );

    expect(fn() => $validator->validate('unknown', []))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects non-array settings', function () {
    $validator = new HomepageSectionSettingsValidator(
        app(HomepageSectionRegistry::class),
    );

    expect(fn() => $validator->validate('hero', 'invalid'))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects arbitrary settings keys not defined in the registry', function () {
    $validator = new HomepageSectionSettingsValidator(
        app(HomepageSectionRegistry::class),
    );

    expect(fn() => $validator->validate('hero', [
        'blade' => 'admin.dashboard',
        'class' => 'App\\Models\\User',
        'script' => 'alert(1)',
    ]))->toThrow(InvalidArgumentException::class);
});

it('does not modify the registry defaults', function () {
    $registry = app(HomepageSectionRegistry::class);

    $validator = new HomepageSectionSettingsValidator($registry);

    $validator->validate('hero', []);

    expect($registry->get('hero')['default_settings'])->toBe([]);
});
