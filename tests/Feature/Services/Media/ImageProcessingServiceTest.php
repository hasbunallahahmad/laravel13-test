<?php

use App\Services\Media\ImageProcessingService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->service = app(ImageProcessingService::class);
});

it('converts a jpeg image to webp while preserving the original', function () {
    $file = UploadedFile::fake()->image(
        'gedung-dinas.jpg',
        1200,
        800,
    );

    $result = $this->service->process(
        file: $file,
        directory: 'media/test',
        filename: 'gedung-dinas',
    );

    expect($result)
        ->toHaveKeys([
            'original_path',
            'webp_path',
            'thumbnail_path',
            'width',
            'height',
            'mime_type',
        ])
        ->and($result['width'])->toBe(1200)
        ->and($result['height'])->toBe(800)
        ->and($result['mime_type'])->toBe('image/jpeg');

    Storage::disk('public')
        ->assertExists($result['original_path']);

    Storage::disk('public')
        ->assertExists($result['webp_path']);

    Storage::disk('public')
        ->assertExists($result['thumbnail_path']);

    expect($result['webp_path'])
        ->toEndWith('.webp');

    expect($result['thumbnail_path'])
        ->toEndWith('.webp');
});

it('converts a png image to webp', function () {
    $file = UploadedFile::fake()->image(
        'logo.png',
        1000,
        600,
    );

    $result = $this->service->process(
        file: $file,
        directory: 'media/test',
        filename: 'logo',
    );

    expect($result['mime_type'])
        ->toBe('image/png');

    Storage::disk('public')
        ->assertExists($result['original_path']);

    Storage::disk('public')
        ->assertExists($result['webp_path']);

    Storage::disk('public')
        ->assertExists($result['thumbnail_path']);
});

it('does not create a webp conversion for an existing webp image', function () {
    $file = UploadedFile::fake()->image(
        'banner.webp',
        1200,
        800,
    );

    $result = $this->service->process(
        file: $file,
        directory: 'media/test',
        filename: 'banner',
    );

    expect($result['mime_type'])
        ->toBe('image/webp');

    Storage::disk('public')
        ->assertExists($result['original_path']);

    Storage::disk('public')
        ->assertMissing($result['webp_path']);

    Storage::disk('public')
        ->assertExists($result['thumbnail_path']);
});

it('preserves the original image dimensions', function () {
    $file = UploadedFile::fake()->image(
        'photo.jpg',
        1600,
        900,
    );

    $result = $this->service->process(
        file: $file,
        directory: 'media/test',
        filename: 'photo',
    );

    expect($result['width'])
        ->toBe(1600)
        ->and($result['height'])
        ->toBe(900);
});

it('creates a thumbnail without exceeding the maximum thumbnail dimensions', function () {
    $file = UploadedFile::fake()->image(
        'large-photo.jpg',
        2400,
        1600,
    );

    $result = $this->service->process(
        file: $file,
        directory: 'media/test',
        filename: 'large-photo',
    );

    $thumbnail = Storage::disk('public')
        ->get($result['thumbnail_path']);

    expect($thumbnail)
        ->not->toBeEmpty();
});

it('rejects images that exceed the maximum source dimensions', function () {
    $file = UploadedFile::fake()->image(
        'too-large.jpg',
        5000,
        5000,
    );

    expect(fn() => $this->service->process(
        file: $file,
        directory: 'media/test',
        filename: 'too-large',
    ))
        ->toThrow(InvalidArgumentException::class);
});

it('accepts an image at the maximum source dimensions', function () {
    $file = UploadedFile::fake()->image(
        'maximum-size.jpg',
        4000,
        4000,
    );

    $result = $this->service->process(
        file: $file,
        directory: 'media/test',
        filename: 'maximum-size',
    );

    expect($result['width'])
        ->toBe(4000)
        ->and($result['height'])
        ->toBe(4000);

    Storage::disk('public')
        ->assertExists($result['original_path']);

    Storage::disk('public')
        ->assertExists($result['webp_path']);

    Storage::disk('public')
        ->assertExists($result['thumbnail_path']);
});

it('rejects an image wider than the maximum source width', function () {
    $file = UploadedFile::fake()->image(
        'too-wide.jpg',
        4001,
        4000,
    );

    expect(fn () => $this->service->process(
        file: $file,
        directory: 'media/test',
        filename: 'too-wide',
    ))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects an image taller than the maximum source height', function () {
    $file = UploadedFile::fake()->image(
        'too-tall.jpg',
        4000,
        4001,
    );

    expect(fn () => $this->service->process(
        file: $file,
        directory: 'media/test',
        filename: 'too-tall',
    ))
        ->toThrow(InvalidArgumentException::class);
});

it('preserves the aspect ratio of a landscape thumbnail', function () {
    $file = UploadedFile::fake()->image(
        'landscape.jpg',
        2400,
        1600,
    );

    $result = $this->service->process(
        file: $file,
        directory: 'media/test',
        filename: 'landscape',
    );

    $thumbnailPath = Storage::disk('public')
        ->path($result['thumbnail_path']);

    $thumbnailImage = getimagesize($thumbnailPath);

    expect($thumbnailImage)
        ->not->toBeFalse();

    [$width, $height] = $thumbnailImage;

    expect($width)
        ->toBe(400)
        ->and($height)
        ->toBe(267);
});

it('preserves the aspect ratio of a portrait thumbnail', function () {
    $file = UploadedFile::fake()->image(
        'portrait.jpg',
        1600,
        2400,
    );

    $result = $this->service->process(
        file: $file,
        directory: 'media/test',
        filename: 'portrait',
    );

    $thumbnailPath = Storage::disk('public')
        ->path($result['thumbnail_path']);

    $thumbnailImage = getimagesize($thumbnailPath);

    expect($thumbnailImage)
        ->not->toBeFalse();

    [$width, $height] = $thumbnailImage;

    expect($width)
        ->toBe(267)
        ->and($height)
        ->toBe(400);
});

it('creates a square thumbnail for a square image', function () {
    $file = UploadedFile::fake()->image(
        'square.jpg',
        2000,
        2000,
    );

    $result = $this->service->process(
        file: $file,
        directory: 'media/test',
        filename: 'square',
    );

    $thumbnailPath = Storage::disk('public')
        ->path($result['thumbnail_path']);

    $thumbnailImage = getimagesize($thumbnailPath);

    expect($thumbnailImage)
        ->not->toBeFalse();

    [$width, $height] = $thumbnailImage;

    expect($width)
        ->toBe(400)
        ->and($height)
        ->toBe(400);
});

it('creates only a thumbnail for an existing webp source', function () {
    $file = UploadedFile::fake()->image(
        'existing.webp',
        1200,
        800,
    );

    $result = $this->service->process(
        file: $file,
        directory: 'media/test',
        filename: 'existing',
    );

    expect($result['webp_path'])
        ->toBeNull();

    Storage::disk('public')
        ->assertExists($result['original_path']);

    Storage::disk('public')
        ->assertExists($result['thumbnail_path']);
});

it('uses a safe extension derived from the validated mime type', function () {
    $file = UploadedFile::fake()->image(
        'photo.php.jpg',
        1200,
        800,
    );

    $result = $this->service->process(
        file: $file,
        directory: 'media/test',
        filename: 'safe-photo',
    );

    expect($result['original_path'])
        ->toBe('media/test/safe-photo.jpg');

    Storage::disk('public')
        ->assertExists('media/test/safe-photo.jpg');

    Storage::disk('public')
        ->assertMissing('media/test/safe-photo.php.jpg');
});

it('rejects unsupported image mime types', function () {
    $file = UploadedFile::fake()->create(
        'malicious.php',
        10,
        'application/x-httpd-php',
    );

    expect(fn () => $this->service->process(
        file: $file,
        directory: 'media/test',
        filename: 'malicious',
    ))
        ->toThrow(InvalidArgumentException::class);
});

it('processes uploaded images into original, webp, and thumbnail assets', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image(
        'gedung-dinas.jpg',
        1200,
        800,
    );

    $data = new \App\Data\Media\MediaUploadData(
        file: $file,
        altText: 'Gedung Dinas',
        caption: 'Gedung Dinas Arsip dan Perpustakaan',
    );

    $media = app(\App\Services\Media\MediaService::class)
        ->upload($data);

    expect($media)
        ->toBeInstanceOf(\App\Models\Media::class)
        ->and($media->mime_type)
        ->toBe('image/jpeg')
        ->and($media->extension)
        ->toBe('jpg')
        ->and($media->path)
        ->toMatch('/^media\/\d{4}\/\d{2}$/')
        ->and($media->file_name)
        ->toEndWith('.jpg');

    Storage::disk('public')
        ->assertExists(
            $media->path.'/'.$media->file_name,
        );

    expect($media->metadata)
        ->toHaveKey('variants')
        ->and($media->metadata['variants'])
        ->toHaveKeys([
            'webp',
            'thumbnail',
        ]);

    Storage::disk('public')
        ->assertExists(
            $media->metadata['variants']['webp'],
        );

    Storage::disk('public')
        ->assertExists(
            $media->metadata['variants']['thumbnail'],
        );
});
