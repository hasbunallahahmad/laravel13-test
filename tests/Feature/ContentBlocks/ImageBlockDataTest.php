<?php

declare(strict_types=1);

use App\ContentBlocks\Data\ImageBlockData;
use Illuminate\Support\Str;
use InvalidArgumentException;

it('creates image block data from a valid array', function (): void {
    $imageUuid = (string) Str::uuid();

    $data = ImageBlockData::fromArray([
        'image_uuid' => $imageUuid,
        'alt' => 'Gedung Dinas Arsip dan Perpustakaan',
        'caption' => 'Gedung Dinas Arsip dan Perpustakaan Kota Semarang.',
        'link_url' => '/profil',
    ]);

    expect($data)
        ->toBeInstanceOf(ImageBlockData::class)
        ->and($data->image_uuid)->toBe($imageUuid)
        ->and($data->alt)->toBe('Gedung Dinas Arsip dan Perpustakaan')
        ->and($data->caption)
        ->toBe('Gedung Dinas Arsip dan Perpustakaan Kota Semarang.')
        ->and($data->link_url)
        ->toBe('/profil');
});

it('converts image block data back to array', function (): void {
    $imageUuid = (string) Str::uuid();

    $data = ImageBlockData::fromArray([
        'image_uuid' => $imageUuid,
        'alt' => 'Foto gedung',
    ]);

    expect($data->toArray())->toBe([
        'image_uuid' => $imageUuid,
        'alt' => 'Foto gedung',
        'caption' => null,
        'link_url' => null,
    ]);
});

it('requires image UUID', function (): void {
    expect(fn() => ImageBlockData::fromArray([
        'alt' => 'Foto gedung',
    ]))->toThrow(InvalidArgumentException::class);
});

it('requires image UUID to be valid', function (): void {
    expect(fn() => ImageBlockData::fromArray([
        'image_uuid' => 'invalid-uuid',
        'alt' => 'Foto gedung',
    ]))->toThrow(InvalidArgumentException::class);
});

it('requires alt text', function (): void {
    expect(fn() => ImageBlockData::fromArray([
        'image_uuid' => (string) Str::uuid(),
    ]))->toThrow(InvalidArgumentException::class);
});

it('requires alt text to be a string', function (): void {
    expect(fn() => ImageBlockData::fromArray([
        'image_uuid' => (string) Str::uuid(),
        'alt' => ['invalid'],
    ]))->toThrow(InvalidArgumentException::class);
});

it('does not allow empty alt text', function (): void {
    expect(fn() => ImageBlockData::fromArray([
        'image_uuid' => (string) Str::uuid(),
        'alt' => '   ',
    ]))->toThrow(InvalidArgumentException::class);
});

it('allows optional caption and link URL', function (): void {
    $data = ImageBlockData::fromArray([
        'image_uuid' => (string) Str::uuid(),
        'alt' => 'Foto',
    ]);

    expect($data->caption)
        ->toBeNull()
        ->and($data->link_url)
        ->toBeNull();
});

it('validates optional caption', function (): void {
    expect(fn() => ImageBlockData::fromArray([
        'image_uuid' => (string) Str::uuid(),
        'alt' => 'Foto',
        'caption' => ['invalid'],
    ]))->toThrow(InvalidArgumentException::class);
});

it('validates optional link URL', function (): void {
    expect(fn() => ImageBlockData::fromArray([
        'image_uuid' => (string) Str::uuid(),
        'alt' => 'Foto',
        'link_url' => ['invalid'],
    ]))->toThrow(InvalidArgumentException::class);
});

it('validates data explicitly', function (): void {
    ImageBlockData::validate([
        'image_uuid' => (string) Str::uuid(),
        'alt' => 'Valid image',
    ]);

    expect(
        fn() => ImageBlockData::validate([])
    )->toThrow(InvalidArgumentException::class);
});

it('returns the image media reference', function (): void {
    $imageUuid = (string) Str::uuid();

    $data = ImageBlockData::fromArray([
        'image_uuid' => $imageUuid,
        'alt' => 'Foto gedung',
    ]);

    expect($data->mediaReferences())
        ->toBe([$imageUuid]);
});
