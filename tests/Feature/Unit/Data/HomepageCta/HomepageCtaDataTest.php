<?php

declare(strict_types=1);

use App\Data\HomepageCta\HomepageCtaButtonData;
use App\Data\HomepageCta\HomepageCtaData;

it('stores homepage CTA section data with buttons', function (): void {
    $button = new HomepageCtaButtonData(
        label: 'Lihat Layanan',
        type: 'url',
        url: '/layanan',
    );

    $data = new HomepageCtaData(
        title: 'Jelajahi Layanan',
        description: 'Temukan layanan kami.',
        sortOrder: 1,
        isActive: true,
        buttons: [$button],
    );

    expect($data->title)->toBe('Jelajahi Layanan')
        ->and($data->description)->toBe('Temukan layanan kami.')
        ->and($data->sortOrder)->toBe(1)
        ->and($data->isActive)->toBeTrue()
        ->and($data->buttons)->toHaveCount(1)
        ->and($data->buttons[0])->toBeInstanceOf(HomepageCtaButtonData::class);
});

it('uses default values for optional section data', function (): void {
    $data = new HomepageCtaData(
        title: 'Jelajahi Layanan',
    );

    expect($data->description)->toBeNull()
        ->and($data->sortOrder)->toBe(0)
        ->and($data->isActive)->toBeTrue()
        ->and($data->buttons)->toBe([]);
});
