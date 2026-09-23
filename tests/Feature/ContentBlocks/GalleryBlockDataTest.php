<?php

declare(strict_types=1);

use App\ContentBlocks\Data\GalleryBlockData;
use Illuminate\Support\Str;
use InvalidArgumentException;

it('creates gallery block data from a valid array', function (): void {
    $imageUuidOne = (string) Str::uuid();
    $imageUuidTwo = (string) Str::uuid();

    $data = GalleryBlockData::fromArray([
        'images' => [
            $imageUuidOne,
            $imageUuidTwo,
        ],
        'columns' => 3,
        'caption' => 'Dokumentasi kegiatan Dinas Arsip.',
        'lightbox' => true,
    ]);

    expect($data)
        ->toBeInstanceOf(GalleryBlockData::class)
        ->and($data->images)
        ->toBe([
            $imageUuidOne,
            $imageUuidTwo,
        ])
        ->and($data->columns)
        ->toBe(3)
        ->and($data->caption)
        ->toBe('Dokumentasi kegiatan Dinas Arsip.')
        ->and($data->lightbox)
        ->toBeTrue();
});

it('converts gallery block data back to array', function (): void {
    $imageUuid = (string) Str::uuid();

    $data = GalleryBlockData::fromArray([
        'images' => [$imageUuid],
    ]);

    expect($data->toArray())
        ->toBe([
            'images' => [$imageUuid],
            'columns' => 3,
            'caption' => null,
            'lightbox' => true,
        ]);
});

it('requires images', function (): void {
    expect(fn() => GalleryBlockData::fromArray([]))
        ->toThrow(InvalidArgumentException::class);
});

it('requires images to be an array', function (): void {
    expect(fn() => GalleryBlockData::fromArray([
        'images' => 'invalid',
    ]))->toThrow(InvalidArgumentException::class);
});

it('requires at least one image', function (): void {
    expect(fn() => GalleryBlockData::fromArray([
        'images' => [],
    ]))->toThrow(InvalidArgumentException::class);
});

it('requires every image to be a valid UUID', function (): void {
    expect(fn() => GalleryBlockData::fromArray([
        'images' => [
            (string) Str::uuid(),
            'invalid-uuid',
        ],
    ]))->toThrow(InvalidArgumentException::class);
});

it('requires image UUID values to be strings', function (): void {
    expect(fn() => GalleryBlockData::fromArray([
        'images' => [
            (string) Str::uuid(),
            ['invalid'],
        ],
    ]))->toThrow(InvalidArgumentException::class);
});

it('accepts supported column counts', function (): void {
    $imageUuid = (string) Str::uuid();

    foreach ([1, 2, 3, 4] as $columns) {
        $data = GalleryBlockData::fromArray([
            'images' => [$imageUuid],
            'columns' => $columns,
        ]);

        expect($data->columns)->toBe($columns);
    }
});

it('rejects unsupported column counts', function (): void {
    expect(fn() => GalleryBlockData::fromArray([
        'images' => [(string) Str::uuid()],
        'columns' => 5,
    ]))->toThrow(InvalidArgumentException::class);
});

it('validates caption', function (): void {
    expect(fn() => GalleryBlockData::fromArray([
        'images' => [(string) Str::uuid()],
        'caption' => ['invalid'],
    ]))->toThrow(InvalidArgumentException::class);
});

it('validates lightbox', function (): void {
    expect(fn() => GalleryBlockData::fromArray([
        'images' => [(string) Str::uuid()],
        'lightbox' => 'yes',
    ]))->toThrow(InvalidArgumentException::class);
});

it('uses sensible defaults', function (): void {
    $data = GalleryBlockData::fromArray([
        'images' => [(string) Str::uuid()],
    ]);

    expect($data->columns)
        ->toBe(3)
        ->and($data->caption)
        ->toBeNull()
        ->and($data->lightbox)
        ->toBeTrue();
});

it('validates data explicitly', function (): void {
    GalleryBlockData::validate([
        'images' => [(string) Str::uuid()],
    ]);

    expect(
        fn() => GalleryBlockData::validate([])
    )->toThrow(InvalidArgumentException::class);
});

it('returns all gallery media references', function (): void {
    $imageOne = (string) Str::uuid();
    $imageTwo = (string) Str::uuid();
    $imageThree = (string) Str::uuid();

    $data = GalleryBlockData::fromArray([
        'images' => [
            $imageOne,
            $imageTwo,
            $imageThree,
        ],
    ]);

    expect($data->mediaReferences())
        ->toBe([
            $imageOne,
            $imageTwo,
            $imageThree,
        ]);
});
