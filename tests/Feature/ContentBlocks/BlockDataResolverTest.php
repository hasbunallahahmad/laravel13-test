<?php

declare(strict_types=1);

use App\ContentBlocks\BlockDataResolver;
use App\ContentBlocks\Data\CtaBlockData;
use App\ContentBlocks\Data\GalleryBlockData;
use App\ContentBlocks\Data\HeroBlockData;
use App\ContentBlocks\Data\ImageBlockData;
use App\ContentBlocks\Data\TextBlockData;
use App\Enums\ContentBlockType;
use App\Models\ContentBlock;
use Illuminate\Support\Str;
use InvalidArgumentException;

it('resolves hero block data', function (): void {
    $resolver = new BlockDataResolver();

    $data = $resolver->resolveData(
        ContentBlockType::HERO,
        [
            'title' => 'Selamat Datang',
            'alignment' => 'center',
        ],
    );

    expect($data)
        ->toBeInstanceOf(HeroBlockData::class)
        ->and($data->title)
        ->toBe('Selamat Datang')
        ->and($data->alignment)
        ->toBe('center');
});

it('resolves text block data', function (): void {
    $resolver = new BlockDataResolver();

    $data = $resolver->resolveData(
        ContentBlockType::TEXT,
        [
            'content' => 'Konten halaman.',
        ],
    );

    expect($data)
        ->toBeInstanceOf(TextBlockData::class)
        ->and($data->content)
        ->toBe('Konten halaman.');
});

it('resolves image block data', function (): void {
    $resolver = new BlockDataResolver();

    $imageUuid = (string) Str::uuid();

    $data = $resolver->resolveData(
        ContentBlockType::IMAGE,
        [
            'image_uuid' => $imageUuid,
            'alt' => 'Foto gedung',
        ],
    );

    expect($data)
        ->toBeInstanceOf(ImageBlockData::class)
        ->and($data->image_uuid)
        ->toBe($imageUuid);
});

it('resolves gallery block data', function (): void {
    $resolver = new BlockDataResolver();

    $imageUuid = (string) Str::uuid();

    $data = $resolver->resolveData(
        ContentBlockType::GALLERY,
        [
            'images' => [$imageUuid],
            'columns' => 3,
        ],
    );

    expect($data)
        ->toBeInstanceOf(GalleryBlockData::class)
        ->and($data->images)
        ->toBe([$imageUuid])
        ->and($data->columns)
        ->toBe(3);
});

it('resolves CTA block data', function (): void {
    $resolver = new BlockDataResolver();

    $data = $resolver->resolveData(
        ContentBlockType::CTA,
        [
            'title' => 'Kunjungi Perpustakaan',
            'button_text' => 'Lihat Koleksi',
            'button_url' => '/koleksi',
        ],
    );

    expect($data)
        ->toBeInstanceOf(CtaBlockData::class)
        ->and($data->title)
        ->toBe('Kunjungi Perpustakaan')
        ->and($data->button_text)
        ->toBe('Lihat Koleksi');
});

it('resolves data from a content block model', function (): void {
    $block = ContentBlock::factory()->create([
        'type' => ContentBlockType::TEXT,
        'data' => [
            'content' => 'Konten dari model.',
        ],
    ]);

    $resolver = new BlockDataResolver();

    $data = $resolver->resolve($block);

    expect($data)
        ->toBeInstanceOf(TextBlockData::class)
        ->and($data->content)
        ->toBe('Konten dari model.');
});

it('returns the registered data class', function (): void {
    $resolver = new BlockDataResolver();

    expect($resolver->dataClass(ContentBlockType::HERO))
        ->toBe(HeroBlockData::class);

    expect($resolver->dataClass(ContentBlockType::TEXT))
        ->toBe(TextBlockData::class);

    expect($resolver->dataClass(ContentBlockType::IMAGE))
        ->toBe(ImageBlockData::class);

    expect($resolver->dataClass(ContentBlockType::GALLERY))
        ->toBe(GalleryBlockData::class);

    expect($resolver->dataClass(ContentBlockType::CTA))
        ->toBe(CtaBlockData::class);
});

it('passes invalid block data validation to the data object', function (): void {
    $resolver = new BlockDataResolver();

    expect(fn() => $resolver->resolveData(
        ContentBlockType::TEXT,
        [],
    ))->toThrow(InvalidArgumentException::class);
});

it('passes invalid hero data validation to the data object', function (): void {
    $resolver = new BlockDataResolver();

    expect(fn() => $resolver->resolveData(
        ContentBlockType::HERO,
        [],
    ))->toThrow(InvalidArgumentException::class);
});

it('passes invalid image data validation to the data object', function (): void {
    $resolver = new BlockDataResolver();

    expect(fn() => $resolver->resolveData(
        ContentBlockType::IMAGE,
        [],
    ))->toThrow(InvalidArgumentException::class);
});

it('passes invalid gallery data validation to the data object', function (): void {
    $resolver = new BlockDataResolver();

    expect(fn() => $resolver->resolveData(
        ContentBlockType::GALLERY,
        [],
    ))->toThrow(InvalidArgumentException::class);
});

it('passes invalid CTA data validation to the data object', function (): void {
    $resolver = new BlockDataResolver();

    expect(fn() => $resolver->resolveData(
        ContentBlockType::CTA,
        [],
    ))->toThrow(InvalidArgumentException::class);
});
