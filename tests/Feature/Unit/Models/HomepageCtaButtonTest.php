<?php

declare(strict_types=1);

use App\Models\HomepageCta;
use App\Models\HomepageCtaButton;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('generates a UUID when creating a homepage CTA button', function (): void {
    $cta = HomepageCta::query()->create([
        'title' => 'Jelajahi Layanan',
    ]);

    $button = HomepageCtaButton::query()->create([
        'homepage_cta_id' => $cta->id,
        'label' => 'Lihat Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    expect($button->uuid)->not->toBeNull()
        ->and($button->getRouteKeyName())->toBe('uuid');
});

it('belongs to a homepage CTA', function (): void {
    $cta = HomepageCta::query()->create([
        'title' => 'Jelajahi Layanan',
    ]);

    $button = HomepageCtaButton::query()->create([
        'homepage_cta_id' => $cta->id,
        'label' => 'Lihat Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    expect($button->homepageCta->is($cta))->toBeTrue();
});

it('casts sort order and active status correctly', function (): void {
    $cta = HomepageCta::query()->create([
        'title' => 'Jelajahi Layanan',
    ]);

    $button = HomepageCtaButton::query()->create([
        'homepage_cta_id' => $cta->id,
        'label' => 'Lihat Layanan',
        'type' => 'url',
        'url' => '/layanan',
        'sort_order' => 3,
        'is_active' => true,
    ]);

    expect($button->sort_order)->toBeInt()
        ->and($button->is_active)->toBeTrue();
});

it('can be soft deleted', function (): void {
    $cta = HomepageCta::query()->create([
        'title' => 'Jelajahi Layanan',
    ]);

    $button = HomepageCtaButton::query()->create([
        'homepage_cta_id' => $cta->id,
        'label' => 'Lihat Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    $button->delete();

    $this->assertSoftDeleted('homepage_cta_buttons', [
        'id' => $button->id,
    ]);
});

it('provides active inactive and ordered query scopes', function (): void {
    $cta = HomepageCta::query()->create([
        'title' => 'Jelajahi Layanan',
    ]);

    $later = HomepageCtaButton::query()->create([
        'homepage_cta_id' => $cta->id,
        'label' => 'Tombol Kedua',
        'type' => 'url',
        'url' => '/kedua',
        'sort_order' => 2,
    ]);

    $earlier = HomepageCtaButton::query()->create([
        'homepage_cta_id' => $cta->id,
        'label' => 'Tombol Pertama',
        'type' => 'url',
        'url' => '/pertama',
        'sort_order' => 1,
    ]);

    $inactive = HomepageCtaButton::query()->create([
        'homepage_cta_id' => $cta->id,
        'label' => 'Tombol Nonaktif',
        'type' => 'url',
        'url' => '/nonaktif',
        'sort_order' => 3,
        'is_active' => false,
    ]);

    expect(HomepageCtaButton::query()->active()->count())->toBe(2)
        ->and(HomepageCtaButton::query()->inactive()->count())->toBe(1)
        ->and(HomepageCtaButton::query()->ordered()->pluck('id')->all())
        ->toBe([$earlier->id, $later->id, $inactive->id]);
});
