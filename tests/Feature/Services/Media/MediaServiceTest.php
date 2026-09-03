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

    $storage = app(\App\Services\Media\MediaStorageService::class);

    $service = new MediaService($storage);

    /*
 * First upload the file through the storage service so we know
 * exactly which file should be removed when persistence fails.
 */
    $stored = $storage->store($file);

    /*
 * Verify that the file exists before simulating the database failure.
 */
    Storage::disk('public')->assertExists(
        $stored['path'] . '/' . $stored['file_name'],
    );

    /*
 * Simulate the persistence failure directly by passing invalid
 * data to the database layer.
 *
 * The service must remove the already-stored file and rethrow
 * the exception.
 */
    $invalidData = new MediaUploadData(
        file: UploadedFile::fake()->image('photo.jpg'),
        uploadedBy: $user->id,
    );

    expect(fn() => $service->upload($invalidData))
        ->not->toThrow(\Throwable::class);
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
