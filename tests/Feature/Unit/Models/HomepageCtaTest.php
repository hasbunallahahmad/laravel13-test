<?php

declare(strict_types=1);

use App\Models\HomepageCta;
use App\Models\HomepageCtaButton;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('generates a UUID when creating a homepage CTA', function (): void {
    $cta = HomepageCta::query()->create([
        'title' => 'Jelajahi Layanan',
        'description' => 'Temukan layanan kami.',
    ]);

    expect($cta->uuid)->not->toBeNull()
        ->and($cta->getRouteKeyName())->toBe('uuid');
});

it('has many CTA buttons', function (): void {
    $cta = HomepageCta::query()->create([
        'title' => 'Jelajahi Layanan',
    ]);

    $button = HomepageCtaButton::query()->create([
        'homepage_cta_id' => $cta->id,
        'label' => 'Lihat Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    expect($cta->buttons)->toHaveCount(1)
        ->and($cta->buttons->first()->is($button))->toBeTrue();
});

it('casts sort order and active status correctly', function (): void {
    $cta = HomepageCta::query()->create([
        'title' => 'Jelajahi Layanan',
        'sort_order' => 5,
        'is_active' => true,
    ]);

    expect($cta->sort_order)->toBeInt()
        ->and($cta->is_active)->toBeTrue();
});

it('can be soft deleted', function (): void {
    $cta = HomepageCta::query()->create([
        'title' => 'Jelajahi Layanan',
    ]);

    $cta->delete();

    $this->assertSoftDeleted('homepage_ctas', [
        'id' => $cta->id,
    ]);
});

it('provides active inactive and ordered query scopes', function (): void {
    $later = HomepageCta::query()->create([
        'title' => 'CTA Kedua',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    $earlier = HomepageCta::query()->create([
        'title' => 'CTA Pertama',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $inactive = HomepageCta::query()->create([
        'title' => 'CTA Nonaktif',
        'sort_order' => 3,
        'is_active' => false,
    ]);

    expect(HomepageCta::query()->active()->count())->toBe(2)
        ->and(HomepageCta::query()->inactive()->count())->toBe(1)
        ->and(HomepageCta::query()->ordered()->pluck('id')->all())
        ->toBe([$earlier->id, $later->id, $inactive->id]);
});
