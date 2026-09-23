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


test('upload requires a file', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.create');

    $response = $this
        ->actingAs($user)
        ->post(
            route('admin.media.store'),
            [],
        );

    $response
        ->assertSessionHasErrors('file');

    expect(Media::query()->count())
        ->toBe(0);
});

test('upload rejects executable files', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.create');

    $file = UploadedFile::fake()->create(
        'malicious.php',
        10,
        'application/x-php',
    );

    $response = $this
        ->actingAs($user)
        ->post(
            route('admin.media.store'),
            [
                'file' => $file,
            ],
        );

    $response
        ->assertSessionHasErrors('file');

    expect(Media::query()->count())
        ->toBe(0);

    Storage::disk('public')->assertDirectoryEmpty(
        'media/' . now()->format('Y/m'),
    );
});

test('upload rejects unsupported file types', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.create');

    $file = UploadedFile::fake()->create(
        'script.exe',
        10,
        'application/octet-stream',
    );

    $response = $this
        ->actingAs($user)
        ->post(
            route('admin.media.store'),
            [
                'file' => $file,
            ],
        );

    $response
        ->assertSessionHasErrors('file');

    expect(Media::query()->count())
        ->toBe(0);
});

test('uploaded media uses a generated uuid filename instead of the original filename', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $user->givePermissionTo('media.create');

    $file = UploadedFile::fake()->image('my-photo.jpg');

    $this->actingAs($user)
        ->post(route('admin.media.store'), [
            'file' => $file,
        ])
        ->assertRedirect(route('admin.media.index'));

    $media = Media::query()->firstOrFail();

    expect($media->original_name)->toBe('my-photo.jpg')
        ->and($media->file_name)->not->toBe('my-photo.jpg')
        ->and($media->file_name)->toMatch(
            '/^[0-9a-f-]{36}\.jpg$/i'
        );
});

test('uploading the same original filename twice creates different stored filenames', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $user->givePermissionTo('media.create');

    $fileOne = UploadedFile::fake()->image('same-name.jpg');
    $fileTwo = UploadedFile::fake()->image('same-name.jpg');

    $this->actingAs($user)
        ->post(route('admin.media.store'), ['file' => $fileOne])
        ->assertRedirect();

    $this->actingAs($user)
        ->post(route('admin.media.store'), ['file' => $fileTwo])
        ->assertRedirect();

    $media = Media::query()
        ->orderBy('id')
        ->get();

    expect($media)->toHaveCount(2)
        ->and($media[0]->file_name)
        ->not->toBe($media[1]->file_name)
        ->and($media[0]->original_name)
        ->toBe('same-name.jpg')
        ->and($media[1]->original_name)
        ->toBe('same-name.jpg');
});

test('uploaded media path is controlled by the application', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $user->givePermissionTo('media.create');

    $file = UploadedFile::fake()->image(
        '../../../../../evil.jpg'
    );

    $this->actingAs($user)
        ->post(route('admin.media.store'), [
            'file' => $file,
        ])
        ->assertRedirect();

    $media = Media::query()->firstOrFail();

    expect($media->path)
        ->toMatch('/^media\/\d{4}\/\d{2}$/')
        ->and($media->file_name)
        ->not->toContain('..')
        ->and($media->file_name)
        ->not->toContain('/');
});

// test('upload rejects a file whose content does not match its image extension', function () {
//     $user = User::factory()->create();

//     $user->givePermissionTo('media.create');

//     $file = UploadedFile::fake()->createWithContent(
//         'fake-image.jpg',
//         'this is not a real jpeg image',
//     );

//     $response = $this
//         ->actingAs($user)
//         ->post(
//             route('admin.media.store'),
//             [
//                 'file' => $file,
//             ],
//         );

//     $response->assertSessionHasErrors('file');

//     expect(Media::query()->count())->toBe(0);
// });
// test('upload rejects a file with spoofed image mime type', function () {
//     $user = User::factory()->create();

//     $user->givePermissionTo('media.create');

//     $file = UploadedFile::fake()->create(
//         'fake-image.jpg',
//         10,
//         'image/jpeg',
//     );

//     $response = $this
//         ->actingAs($user)
//         ->post(
//             route('admin.media.store'),
//             [
//                 'file' => $file,
//             ],
//         );

//     $response->assertSessionHasErrors('file');

//     expect(Media::query()->count())->toBe(0);
// });

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

test('media show displays complete media information', function () {
    $user = User::factory()->create([
        'name' => 'Admin Media',
    ]);

    $user->givePermissionTo('media.view');

    $media = Media::factory()->create([
        'original_name' => 'foto-kegiatan.jpg',
        'file_name' => '550e8400-e29b-41d4-a716-446655440000.jpg',
        'disk' => 'public',
        'path' => 'media/2026/09',
        'mime_type' => 'image/jpeg',
        'extension' => 'jpg',
        'size' => 204800,
        'metadata' => [
            'width' => 1200,
            'height' => 800,
        ],
        'alt_text' => 'Foto kegiatan Dinas Arpus',
        'caption' => 'Dokumentasi kegiatan Dinas Arsip dan Perpustakaan Kota Semarang.',
        'uploaded_by' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(
            route(
                'admin.media.show',
                $media->uuid,
            ),
        );

    $response
        ->assertOk()
        ->assertSee('foto-kegiatan.jpg')
        ->assertSee($media->uuid)
        ->assertSee('550e8400-e29b-41d4-a716-446655440000.jpg')
        ->assertSee('image/jpeg')
        ->assertSee('jpg')
        ->assertSee('204800')
        ->assertSee('media/2026/09')
        ->assertSee('Admin Media')
        ->assertSee('1200')
        ->assertSee('800')
        ->assertSee('Foto kegiatan Dinas Arpus')
        ->assertSee('Dokumentasi kegiatan Dinas Arsip dan Perpustakaan Kota Semarang.');
});

test('media show handles media without metadata', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.view');

    $media = Media::factory()->create([
        'metadata' => [],
        'alt_text' => null,
        'caption' => null,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(
            route(
                'admin.media.show',
                $media->uuid,
            ),
        );

    $response
        ->assertOk()
        ->assertSee($media->original_name);
});

test('media show displays image preview for image media', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('media.view');

    $media = Media::factory()->create([
        'mime_type' => 'image/jpeg',
        'extension' => 'jpg',
        'disk' => 'public',
        'path' => 'media/2026/09',
        'file_name' => '550e8400-e29b-41d4-a716-446655440000.jpg',
    ]);

    $response = $this->actingAs($user)->get(
        route('admin.media.show', $media->uuid),
    );

    $response
        ->assertOk()
        ->assertSee('<img', false)
        ->assertSee('550e8400-e29b-41d4-a716-446655440000.jpg', false);
});

test('media show does not display image preview for non-image media', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('media.view');

    $media = Media::factory()->create([
        'mime_type' => 'application/pdf',
        'extension' => 'pdf',
        'disk' => 'public',
        'path' => 'media/2026/09',
        'file_name' => '550e8400-e29b-41d4-a716-446655440000.pdf',
    ]);

    $response = $this->actingAs($user)->get(
        route('admin.media.show', $media->uuid),
    );

    $response
        ->assertOk()
        ->assertDontSee('<img', false);
});

// test('media show uses the admin layout', function () {
//     $user = User::factory()->create();

//     $user->givePermissionTo('media.view');

//     $media = Media::factory()->create();

//     $response = $this
//         ->actingAs($user)
//         ->get(
//             route(
//                 'admin.media.show',
//                 $media->uuid,
//             ),
//         );

//     $response
//         ->assertOk()
//         ->assertSee('Dashboard')
//         ->assertSee('Media');
// });

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

test('user with media view permission can access media edit form', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('media.view');

    $media = Media::factory()->create([
        'alt_text' => 'Alt lama',
        'caption' => 'Caption lama',
    ]);

    $response = $this->actingAs($user)->get(
        route('admin.media.edit', $media->uuid),
    );

    $response
        ->assertOk()
        ->assertSee('Alt lama')
        ->assertSee('Caption lama')
        ->assertSee($media->uuid);
});

test('user without media view permission cannot access media edit form', function () {
    $user = User::factory()->create();

    $media = Media::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.media.edit', $media->uuid))
        ->assertForbidden();
});

test('updating media metadata does not change stored file information', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('media.update');

    $media = Media::factory()->create([
        'original_name' => 'foto-asli.jpg',
        'file_name' => '550e8400-e29b-41d4-a716-446655440000.jpg',
        'disk' => 'public',
        'path' => 'media/2026/09',
        'mime_type' => 'image/jpeg',
        'extension' => 'jpg',
        'size' => 204800,
        'metadata' => [
            'width' => 1200,
            'height' => 800,
            'variants' => [
                'webp' => 'media/2026/09/550e8400-e29b-41d4-a716-446655440000.webp',
                'thumbnail' => 'media/2026/09/550e8400-e29b-41d4-a716-446655440000-thumb.webp',
            ],
        ],
    ]);

    $this->actingAs($user)
        ->patch(route('admin.media.update', $media->uuid), [
            'alt_text' => 'Foto terbaru',
            'caption' => 'Caption terbaru',
        ])
        ->assertRedirect(route('admin.media.show', $media->uuid));

    $media->refresh();

    expect($media->alt_text)->toBe('Foto terbaru')
        ->and($media->caption)->toBe('Caption terbaru')
        ->and($media->original_name)->toBe('foto-asli.jpg')
        ->and($media->file_name)->toBe('550e8400-e29b-41d4-a716-446655440000.jpg')
        ->and($media->disk)->toBe('public')
        ->and($media->path)->toBe('media/2026/09')
        ->and($media->mime_type)->toBe('image/jpeg')
        ->and($media->extension)->toBe('jpg')
        ->and($media->size)->toBe(204800)
        ->and($media->metadata)->toBe([
            'width' => 1200,
            'height' => 800,
            'variants' => [
                'webp' => 'media/2026/09/550e8400-e29b-41d4-a716-446655440000.webp',
                'thumbnail' => 'media/2026/09/550e8400-e29b-41d4-a716-446655440000-thumb.webp',
            ],
        ]);
});

test('user with media update permission can access media edit form', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('media.update');

    $media = Media::factory()->create([
        'alt_text' => 'Alt lama',
        'caption' => 'Caption lama',
    ]);

    $response = $this->actingAs($user)->get(
        route('admin.media.edit', $media->uuid),
    );

    $response
        ->assertOk()
        ->assertSee('Alt lama')
        ->assertSee('Caption lama')
        ->assertSee($media->uuid);
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

/*
|--------------------------------------------------------------------------
| Index - Listing
|--------------------------------------------------------------------------
*/

test('media index displays uploaded media', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.view');

    Media::factory()->create([
        'original_name' => 'foto-kegiatan.jpg',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.media.index'));

    $response
        ->assertOk()
        ->assertSee('foto-kegiatan.jpg');
});

test('media index displays upload form for users with media create permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'media.view',
        'media.create',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.media.index'));

    $response
        ->assertOk()
        ->assertSee(
            '<form method="POST" action="' . route('admin.media.store') . '"',
            false,
        )
        ->assertSee('enctype="multipart/form-data"', false)
        ->assertSee('data-media-upload-form', false)
        ->assertSee('name="file"', false)
        ->assertSee('name="alt_text"', false)
        ->assertSee('name="caption"', false);
});

test('media index hides upload form for users without media create permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.view');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.media.index'));

    $response
        ->assertOk()
        ->assertDontSee(
            '<form method="POST" action="' . route('admin.media.store') . '"',
            false,
        )
        ->assertDontSee('data-media-upload-form', false)
        ->assertDontSee('name="file"', false)
        ->assertDontSee('name="alt_text"', false)
        ->assertDontSee('name="caption"', false);
});

test('media index uses thumbnail variant for image preview', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.view');

    $media = Media::factory()->create([
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'path' => 'media/2026/09',
        'file_name' => 'original.jpg',
        'original_name' => 'foto-kegiatan.jpg',
        'metadata' => [
            'width' => 1600,
            'height' => 1200,
            'variants' => [
                'webp' => 'media/2026/09/original.webp',
                'thumbnail' => 'media/2026/09/original-thumb.webp',
            ],
        ],
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.media.index'));

    $response
        ->assertOk()
        ->assertSee(
            Storage::disk('public')->url(
                'media/2026/09/original-thumb.webp',
            ),
            false,
        )
        ->assertDontSee(
            Storage::disk('public')->url(
                'media/2026/09/original.jpg',
            ),
            false,
        );
});

test('media index does not display soft deleted media', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.view');

    Media::factory()->create([
        'original_name' => 'media-aktif.jpg',
    ]);

    $deletedMedia = Media::factory()->create([
        'original_name' => 'media-terhapus.jpg',
    ]);

    $deletedMedia->delete();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.media.index'));

    $response
        ->assertOk()
        ->assertSee('media-aktif.jpg')
        ->assertDontSee('media-terhapus.jpg');
});

test('media index displays media metadata', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.view');

    Media::factory()->create([
        'original_name' => 'dokumen.pdf',
        'mime_type' => 'application/pdf',
        'extension' => 'pdf',
        'size' => 204800,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.media.index'));

    $response
        ->assertOk()
        ->assertSee('dokumen.pdf')
        ->assertSee('application/pdf')
        ->assertSee('pdf');
});

test('media index uses pagination', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.view');

    Media::factory()
        ->count(25)
        ->create();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.media.index'));

    $response
        ->assertOk()
        ->assertViewHas('media', function ($media): bool {
            return $media->perPage() === 24;
        });
});

test('media index displays uploader name', function () {
    $user = User::factory()->create([
        'name' => 'Uploader Test',
    ]);

    $user->givePermissionTo('media.view');

    Media::factory()->create([
        'uploaded_by' => $user->id,
        'original_name' => 'foto-uploader.jpg',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.media.index'));

    $response
        ->assertOk()
        ->assertSee('foto-uploader.jpg')
        ->assertSee('Uploader Test');
});

test('user with media view permission can preview pdf media', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $user->givePermissionTo('media.view');

    $media = Media::factory()->create([
        'mime_type' => 'application/pdf',
        'extension' => 'pdf',
        'disk' => 'public',
        'path' => 'media/2026/09',
        'file_name' => 'document-test.pdf',
    ]);

    Storage::disk('public')->put(
        'media/2026/09/document-test.pdf',
        '%PDF-1.4 test pdf content',
    );

    $response = $this->actingAs($user)->get(
        route('admin.media.preview', $media->uuid),
    );

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});

test('user without media view permission cannot preview pdf media', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $media = Media::factory()->create([
        'mime_type' => 'application/pdf',
        'extension' => 'pdf',
        'disk' => 'public',
        'path' => 'media/2026/09',
        'file_name' => 'document-test.pdf',
    ]);

    Storage::disk('public')->put(
        'media/2026/09/document-test.pdf',
        '%PDF-1.4 test pdf content',
    );

    $this->actingAs($user)
        ->get(route('admin.media.preview', $media->uuid))
        ->assertForbidden();
});

test('image media cannot be served through pdf preview endpoint', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $user->givePermissionTo('media.view');

    $media = Media::factory()->create([
        'mime_type' => 'image/jpeg',
        'extension' => 'jpg',
        'disk' => 'public',
        'path' => 'media/2026/09',
        'file_name' => 'photo.jpg',
    ]);

    Storage::disk('public')->put(
        'media/2026/09/photo.jpg',
        'fake image content',
    );

    $this->actingAs($user)
        ->get(route('admin.media.preview', $media->uuid))
        ->assertNotFound();
});


/*
|--------------------------------------------------------------------------
| Trash
|--------------------------------------------------------------------------
*/

test('guest cannot access media trash', function () {
    $response = $this->get(
        route('admin.media.trash'),
    );

    $response->assertRedirect(
        route('login'),
    );
});

test('user without media view permission cannot access media trash', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.media.trash'));

    $response->assertForbidden();
});

test('user with media view permission can access media trash', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('media.view');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.media.trash'));

    $response->assertOk();
});

test('media trash displays only soft deleted media', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('media.view');

    Media::factory()->create([
        'original_name' => 'media-aktif.jpg',
    ]);

    $deletedMedia = Media::factory()->create([
        'original_name' => 'media-terhapus.jpg',
    ]);

    $deletedMedia->delete();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.media.trash'));

    $response
        ->assertOk()
        ->assertSee('media-terhapus.jpg')
        ->assertDontSee('media-aktif.jpg');
});

test('media trash uses pagination', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('media.view');

    Media::factory()
        ->count(25)
        ->create()
        ->each(function (Media $media): void {
            $media->delete();
        });

    $response = $this
        ->actingAs($user)
        ->get(route('admin.media.trash'));

    $response
        ->assertOk()
        ->assertViewHas('media', function ($media): bool {
            return $media->perPage() === 24;
        });
});

/*
|--------------------------------------------------------------------------
| Trash - Restore & Force Delete
|--------------------------------------------------------------------------
*/

test('media trash restore requires media restore permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('media.view');

    $media = Media::factory()->create();
    $media->delete();

    $response = $this
        ->actingAs($user)
        ->patch(
            route('admin.media.restore', $media),
        );

    $response->assertForbidden();

    expect(
        Media::withTrashed()->find($media->id)->trashed()
    )->toBeTrue();
});

test('user with media restore permission can restore media from trash', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('media.restore');

    $media = Media::factory()->create();
    $media->delete();

    $response = $this
        ->actingAs($user)
        ->patch(
            route('admin.media.restore', $media),
        );

    $response
        ->assertRedirect(route('admin.media.index'))
        ->assertSessionHas(
            'success',
            'Media berhasil dipulihkan.',
        );

    expect(
        Media::find($media->id)
    )->not->toBeNull();
});

test('media trash force delete requires media force delete permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('media.view');

    $media = Media::factory()->create();
    $media->delete();

    $response = $this
        ->actingAs($user)
        ->delete(
            route('admin.media.force-delete', $media),
        );

    $response->assertForbidden();

    expect(
        Media::withTrashed()->find($media->id)
    )->not->toBeNull();
});

test('user with media force delete permission can permanently delete media from trash', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('media.force-delete');

    $media = Media::factory()->create();
    $media->delete();

    $response = $this
        ->actingAs($user)
        ->delete(
            route('admin.media.force-delete', $media),
        );

    $response
        ->assertRedirect(route('admin.media.index'))
        ->assertSessionHas(
            'success',
            'Media berhasil dihapus permanen.',
        );

    expect(
        Media::withTrashed()->find($media->id)
    )->toBeNull();
});

test('media trash displays restore and permanent delete actions', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'media.view',
        'media.restore',
        'media.force-delete',
    ]);

    $media = Media::factory()->create([
        'original_name' => 'media-terhapus.jpg',
    ]);

    $media->delete();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.media.trash'));

    $response
        ->assertOk()
        ->assertSee('Pulihkan')
        ->assertSee('Hapus Permanen');
});

test('media trash links restore and permanent delete actions to the correct media', function () {
    $user = User::factory()->create();

    $user->givePermissionTo([
        'media.view',
        'media.restore',
        'media.force-delete',
    ]);

    $media = Media::factory()->create([
        'original_name' => 'media-terhapus.jpg',
    ]);

    $media->delete();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.media.trash'));

    $response
        ->assertOk()
        ->assertSee(
            route('admin.media.restore', $media),
            false,
        )
        ->assertSee(
            route('admin.media.force-delete', $media),
            false,
        );
});

test('media trash hides restore action without restore permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.view');

    $media = Media::factory()->create([
        'original_name' => 'media-terhapus.jpg',
    ]);

    $media->delete();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.media.trash'));

    $response
        ->assertOk()
        ->assertDontSee('Pulihkan')
        ->assertDontSee(
            route('admin.media.restore', $media),
            false,
        );
});

test('media trash hides permanent delete action without force delete permission', function () {
    $user = User::factory()->create();

    $user->givePermissionTo('media.view');

    $media = Media::factory()->create([
        'original_name' => 'media-terhapus.jpg',
    ]);

    $media->delete();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.media.trash'));

    $response
        ->assertOk()
        ->assertDontSee('Hapus Permanen')
        ->assertDontSee(
            route('admin.media.force-delete', $media),
            false,
        );
});

test('media library displays a link to media trash', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('media.view');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.media.index'));

    $response
        ->assertOk()
        ->assertSee('Media Sampah')
        ->assertSee(
            route('admin.media.trash'),
            false,
        );
});
