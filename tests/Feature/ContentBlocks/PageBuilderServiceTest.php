<?php

declare(strict_types=1);

use App\ContentBlocks\PageBuilderService;
use App\Enums\ContentBlockType;
use App\Enums\ContentType;
use App\Models\Content;
use App\Models\ContentBlock;
use App\Models\Media;
use Illuminate\Support\Str;
use InvalidArgumentException;

beforeEach(function (): void {
    $this->service = app(PageBuilderService::class);
});

it('creates a text block for a page', function (): void {
    $content = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $block = $this->service->createBlock(
        content: $content,
        type: ContentBlockType::TEXT,
        data: [
            'content' => 'Selamat datang di website Dinas Arsip.',
        ],
        sortOrder: 1,
    );

    expect($block)
        ->toBeInstanceOf(ContentBlock::class)
        ->and($block->content_id)
        ->toBe($content->id)
        ->and($block->type)
        ->toBe(ContentBlockType::TEXT)
        ->and($block->data)
        ->toBe([
            'content' => 'Selamat datang di website Dinas Arsip.',
        ])
        ->and($block->sort_order)
        ->toBe(1)
        ->and($block->is_active)
        ->toBeTrue();
});

it('rejects creating a block for non-page content', function (): void {
    $content = Content::factory()->create([
        'type' => ContentType::ARTICLE,
    ]);

    expect(
        fn() => $this->service->createBlock(
            content: $content,
            type: ContentBlockType::TEXT,
            data: [
                'content' => 'Konten artikel.',
            ],
        )
    )->toThrow(InvalidArgumentException::class);
});

it('creates an image block with an existing active media reference', function (): void {
    $content = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $media = Media::factory()->create();

    $block = $this->service->createBlock(
        content: $content,
        type: ContentBlockType::IMAGE,
        data: [
            'image_uuid' => $media->uuid,
            'alt' => 'Gedung Dinas Arsip dan Perpustakaan',
        ],
    );

    expect($block->type)
        ->toBe(ContentBlockType::IMAGE)
        ->and($block->data['image_uuid'])
        ->toBe($media->uuid);
});

it('rejects an image block when the media does not exist', function (): void {
    $content = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $mediaUuid = (string) Str::uuid();

    expect(
        fn() => $this->service->createBlock(
            content: $content,
            type: ContentBlockType::IMAGE,
            data: [
                'image_uuid' => $mediaUuid,
                'alt' => 'Gambar tidak ditemukan',
            ],
        )
    )->toThrow(InvalidArgumentException::class);
});

it('rejects an image block when the media has been soft deleted', function (): void {
    $content = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $media = Media::factory()->create();

    $media->delete();

    expect($media->trashed())->toBeTrue();

    expect(
        fn() => $this->service->createBlock(
            content: $content,
            type: ContentBlockType::IMAGE,
            data: [
                'image_uuid' => $media->uuid,
                'alt' => 'Gambar terhapus',
            ],
        )
    )->toThrow(InvalidArgumentException::class);
});

it('creates a gallery block with existing active media references', function (): void {
    $content = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $mediaOne = Media::factory()->create();
    $mediaTwo = Media::factory()->create();
    $mediaThree = Media::factory()->create();

    $block = $this->service->createBlock(
        content: $content,
        type: ContentBlockType::GALLERY,
        data: [
            'images' => [
                $mediaOne->uuid,
                $mediaTwo->uuid,
                $mediaThree->uuid,
            ],
            'columns' => 3,
            'lightbox' => true,
        ],
    );

    expect($block->type)
        ->toBe(ContentBlockType::GALLERY)
        ->and($block->data['images'])
        ->toBe([
            $mediaOne->uuid,
            $mediaTwo->uuid,
            $mediaThree->uuid,
        ])
        ->and($block->data['columns'])
        ->toBe(3)
        ->and($block->data['lightbox'])
        ->toBeTrue();
});

it('rejects a gallery when one media reference does not exist', function (): void {
    $content = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $media = Media::factory()->create();

    expect(
        fn() => $this->service->createBlock(
            content: $content,
            type: ContentBlockType::GALLERY,
            data: [
                'images' => [
                    $media->uuid,
                    (string) Str::uuid(),
                ],
            ],
        )
    )->toThrow(InvalidArgumentException::class);
});

it('rejects a gallery when one media reference has been soft deleted', function (): void {
    $content = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $mediaOne = Media::factory()->create();
    $mediaTwo = Media::factory()->create();

    $mediaTwo->delete();

    expect(
        fn() => $this->service->createBlock(
            content: $content,
            type: ContentBlockType::GALLERY,
            data: [
                'images' => [
                    $mediaOne->uuid,
                    $mediaTwo->uuid,
                ],
            ],
        )
    )->toThrow(InvalidArgumentException::class);
});

it('creates a hero block with an active media reference', function (): void {
    $content = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $media = Media::factory()->create();

    $block = $this->service->createBlock(
        content: $content,
        type: ContentBlockType::HERO,
        data: [
            'title' => 'Selamat Datang',
            'subtitle' => 'Dinas Arsip dan Perpustakaan Kota Semarang',
            'image_uuid' => $media->uuid,
            'alignment' => 'center',
        ],
    );

    expect($block->type)
        ->toBe(ContentBlockType::HERO)
        ->and($block->data['title'])
        ->toBe('Selamat Datang')
        ->and($block->data['image_uuid'])
        ->toBe($media->uuid);
});

it('creates a hero block without a media reference', function (): void {
    $content = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $block = $this->service->createBlock(
        content: $content,
        type: ContentBlockType::HERO,
        data: [
            'title' => 'Selamat Datang',
            'alignment' => 'left',
        ],
    );

    expect($block->type)
        ->toBe(ContentBlockType::HERO)
        ->and($block->data['image_uuid'])
        ->toBeNull();
});

it('updates an existing block belonging to the page', function (): void {
    $content = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $block = ContentBlock::factory()->create([
        'content_id' => $content->id,
        'type' => ContentBlockType::TEXT,
        'data' => [
            'content' => 'Konten lama.',
        ],
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $updated = $this->service->updateBlock(
        content: $content,
        block: $block,
        type: ContentBlockType::TEXT,
        data: [
            'content' => 'Konten baru.',
        ],
        sortOrder: 5,
        isActive: false,
    );

    expect($updated->data)
        ->toBe([
            'content' => 'Konten baru.',
        ])
        ->and($updated->sort_order)
        ->toBe(5)
        ->and($updated->is_active)
        ->toBeFalse();
});

it('updates an image block with an active media reference', function (): void {
    $content = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $oldMedia = Media::factory()->create();
    $newMedia = Media::factory()->create();

    $block = ContentBlock::factory()->create([
        'content_id' => $content->id,
        'type' => ContentBlockType::IMAGE,
        'data' => [
            'image_uuid' => $oldMedia->uuid,
            'alt' => 'Gambar lama',
        ],
    ]);

    $updated = $this->service->updateBlock(
        content: $content,
        block: $block,
        type: ContentBlockType::IMAGE,
        data: [
            'image_uuid' => $newMedia->uuid,
            'alt' => 'Gambar baru',
        ],
    );

    expect($updated->data['image_uuid'])
        ->toBe($newMedia->uuid)
        ->and($updated->data['alt'])
        ->toBe('Gambar baru');
});

it('rejects updating a block that does not belong to the page', function (): void {
    $page = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $otherPage = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $block = ContentBlock::factory()->create([
        'content_id' => $otherPage->id,
        'type' => ContentBlockType::TEXT,
        'data' => [
            'content' => 'Konten halaman lain.',
        ],
    ]);

    expect(
        fn() => $this->service->updateBlock(
            content: $page,
            block: $block,
            type: ContentBlockType::TEXT,
            data: [
                'content' => 'Percobaan manipulasi.',
            ],
        )
    )->toThrow(InvalidArgumentException::class);
});

it('deletes a block belonging to the page', function (): void {
    $content = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $block = ContentBlock::factory()->create([
        'content_id' => $content->id,
    ]);

    $this->service->deleteBlock(
        content: $content,
        block: $block,
    );

    expect($block->fresh()->trashed())->toBeTrue();
});

it('rejects deleting a block that does not belong to the page', function (): void {
    $page = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $otherPage = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $block = ContentBlock::factory()->create([
        'content_id' => $otherPage->id,
    ]);

    expect(
        fn() => $this->service->deleteBlock(
            content: $page,
            block: $block,
        )
    )->toThrow(InvalidArgumentException::class);

    expect($block->fresh()->trashed())->toBeFalse();
});

it('restores a soft deleted block belonging to the page', function (): void {
    $content = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $block = ContentBlock::factory()->create([
        'content_id' => $content->id,
    ]);

    $block->delete();

    expect($block->fresh()->trashed())->toBeTrue();

    $restored = $this->service->restoreBlock(
        content: $content,
        block: $block,
    );

    expect($restored->trashed())->toBeFalse();
});

it('rejects restoring a block that does not belong to the page', function (): void {
    $page = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $otherPage = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $block = ContentBlock::factory()->create([
        'content_id' => $otherPage->id,
    ]);

    $block->delete();

    expect(
        fn() => $this->service->restoreBlock(
            content: $page,
            block: $block,
        )
    )->toThrow(InvalidArgumentException::class);

    expect($block->fresh()->trashed())->toBeTrue();
});

it('reorders blocks belonging to the page', function (): void {
    $content = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $blockOne = ContentBlock::factory()->create([
        'content_id' => $content->id,
        'sort_order' => 0,
    ]);

    $blockTwo = ContentBlock::factory()->create([
        'content_id' => $content->id,
        'sort_order' => 1,
    ]);

    $blockThree = ContentBlock::factory()->create([
        'content_id' => $content->id,
        'sort_order' => 2,
    ]);

    $this->service->reorderBlocks(
        content: $content,
        blockUuids: [
            $blockThree->uuid,
            $blockOne->uuid,
            $blockTwo->uuid,
        ],
    );

    expect($blockThree->fresh()->sort_order)
        ->toBe(0)
        ->and($blockOne->fresh()->sort_order)
        ->toBe(1)
        ->and($blockTwo->fresh()->sort_order)
        ->toBe(2);
});

it('rejects reordering when a block does not belong to the page', function (): void {
    $page = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $otherPage = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $blockOne = ContentBlock::factory()->create([
        'content_id' => $page->id,
        'sort_order' => 0,
    ]);

    $blockOther = ContentBlock::factory()->create([
        'content_id' => $otherPage->id,
        'sort_order' => 0,
    ]);

    expect(
        fn() => $this->service->reorderBlocks(
            content: $page,
            blockUuids: [
                $blockOne->uuid,
                $blockOther->uuid,
            ],
        )
    )->toThrow(InvalidArgumentException::class);

    expect($blockOne->fresh()->sort_order)
        ->toBe(0);
});

it('rejects reordering a non-page content', function (): void {
    $content = Content::factory()->create([
        'type' => ContentType::ARTICLE,
    ]);

    expect(
        fn() => $this->service->reorderBlocks(
            content: $content,
            blockUuids: [],
        )
    )->toThrow(InvalidArgumentException::class);
});

it('allows creating an inactive block', function (): void {
    $content = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $block = $this->service->createBlock(
        content: $content,
        type: ContentBlockType::TEXT,
        data: [
            'content' => 'Block tidak aktif.',
        ],
        isActive: false,
    );

    expect($block->is_active)->toBeFalse();
});

it('allows updating only the block data while preserving order and active state', function (): void {
    $content = Content::factory()->create([
        'type' => ContentType::PAGE,
    ]);

    $block = ContentBlock::factory()->create([
        'content_id' => $content->id,
        'type' => ContentBlockType::TEXT,
        'data' => [
            'content' => 'Konten lama.',
        ],
        'sort_order' => 7,
        'is_active' => false,
    ]);

    $updated = $this->service->updateBlock(
        content: $content,
        block: $block,
        type: ContentBlockType::TEXT,
        data: [
            'content' => 'Konten diperbarui.',
        ],
    );

    expect($updated->data['content'])
        ->toBe('Konten diperbarui.')
        ->and($updated->sort_order)
        ->toBe(7)
        ->and($updated->is_active)
        ->toBeFalse();
});
