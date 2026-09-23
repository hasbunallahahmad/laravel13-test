<?php

declare(strict_types=1);

use App\ContentBlocks\Data\TextBlockData;
use InvalidArgumentException;

it('creates text block data from a valid array', function (): void {
    $data = TextBlockData::fromArray([
        'content' => 'Profil Dinas Arsip dan Perpustakaan.',
    ]);

    expect($data)
        ->toBeInstanceOf(TextBlockData::class)
        ->and($data->content)
        ->toBe('Profil Dinas Arsip dan Perpustakaan.');
});

it('converts text block data back to array', function (): void {
    $data = TextBlockData::fromArray([
        'content' => 'Konten halaman.',
    ]);

    expect($data->toArray())
        ->toBe([
            'content' => 'Konten halaman.',
        ]);
});

it('requires content', function (): void {
    expect(fn() => TextBlockData::fromArray([]))
        ->toThrow(InvalidArgumentException::class);
});

it('requires content to be a string', function (): void {
    expect(fn() => TextBlockData::fromArray([
        'content' => ['invalid'],
    ]))
        ->toThrow(InvalidArgumentException::class);
});

it('does not allow empty content', function (): void {
    expect(fn() => TextBlockData::fromArray([
        'content' => '   ',
    ]))
        ->toThrow(InvalidArgumentException::class);
});

it('validates data explicitly', function (): void {
    TextBlockData::validate([
        'content' => 'Valid content',
    ]);

    expect(
        fn() => TextBlockData::validate([])
    )->toThrow(InvalidArgumentException::class);
});

it('returns no media references', function (): void {
    $data = TextBlockData::fromArray([
        'content' => 'Konten text.',
    ]);

    expect($data->mediaReferences())
        ->toBe([]);
});
