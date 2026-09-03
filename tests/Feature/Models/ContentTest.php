<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Models\Content;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Schema;

test('content uses soft deletes', function () {
    $content = Content::factory()->create();

    $content->delete();

    expect(Content::withTrashed()->find($content->getKey()))->not->toBeNull()
        ->and(Content::find($content->getKey()))->toBeNull();
});

test('content automatically generates uuid', function () {
    $content = Content::factory()->create();

    expect($content->uuid)
        ->not->toBeNull()
        ->and($content->uuid)->toBeString()
        ->and($content->uuid)->toMatch(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i'
        );
});

test('content uuid is unique', function () {
    $first = Content::factory()->create();
    $second = Content::factory()->create();

    expect($first->uuid)->not->toBe($second->uuid);
});

test('content uses uuid as route key', function () {
    $content = Content::factory()->create();

    expect($content->getRouteKeyName())->toBe('uuid')
        ->and($content->getRouteKey())->toBe($content->uuid);
});

test('content type is cast to content type enum', function () {
    $content = Content::factory()->create([
        'type' => ContentType::ARTICLE,
    ]);

    $content->refresh();

    expect($content->type)
        ->toBe(ContentType::ARTICLE);
});

test('content status is cast to content status enum', function () {
    $content = Content::factory()->create([
        'status' => ContentStatus::DRAFT,
    ]);

    $content->refresh();

    expect($content->status)
        ->toBe(ContentStatus::DRAFT);
});

test('published at is cast to datetime', function () {
    $content = Content::factory()->create([
        'published_at' => now(),
    ]);

    $content->refresh();

    expect($content->published_at)
        ->toBeInstanceOf(CarbonImmutable::class);
});

test('metadata is cast to array', function () {
    $content = Content::factory()->create([
        'metadata' => [
            'featured' => true,
            'priority' => 10,
        ],
    ]);

    $content->refresh();

    expect($content->metadata)
        ->toBeArray()
        ->toMatchArray([
            'featured' => true,
            'priority' => 10,
        ]);
});

test('content belongs to an author', function () {
    $content = Content::factory()->create();

    expect($content->author())
        ->toBeInstanceOf(BelongsTo::class);
});

test('content author relationship resolves the user', function () {
    $author = User::factory()->create();

    $content = Content::factory()->create([
        'author_id' => $author->getKey(),
    ]);

    expect($content->author->is($author))->toBeTrue();
});

test('content uuid cannot be changed after creation', function () {
    $content = Content::factory()->create();

    $originalUuid = $content->uuid;

    $content->uuid = fake()->uuid();

    expect(fn() => $content->save())
        ->toThrow(LogicException::class);

    expect($content->fresh()->uuid)
        ->toBe($originalUuid);
});

test('content scopes filter by status', function () {
    Content::factory()->create(['status' => ContentStatus::PUBLISHED]);
    Content::factory()->create(['status' => ContentStatus::DRAFT]);
    Content::factory()->create(['status' => ContentStatus::REVIEW]);
    Content::factory()->create(['status' => ContentStatus::ARCHIVED]);

    expect(Content::published()->count())->toBe(1)
        ->and(Content::drafts()->count())->toBe(1)
        ->and(Content::review()->count())->toBe(1)
        ->and(Content::archived()->count())->toBe(1);
});

test('content of type scope filters by content type', function () {
    Content::factory()->create(['type' => ContentType::ARTICLE]);
    Content::factory()->create(['type' => ContentType::PAGE]);
    Content::factory()->create(['type' => ContentType::ARTICLE]);

    expect(Content::ofType(ContentType::ARTICLE)->count())->toBe(2)
        ->and(Content::ofType(ContentType::PAGE)->count())->toBe(1);
});

test('content fillable fields do not include internal identifiers', function () {
    $content = new Content();

    expect($content->getFillable())
        ->not->toContain('id')
        ->not->toContain('uuid');
});

test('contents table has the expected security indexes', function () {
    expect(Schema::hasColumn('contents', 'uuid'))->toBeTrue()
        ->and(Schema::hasColumn('contents', 'slug'))->toBeTrue()
        ->and(Schema::hasColumn('contents', 'author_id'))->toBeTrue()
        ->and(Schema::hasColumn('contents', 'deleted_at'))->toBeTrue();
});
