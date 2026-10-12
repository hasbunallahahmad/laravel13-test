<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Models\Content;

uses(Tests\TestCase::class);

test('published scope excludes content scheduled for a future publication date', function () {
    $content = Content::factory()->create([
        'type' => ContentType::NEWS,
        'status' => ContentStatus::PUBLISHED,
        'published_at' => now()->addDay(),
    ]);

    expect(
        Content::query()
            ->published()
            ->whereKey($content->getKey())
            ->exists()
    )->toBeFalse();
});

test('published scope includes content whose publication date has been reached', function () {
    $content = Content::factory()->create([
        'type' => ContentType::NEWS,
        'status' => ContentStatus::PUBLISHED,
        'published_at' => now()->subMinute(),
    ]);

    expect(
        Content::query()
            ->published()
            ->whereKey($content->getKey())
            ->exists()
    )->toBeTrue();
});

test('published scope includes published content without a publication date', function () {
    $content = Content::factory()->create([
        'type' => ContentType::NEWS,
        'status' => ContentStatus::PUBLISHED,
        'published_at' => null,
    ]);

    expect(
        Content::query()
            ->published()
            ->whereKey($content->getKey())
            ->exists()
    )->toBeTrue();
});

test('published scope excludes non-published content even when publication date has been reached', function () {
    $content = Content::factory()->create([
        'type' => ContentType::NEWS,
        'status' => ContentStatus::DRAFT,
        'published_at' => now()->subMinute(),
    ]);

    expect(
        Content::query()
            ->published()
            ->whereKey($content->getKey())
            ->exists()
    )->toBeFalse();
});
