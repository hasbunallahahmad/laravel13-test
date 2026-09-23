<?php

declare(strict_types=1);

use App\ContentBlocks\Data\TextBlockData;
use App\Enums\ContentBlockType;
use App\Enums\ContentType;
use App\Models\Content;
use App\Models\ContentBlock;

it('generates a uuid automatically', function (): void {
    $block = ContentBlock::factory()->create();

    expect($block->uuid)
        ->not->toBeNull()
        ->and($block->uuid)->toBeString();
});

it('uses uuid as route key', function (): void {
    $block = ContentBlock::factory()->create();

    expect($block->getRouteKeyName())->toBe('uuid');
});

it('casts type to content block enum', function (): void {
    $block = ContentBlock::factory()->create([
        'type' => ContentBlockType::HERO,
    ]);

    expect($block->type)->toBe(ContentBlockType::HERO);
});

it('casts data to array', function (): void {
    $block = ContentBlock::factory()->create([
        'data' => [
            'title' => 'Welcome',
            'subtitle' => 'Semarang',
        ],
    ]);

    expect($block->data)
        ->toBeArray()
        ->toHaveKey('title')
        ->toHaveKey('subtitle');
});

it('casts sort order to integer', function (): void {
    $block = ContentBlock::factory()->create([
        'sort_order' => '10',
    ]);

    expect($block->sort_order)
        ->toBeInt()
        ->toBe(10);
});

it('casts active state to boolean', function (): void {
    $block = ContentBlock::factory()->create([
        'is_active' => 1,
    ]);

    expect($block->is_active)
        ->toBeBool()
        ->toBeTrue();
});

it('belongs to content', function (): void {
    $content = Content::factory()->create();

    $block = ContentBlock::factory()->create([
        'content_id' => $content->id,
    ]);

    expect($block->content->is($content))->toBeTrue();
});

it('supports soft deletes', function (): void {
    $block = ContentBlock::factory()->create();

    $block->delete();

    expect(ContentBlock::withTrashed()->find($block->id))
        ->not->toBeNull()
        ->and(ContentBlock::find($block->id))
        ->toBeNull();
});

it('does not allow uuid to be changed', function (): void {
    $block = ContentBlock::factory()->create();

    $block->uuid = (string) fake()->uuid();

    expect(fn() => $block->save())
        ->toThrow(LogicException::class);
});

it('provides an active scope', function (): void {
    ContentBlock::factory()->create([
        'is_active' => true,
    ]);

    ContentBlock::factory()->create([
        'is_active' => false,
    ]);

    expect(ContentBlock::active()->count())->toBe(1);
});

it('provides an inactive scope', function (): void {
    ContentBlock::factory()->create([
        'is_active' => true,
    ]);

    ContentBlock::factory()->create([
        'is_active' => false,
    ]);

    expect(ContentBlock::inactive()->count())->toBe(1);
});

it('filters by block type', function (): void {
    ContentBlock::factory()->create([
        'type' => ContentBlockType::HERO,
    ]);

    ContentBlock::factory()->create([
        'type' => ContentBlockType::TEXT,
    ]);

    expect(
        ContentBlock::ofType(ContentBlockType::HERO)->count()
    )->toBe(1);
});

it('orders blocks by sort order', function (): void {
    $content = Content::factory()->create();

    $second = ContentBlock::factory()->create([
        'content_id' => $content->id,
        'sort_order' => 20,
    ]);

    $first = ContentBlock::factory()->create([
        'content_id' => $content->id,
        'sort_order' => 10,
    ]);

    expect($content->blocks->first()->is($first))->toBeTrue()
        ->and($content->blocks->last()->is($second))->toBeTrue();
});

it('identifies page content', function (): void {
    $content = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    expect($content->isPage())->toBeTrue();
});

it('identifies non-page content', function (): void {
    $content = Content::factory()->create([
        'type' => ContentType::ARTICLE,
    ]);

    expect($content->isPage())->toBeFalse();
});

it('has many content blocks', function (): void {
    $content = Content::factory()->create();

    ContentBlock::factory()->count(3)->create([
        'content_id' => $content->id,
    ]);

    expect($content->blocks)
        ->toHaveCount(3);
});

it('returns content blocks in sort order', function (): void {
    $content = Content::factory()->create();

    $last = ContentBlock::factory()->create([
        'content_id' => $content->id,
        'sort_order' => 30,
    ]);

    $first = ContentBlock::factory()->create([
        'content_id' => $content->id,
        'sort_order' => 10,
    ]);

    $middle = ContentBlock::factory()->create([
        'content_id' => $content->id,
        'sort_order' => 20,
    ]);

    expect($content->blocks->pluck('id')->all())
        ->toBe([
            $first->id,
            $middle->id,
            $last->id,
        ]);
});

it('only returns blocks belonging to the content', function (): void {
    $pageA = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $pageB = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    ContentBlock::factory()->count(2)->create([
        'content_id' => $pageA->id,
    ]);

    ContentBlock::factory()->count(3)->create([
        'content_id' => $pageB->id,
    ]);

    expect($pageA->blocks)->toHaveCount(2)
        ->and($pageB->blocks)->toHaveCount(3);
});

it('validates data explicitly', function (): void {
    TextBlockData::validate([
        'content' => 'Valid content',
    ]);

    expect(
        fn() => TextBlockData::validate([])
    )->toThrow(InvalidArgumentException::class);
});
