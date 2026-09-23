<?php

declare(strict_types=1);

use App\ContentBlocks\Data\HeroBlockData;
use InvalidArgumentException;
use Illuminate\Support\Str;

it('creates hero block data from a valid array', function (): void {
    $imageUuid = (string) Str::uuid();

    $data = HeroBlockData::fromArray([
        'title' => 'Selamat Datang di Dinas Arsip',
        'subtitle' => 'Melayani masyarakat melalui arsip dan perpustakaan.',
        'image_uuid' => $imageUuid,
        'button_text' => 'Selengkapnya',
        'button_url' => '/profil',
        'alignment' => 'center',
    ]);

    expect($data)
        ->toBeInstanceOf(HeroBlockData::class)
        ->and($data->title)->toBe('Selamat Datang di Dinas Arsip')
        ->and($data->subtitle)->toBe('Melayani masyarakat melalui arsip dan perpustakaan.')
        ->and($data->image_uuid)->toBe($imageUuid)
        ->and($data->button_text)->toBe('Selengkapnya')
        ->and($data->button_url)->toBe('/profil')
        ->and($data->alignment)->toBe('center');
});

it('converts hero block data back to array', function (): void {
    $imageUuid = (string) Str::uuid();

    $data = HeroBlockData::fromArray([
        'title' => 'Hero Title',
        'image_uuid' => $imageUuid,
    ]);

    expect($data->toArray())->toBe([
        'title' => 'Hero Title',
        'subtitle' => null,
        'image_uuid' => $imageUuid,
        'button_text' => null,
        'button_url' => null,
        'alignment' => 'left',
    ]);
});

it('requires title', function (): void {
    expect(fn() => HeroBlockData::fromArray([]))
        ->toThrow(InvalidArgumentException::class);
});

it('requires title to be a string', function (): void {
    expect(fn() => HeroBlockData::fromArray([
        'title' => ['invalid'],
    ]))->toThrow(InvalidArgumentException::class);
});

it('does not allow empty title', function (): void {
    expect(fn() => HeroBlockData::fromArray([
        'title' => '   ',
    ]))->toThrow(InvalidArgumentException::class);
});

it('validates image UUID', function (): void {
    expect(fn() => HeroBlockData::fromArray([
        'title' => 'Hero',
        'image_uuid' => 'not-a-uuid',
    ]))->toThrow(InvalidArgumentException::class);
});

it('allows hero without image', function (): void {
    $data = HeroBlockData::fromArray([
        'title' => 'Hero tanpa gambar',
    ]);

    expect($data->image_uuid)->toBeNull();
});

it('validates alignment', function (): void {
    expect(fn() => HeroBlockData::fromArray([
        'title' => 'Hero',
        'alignment' => 'invalid',
    ]))->toThrow(InvalidArgumentException::class);
});

it('accepts supported alignments', function (): void {
    foreach (['left', 'center', 'right'] as $alignment) {
        $data = HeroBlockData::fromArray([
            'title' => 'Hero',
            'alignment' => $alignment,
        ]);

        expect($data->alignment)->toBe($alignment);
    }
});

it('validates data explicitly', function (): void {
    HeroBlockData::validate([
        'title' => 'Valid Hero',
    ]);

    expect(
        fn() => HeroBlockData::validate([])
    )->toThrow(InvalidArgumentException::class);
});

it('returns no media references when image is not configured', function (): void {
    $data = HeroBlockData::fromArray([
        'title' => 'Selamat Datang',
    ]);

    expect($data->mediaReferences())
        ->toBe([]);
});

it('returns the hero image media reference', function (): void {
    $imageUuid = (string) Str::uuid();

    $data = HeroBlockData::fromArray([
        'title' => 'Selamat Datang',
        'image_uuid' => $imageUuid,
    ]);

    expect($data->mediaReferences())
        ->toBe([$imageUuid]);
});
