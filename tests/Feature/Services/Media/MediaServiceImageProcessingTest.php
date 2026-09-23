<?php

declare(strict_types=1);

use App\Data\Media\MediaUploadData;
use App\Models\Media;
use App\Services\Media\MediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('processes an uploaded image and stores its derivatives', function () {
    $file = UploadedFile::fake()->image(
        'gedung-dinas.jpg',
        1200,
        800,
    );

    $media = app(MediaService::class)->upload(
        new MediaUploadData(
            file: $file,
            altText: 'Gedung Dinas',
            caption: 'Gedung Dinas Arsip dan Perpustakaan',
        ),
    );

    expect($media)
        ->toBeInstanceOf(Media::class)
        ->and($media->mime_type)
        ->toBe('image/jpeg')
        ->and($media->extension)
        ->toBe('jpg')
        ->and($media->metadata)
        ->toMatchArray([
            'width' => 1200,
            'height' => 800,
        ]);

    $directory = $media->path;
    $original = $directory.'/'.$media->file_name;

    Storage::disk('public')
        ->assertExists($original);

    $metadata = $media->metadata;

    expect($metadata)
        ->toHaveKey('variants')
        ->and($metadata['variants'])
        ->toHaveKeys([
            'webp',
            'thumbnail',
        ]);

    Storage::disk('public')
        ->assertExists($metadata['variants']['webp']);

    Storage::disk('public')
        ->assertExists($metadata['variants']['thumbnail']);
});

it('does not create a duplicate webp variant for a webp upload', function () {
    $file = UploadedFile::fake()->image(
        'banner.webp',
        1200,
        800,
    );

    $media = app(MediaService::class)->upload(
        new MediaUploadData(
            file: $file,
        ),
    );

    $metadata = $media->metadata;

    Storage::disk('public')
        ->assertExists(
            $media->path.'/'.$media->file_name,
        );

    expect($metadata)
        ->toHaveKey('variants')
        ->and($metadata['variants'])
        ->toHaveKey('thumbnail')
        ->and($metadata['variants']['webp'] ?? null)
        ->toBeNull();

    Storage::disk('public')
        ->assertExists($metadata['variants']['thumbnail']);
});

it('does not process pdf files through image processing', function () {
    $file = UploadedFile::fake()->create(
        'document.pdf',
        100,
        'application/pdf',
    );

    $media = app(MediaService::class)->upload(
        new MediaUploadData(
            file: $file,
        ),
    );

    expect($media->mime_type)
        ->toBe('application/pdf')
        ->and($media->extension)
        ->toBe('pdf')
        ->and($media->metadata)
        ->toBe([]);

    Storage::disk('public')
        ->assertExists(
            $media->path.'/'.$media->file_name,
        );
});

it('deletes image derivatives when media is permanently deleted', function () {
    $file = UploadedFile::fake()->image(
        'gedung-dinas.jpg',
        1200,
        800,
    );

    $media = app(MediaService::class)->upload(
        new MediaUploadData(
            file: $file,
        ),
    );

    $originalPath = $media->path.'/'.$media->file_name;
    $webpPath = $media->metadata['variants']['webp'];
    $thumbnailPath = $media->metadata['variants']['thumbnail'];

    Storage::disk('public')->assertExists($originalPath);
    Storage::disk('public')->assertExists($webpPath);
    Storage::disk('public')->assertExists($thumbnailPath);

    app(MediaService::class)->forceDelete($media);

    expect(Media::query()->find($media->id))
        ->toBeNull();

    Storage::disk('public')->assertMissing($originalPath);
    Storage::disk('public')->assertMissing($webpPath);
    Storage::disk('public')->assertMissing($thumbnailPath);
});

test('it cleans up all image files when media database creation fails', function () {
    $file = UploadedFile::fake()->image(
        'gedung-dinas.jpg',
        1200,
        800,
    );

    Media::creating(function (): void {
        throw new RuntimeException(
            'Simulated media persistence failure.',
        );
    });

    expect(fn () => app(MediaService::class)->upload(
        new MediaUploadData(
            file: $file,
        ),
    ))->toThrow(
        RuntimeException::class,
        'Simulated media persistence failure.',
    );

    expect(Media::query()->count())
        ->toBe(0);

    expect(Storage::disk('public')->allFiles('media'))
        ->toBe([]);
});

test('it rethrows the original exception after cleaning up processed image files', function () {
    $file = UploadedFile::fake()->image(
        'gedung-dinas.jpg',
        1200,
        800,
    );

    Media::creating(function (): void {
        throw new RuntimeException(
            'Original image persistence error.',
        );
    });

    expect(fn () => app(MediaService::class)->upload(
        new MediaUploadData(
            file: $file,
        ),
    ))->toThrow(
        RuntimeException::class,
        'Original image persistence error.',
    );

    expect(Storage::disk('public')->allFiles('media'))
        ->toBe([]);
});
