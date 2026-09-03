<?php

declare(strict_types=1);

use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

/*
|--------------------------------------------------------------------------
| Index
|--------------------------------------------------------------------------
*/

test('guest cannot access media index', function () {
    $response = $this->get(
        route('admin.media.index'),
    );

    $response->assertRedirect(
        route('login'),
    );
});

test('user without media view permission cannot access media index', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.media.index'));

    $response->assertForbidden();
});

test('user with media view permission can access media index', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.view');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.media.index'));

    $response->assertOk();
});

/*
|--------------------------------------------------------------------------
| Store / Upload
|--------------------------------------------------------------------------
*/

test('user without media create permission cannot upload media', function () {
    $user = User::factory()->create();

    $file = UploadedFile::fake()->image(
        'photo.jpg',
    );

    $response = $this
        ->actingAs($user)
        ->post(
            route('admin.media.store'),
            [
                'file' => $file,
            ],
        );

    $response->assertForbidden();

    Storage::disk('public')->assertDirectoryEmpty(
        'media/' . now()->format('Y/m'),
    );
});

test('user with media create permission can upload media', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.create');

    $file = UploadedFile::fake()->image(
        'photo.jpg',
        800,
        600,
    );

    $response = $this
        ->actingAs($user)
        ->post(
            route('admin.media.store'),
            [
                'file' => $file,
                'alt_text' => 'Foto kegiatan',
                'caption' => 'Kegiatan Dinas Arpus',
            ],
        );

    $response->assertRedirect();

    $this->assertDatabaseHas('media', [
        'original_name' => 'photo.jpg',
        'alt_text' => 'Foto kegiatan',
        'caption' => 'Kegiatan Dinas Arpus',
        'uploaded_by' => $user->id,
    ]);

    $media = Media::query()
        ->where('original_name', 'photo.jpg')
        ->firstOrFail();

    Storage::disk('public')->assertExists(
        $media->storage_path,
    );
});

test('upload stores uploader from authenticated user', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.create');

    $file = UploadedFile::fake()->image(
        'photo.jpg',
    );

    $this
        ->actingAs($user)
        ->post(
            route('admin.media.store'),
            [
                'file' => $file,
            ],
        )
        ->assertRedirect();

    $media = Media::query()
        ->where('original_name', 'photo.jpg')
        ->firstOrFail();

    expect($media->uploaded_by)
        ->toBe($user->id);
});

/*
|--------------------------------------------------------------------------
| Show
|--------------------------------------------------------------------------
*/

test('user with media view permission can view media using uuid', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.view');

    $media = Media::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(
            route(
                'admin.media.show',
                $media->uuid,
            ),
        );

    $response->assertOk();
});

test('media show route does not use numeric id for model binding', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.view');

    $media = Media::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(
            route(
                'admin.media.show',
                $media->id,
            ),
        );

    $response->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Update
|--------------------------------------------------------------------------
*/

test('user without media update permission cannot update media', function () {
    $user = User::factory()->create();

    $media = Media::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(
            route(
                'admin.media.update',
                $media->uuid,
            ),
            [
                'alt_text' => 'Updated alt text',
                'caption' => 'Updated caption',
            ],
        );

    $response->assertForbidden();

    $media->refresh();

    expect($media->alt_text)
        ->not->toBe('Updated alt text');
});

test('user with media update permission can update media metadata', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.update');

    $media = Media::factory()->create([
        'alt_text' => 'Old alt text',
        'caption' => 'Old caption',
    ]);

    $response = $this
        ->actingAs($user)
        ->patch(
            route(
                'admin.media.update',
                $media->uuid,
            ),
            [
                'alt_text' => 'Updated alt text',
                'caption' => 'Updated caption',
            ],
        );

    $response->assertRedirect();

    $media->refresh();

    expect($media->alt_text)
        ->toBe('Updated alt text')
        ->and($media->caption)
        ->toBe('Updated caption');
});

/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

test('user without media delete permission cannot delete media', function () {
    $user = User::factory()->create();

    $media = Media::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(
            route(
                'admin.media.destroy',
                $media->uuid,
            ),
        );

    $response->assertForbidden();

    $media->refresh();

    expect($media->trashed())
        ->toBeFalse();
});

test('user with media delete permission can soft delete media', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.delete');

    $media = Media::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(
            route(
                'admin.media.destroy',
                $media->uuid,
            ),
        );

    $response->assertRedirect();

    $media->refresh();

    expect($media->trashed())
        ->toBeTrue();

    $this->assertSoftDeleted('media', [
        'id' => $media->id,
    ]);
});

test('soft deleting media keeps the physical file', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.delete');

    $fileName = 'soft-delete-file.jpg';
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

    Storage::disk('public')->assertExists(
        $media->storage_path,
    );

    $response = $this
        ->actingAs($user)
        ->delete(
            route(
                'admin.media.destroy',
                $media->uuid,
            ),
        );

    $response->assertRedirect();

    $media->refresh();

    expect($media->trashed())
        ->toBeTrue();

    Storage::disk('public')->assertExists(
        $filePath,
    );
});

/*
|--------------------------------------------------------------------------
| Restore
|--------------------------------------------------------------------------
*/

test('user without media restore permission cannot restore media', function () {
    $user = User::factory()->create();

    $media = Media::factory()->create();

    $media->delete();

    $response = $this
        ->actingAs($user)
        ->patch(
            route(
                'admin.media.restore',
                $media->uuid,
            ),
        );

    $response->assertForbidden();

    $media->refresh();

    expect($media->trashed())
        ->toBeTrue();
});

test('user with media restore permission can restore media', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.restore');

    $media = Media::factory()->create();

    $media->delete();

    expect($media->trashed())
        ->toBeTrue();

    $response = $this
        ->actingAs($user)
        ->patch(
            route(
                'admin.media.restore',
                $media->uuid,
            ),
        );

    $response->assertRedirect();

    $media->refresh();

    expect($media->trashed())
        ->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Force Delete
|--------------------------------------------------------------------------
*/

test('user without media force delete permission cannot permanently delete media', function () {
    $user = User::factory()->create();

    $media = Media::factory()->create();

    $media->delete();

    $response = $this
        ->actingAs($user)
        ->delete(
            route(
                'admin.media.force-delete',
                $media->uuid,
            ),
        );

    $response->assertForbidden();

    expect(
        Media::withTrashed()
            ->whereKey($media->id)
            ->exists(),
    )->toBeTrue();
});

test('user with media force delete permission can permanently delete media', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.force-delete');

    $media = Media::factory()->create();

    $media->delete();

    $response = $this
        ->actingAs($user)
        ->delete(
            route(
                'admin.media.force-delete',
                $media->uuid,
            ),
        );

    $response->assertRedirect();

    expect(
        Media::withTrashed()
            ->whereKey($media->id)
            ->exists(),
    )->toBeFalse();
});

test('force deleting media also deletes the physical file', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.force-delete');

    $fileName = 'physical-file.jpg';
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

    Storage::disk('public')->assertExists(
        $media->storage_path,
    );

    $media->delete();

    $response = $this
        ->actingAs($user)
        ->delete(
            route(
                'admin.media.force-delete',
                $media->uuid,
            ),
        );

    $response->assertRedirect();

    expect(
        Media::withTrashed()
            ->whereKey($media->id)
            ->exists(),
    )->toBeFalse();

    Storage::disk('public')->assertMissing(
        $filePath,
    );
});

test('restoring media keeps the physical file', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.restore');

    $fileName = 'restore-file.jpg';
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

    Storage::disk('public')->assertExists(
        $media->storage_path,
    );

    // Soft delete terlebih dahulu.
    $media->delete();

    expect($media->trashed())
        ->toBeTrue();

    Storage::disk('public')->assertExists(
        $filePath,
    );

    $response = $this
        ->actingAs($user)
        ->patch(
            route(
                'admin.media.restore',
                $media->uuid,
            ),
        );

    $response->assertRedirect();

    $media->refresh();

    expect($media->trashed())
        ->toBeFalse();

    Storage::disk('public')->assertExists(
        $filePath,
    );
});
