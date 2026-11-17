<?php

declare(strict_types=1);

use App\Data\HomepageCta\HomepageCtaButtonData;

it('stores homepage CTA button data', function (): void {
    $data = new HomepageCtaButtonData(
        label: 'Jelajahi Layanan',
        type: 'url',
        url: '/layanan',
        target: '_blank',
        sortOrder: 1,
        isActive: true,
    );

    expect($data->label)->toBe('Jelajahi Layanan')
        ->and($data->type)->toBe('url')
        ->and($data->url)->toBe('/layanan')
        ->and($data->target)->toBe('_blank')
        ->and($data->sortOrder)->toBe(1)
        ->and($data->isActive)->toBeTrue();
});

it('uses default values for optional button data', function (): void {
    $data = new HomepageCtaButtonData(
        label: 'Beranda',
        type: 'route',
        routeName: 'home',
    );

    expect($data->url)->toBeNull()
        ->and($data->target)->toBe('_self')
        ->and($data->sortOrder)->toBe(0)
        ->and($data->isActive)->toBeTrue();
});

it('stores existing button uuid', function (): void {
    $uuid = '019a1234-5678-7abc-8def-0123456789ab';

    $data = new HomepageCtaButtonData(
        label: 'Jelajahi Layanan',
        type: 'url',
        url: '/layanan',
        uuid: $uuid,
    );

    expect($data->uuid)->toBe($uuid);
});

it('uses null uuid for a new button', function (): void {
    $data = new HomepageCtaButtonData(
        label: 'Baca Berita',
        type: 'url',
        url: '/berita',
    );

    expect($data->uuid)->toBeNull();
});
