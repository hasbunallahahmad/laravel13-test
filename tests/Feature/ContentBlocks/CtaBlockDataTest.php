<?php

declare(strict_types=1);

use App\ContentBlocks\Data\CtaBlockData;
use InvalidArgumentException;

it('creates CTA block data from a valid array', function (): void {
    $data = CtaBlockData::fromArray([
        'title' => 'Kunjungi Perpustakaan',
        'description' => 'Temukan koleksi dan layanan perpustakaan kami.',
        'button_text' => 'Lihat Koleksi',
        'button_url' => '/koleksi',
        'alignment' => 'center',
    ]);

    expect($data)
        ->toBeInstanceOf(CtaBlockData::class)
        ->and($data->title)
        ->toBe('Kunjungi Perpustakaan')
        ->and($data->description)
        ->toBe('Temukan koleksi dan layanan perpustakaan kami.')
        ->and($data->button_text)
        ->toBe('Lihat Koleksi')
        ->and($data->button_url)
        ->toBe('/koleksi')
        ->and($data->alignment)
        ->toBe('center');
});

it('converts CTA block data back to array', function (): void {
    $data = CtaBlockData::fromArray([
        'title' => 'Hubungi Kami',
        'button_text' => 'Kontak',
        'button_url' => '/kontak',
    ]);

    expect($data->toArray())->toBe([
        'title' => 'Hubungi Kami',
        'description' => null,
        'button_text' => 'Kontak',
        'button_url' => '/kontak',
        'alignment' => 'left',
    ]);
});

it('requires title', function (): void {
    expect(fn() => CtaBlockData::fromArray([
        'button_text' => 'Kontak',
        'button_url' => '/kontak',
    ]))->toThrow(InvalidArgumentException::class);
});

it('requires title to be a string', function (): void {
    expect(fn() => CtaBlockData::fromArray([
        'title' => ['invalid'],
        'button_text' => 'Kontak',
        'button_url' => '/kontak',
    ]))->toThrow(InvalidArgumentException::class);
});

it('does not allow empty title', function (): void {
    expect(fn() => CtaBlockData::fromArray([
        'title' => '   ',
        'button_text' => 'Kontak',
        'button_url' => '/kontak',
    ]))->toThrow(InvalidArgumentException::class);
});

it('allows optional description', function (): void {
    $data = CtaBlockData::fromArray([
        'title' => 'Hubungi Kami',
        'button_text' => 'Kontak',
        'button_url' => '/kontak',
    ]);

    expect($data->description)->toBeNull();
});

it('validates description', function (): void {
    expect(fn() => CtaBlockData::fromArray([
        'title' => 'Hubungi Kami',
        'description' => ['invalid'],
        'button_text' => 'Kontak',
        'button_url' => '/kontak',
    ]))->toThrow(InvalidArgumentException::class);
});

it('requires button text', function (): void {
    expect(fn() => CtaBlockData::fromArray([
        'title' => 'Hubungi Kami',
        'button_url' => '/kontak',
    ]))->toThrow(InvalidArgumentException::class);
});

it('does not allow empty button text', function (): void {
    expect(fn() => CtaBlockData::fromArray([
        'title' => 'Hubungi Kami',
        'button_text' => '   ',
        'button_url' => '/kontak',
    ]))->toThrow(InvalidArgumentException::class);
});

it('requires button URL', function (): void {
    expect(fn() => CtaBlockData::fromArray([
        'title' => 'Hubungi Kami',
        'button_text' => 'Kontak',
    ]))->toThrow(InvalidArgumentException::class);
});

it('does not allow empty button URL', function (): void {
    expect(fn() => CtaBlockData::fromArray([
        'title' => 'Hubungi Kami',
        'button_text' => 'Kontak',
        'button_url' => '   ',
    ]))->toThrow(InvalidArgumentException::class);
});

it('validates button URL type', function (): void {
    expect(fn() => CtaBlockData::fromArray([
        'title' => 'Hubungi Kami',
        'button_text' => 'Kontak',
        'button_url' => ['invalid'],
    ]))->toThrow(InvalidArgumentException::class);
});

it('accepts supported alignments', function (): void {
    foreach (['left', 'center', 'right'] as $alignment) {
        $data = CtaBlockData::fromArray([
            'title' => 'CTA',
            'button_text' => 'Klik',
            'button_url' => '/test',
            'alignment' => $alignment,
        ]);

        expect($data->alignment)->toBe($alignment);
    }
});

it('rejects invalid alignment', function (): void {
    expect(fn() => CtaBlockData::fromArray([
        'title' => 'CTA',
        'button_text' => 'Klik',
        'button_url' => '/test',
        'alignment' => 'invalid',
    ]))->toThrow(InvalidArgumentException::class);
});

it('validates data explicitly', function (): void {
    CtaBlockData::validate([
        'title' => 'Valid CTA',
        'button_text' => 'Klik',
        'button_url' => '/test',
    ]);

    expect(
        fn() => CtaBlockData::validate([])
    )->toThrow(InvalidArgumentException::class);
});

it('returns no media references', function (): void {
    $data = CtaBlockData::fromArray([
        'title' => 'Kunjungi Perpustakaan',
        'button_text' => 'Lihat Koleksi',
        'button_url' => '/koleksi',
    ]);

    expect($data->mediaReferences())
        ->toBe([]);
});
