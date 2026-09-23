<?php

declare(strict_types=1);

use App\Data\Media\MediaUpdateData;
use App\Data\Media\MediaUploadData;
use App\Models\Media;
use App\Models\User;
use App\Services\Media\MediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

/*
|--------------------------------------------------------------------------
| Upload
|--------------------------------------------------------------------------
*/

test('it creates media record after successful upload', function () {
    $user = User::factory()->create();

    $file = UploadedFile::fake()->image(
        'original-image.jpg',
        800,
        600,
    );

    $data = new MediaUploadData(
        file: $file,
        altText: 'Foto kegiatan',
        caption: 'Kegiatan Dinas Arpus',
        uploadedBy: $user->id,
    );

    $media = app(MediaService::class)->upload($data);

    expect($media)
        ->toBeInstanceOf(Media::class)
        ->and($media->exists)
        ->toBeTrue();

    expect($media->original_name)
        ->toBe('original-image.jpg');

    expect($media->uploaded_by)
        ->toBe($user->id);

    expect($media->alt_text)
        ->toBe('Foto kegiatan');

    expect($media->caption)
        ->toBe('Kegiatan Dinas Arpus');

    Storage::disk('public')->assertExists(
        $media->storage_path,
    );
});

test('it generates unique storage filename', function () {
    $user = User::factory()->create();

    $file = UploadedFile::fake()->image(
        'original-image.jpg',
    );

    $data = new MediaUploadData(
        file: $file,
        uploadedBy: $user->id,
    );

    $media = app(MediaService::class)->upload($data);

    expect($media->file_name)
        ->not->toBe('original-image.jpg')
        ->toEndWith('.jpg');
});

test('it stores image dimensions in metadata', function () {
    $user = User::factory()->create();

    $file = UploadedFile::fake()->image(
        'photo.jpg',
        1200,
        800,
    );

    $data = new MediaUploadData(
        file: $file,
        uploadedBy: $user->id,
    );

    $media = app(MediaService::class)->upload($data);

    expect($media->metadata)
        ->toBeArray()
        ->toHaveKey('width', 1200)
        ->toHaveKey('height', 800);
});

test('it stores empty metadata for pdf', function () {
    $user = User::factory()->create();

    $file = UploadedFile::fake()->create(
        'document.pdf',
        100,
        'application/pdf',
    );

    $data = new MediaUploadData(
        file: $file,
        uploadedBy: $user->id,
    );

    $media = app(MediaService::class)->upload($data);

    expect($media->metadata)
        ->toBeArray()
        ->toBe([]);
});

test('it stores file information returned by storage service', function () {
    $user = User::factory()->create();

    $file = UploadedFile::fake()->image(
        'photo.png',
    );

    $data = new MediaUploadData(
        file: $file,
        uploadedBy: $user->id,
    );

    $media = app(MediaService::class)->upload($data);

    expect($media->disk)
        ->toBe('public');

    expect($media->path)
        ->toMatch('/^media\/\d{4}\/\d{2}$/');

    expect($media->extension)
        ->toBe('png');

    expect($media->mime_type)
        ->toBe('image/png');

    expect($media->size)
        ->toBeInt()
        ->toBeGreaterThan(0);
});

/*
|--------------------------------------------------------------------------
| Failure Handling
|--------------------------------------------------------------------------
*/

test('it deletes stored file when media database creation fails', function () {
    $user = User::factory()->create();

    $file = UploadedFile::fake()->image(
        'photo.jpg',
    );

    $data = new MediaUploadData(
        file: $file,
        uploadedBy: $user->id,
    );

    Media::creating(function (): void {
        throw new RuntimeException(
            'Simulated media persistence failure.',
        );
    });

    $service = app(MediaService::class);

    expect(fn() => $service->upload($data))
        ->toThrow(
            RuntimeException::class,
            'Simulated media persistence failure.',
        );

    expect(
        Media::query()->count(),
    )->toBe(0);

    expect(
        Storage::disk('public')->allFiles('media'),
    )->toBe([]);
});

test('it rethrows the original exception after cleaning up stored file', function () {
    $user = User::factory()->create();

    $file = UploadedFile::fake()->image(
        'photo.jpg',
    );

    $data = new MediaUploadData(
        file: $file,
        uploadedBy: $user->id,
    );

    Media::creating(function (): void {
        throw new RuntimeException(
            'Original persistence error.',
        );
    });

    expect(fn() => app(MediaService::class)->upload($data))
        ->toThrow(
            RuntimeException::class,
            'Original persistence error.',
        );

    expect(
        Storage::disk('public')->allFiles('media'),
    )->toBe([]);
});

test('it permanently deletes media and its stored file', function () {
    Storage::fake('public');

    $fileName = 'force-delete-file.jpg';
    $filePath = 'media/test/' . $fileName;

    Storage::disk('public')->put(
        $filePath,
        'fake image content',
    );

    $media = Media::factory()->create([
        'disk' => 'public',
        'path' => 'media/test',
        'file_name' => $fileName,
    ]);

    $media->delete();

    expect($media->trashed())
        ->toBeTrue();

    Storage::disk('public')->assertExists(
        $filePath,
    );

    app(MediaService::class)->forceDelete($media);

    expect(
        Media::withTrashed()
            ->whereKey($media->id)
            ->exists(),
    )->toBeFalse();

    Storage::disk('public')->assertMissing(
        $filePath,
    );
});

test('it permanently deletes the database record before deleting the stored file', function () {
    $fileName = 'force-delete-order.jpg';
    $filePath = 'media/test/' . $fileName;

    Storage::disk('public')->put(
        $filePath,
        'fake image content',
    );

    $media = Media::factory()->create([
        'disk' => 'public',
        'path' => 'media/test',
        'file_name' => $fileName,
    ]);

    $media->delete();

    app(MediaService::class)->forceDelete($media);

    expect(
        Media::withTrashed()
            ->whereKey($media->id)
            ->exists(),
    )->toBeFalse();

    Storage::disk('public')->assertMissing(
        $filePath,
    );
});

test('it updates media metadata', function () {
    $media = Media::factory()->create([
        'alt_text' => 'Alt text lama',
        'caption' => 'Caption lama',
    ]);

    $data = new MediaUpdateData(
        altText: 'Alt text baru',
        caption: 'Caption baru',
    );

    $service = app(MediaService::class);

    $updated = $service->update($media, $data);

    expect($updated->alt_text)
        ->toBe('Alt text baru')
        ->and($updated->caption)
        ->toBe('Caption baru');

    $this->assertDatabaseHas('media', [
        'id' => $media->id,
        'alt_text' => 'Alt text baru',
        'caption' => 'Caption baru',
    ]);
});

test('it can clear media metadata', function () {
    $media = Media::factory()->create([
        'alt_text' => 'Alt text lama',
        'caption' => 'Caption lama',
    ]);

    $data = new MediaUpdateData(
        altText: null,
        caption: null,
    );

    $service = app(MediaService::class);

    $updated = $service->update($media, $data);

    expect($updated->alt_text)
        ->toBeNull()
        ->and($updated->caption)
        ->toBeNull();

    $this->assertDatabaseHas('media', [
        'id' => $media->id,
        'alt_text' => null,
        'caption' => null,
    ]);
});

test('it updates only media metadata without changing file information', function () {
    $media = Media::factory()->create([
        'original_name' => 'dokumen-lama.pdf',
        'file_name' => 'media-file.pdf',
        'disk' => 'public',
        'path' => 'media/2026/09',
        'mime_type' => 'application/pdf',
        'extension' => 'pdf',
        'size' => 123456,
        'alt_text' => 'Alt text lama',
        'caption' => 'Caption lama',
    ]);

    $data = new MediaUpdateData(
        altText: 'Alt text baru',
        caption: 'Caption baru',
    );

    $service = app(MediaService::class);

    $updated = $service->update($media, $data);

    expect($updated->original_name)
        ->toBe('dokumen-lama.pdf')
        ->and($updated->file_name)
        ->toBe('media-file.pdf')
        ->and($updated->disk)
        ->toBe('public')
        ->and($updated->path)
        ->toBe('media/2026/09')
        ->and($updated->mime_type)
        ->toBe('application/pdf')
        ->and($updated->extension)
        ->toBe('pdf')
        ->and($updated->size)
        ->toBe(123456)
        ->and($updated->alt_text)
        ->toBe('Alt text baru')
        ->and($updated->caption)
        ->toBe('Caption baru');
});

test('it deletes all processed image files when media database creation fails', function () {
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

    $service = app(MediaService::class);

    expect(fn () => $service->upload(
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
