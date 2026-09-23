<?php

use App\Models\Media;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->permission = Permission::findOrCreate('media.view', 'web');
});

test('user with media view permission can access the media picker', function () {
    $user = User::factory()->create();
    $user->givePermissionTo($this->permission);

    $response = $this->actingAs($user)
        ->get(route('admin.media.picker'));

    $response->assertSuccessful();
    $response->assertViewIs('admin.media.picker');
});

test('media picker only displays image media', function () {
    $user = User::factory()->create();
    $user->givePermissionTo($this->permission);

    $image = Media::factory()->create([
        'mime_type' => 'image/jpeg',
    ]);

    $pdf = Media::factory()->create([
        'mime_type' => 'application/pdf',
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.media.picker'));

    $response->assertSuccessful();
    $response->assertViewHas('media', function ($media) use ($image, $pdf) {
        return $media->contains('id', $image->id)
            && ! $media->contains('id', $pdf->id);
    });
});

test('media picker excludes trashed media', function () {
    $user = User::factory()->create();
    $user->givePermissionTo($this->permission);

    $activeImage = Media::factory()->create([
        'mime_type' => 'image/png',
    ]);

    $trashedImage = Media::factory()->create([
        'mime_type' => 'image/png',
        'deleted_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.media.picker'));

    $response->assertSuccessful();
    $response->assertViewHas('media', function ($media) use ($activeImage, $trashedImage) {
        return $media->contains('id', $activeImage->id)
            && ! $media->contains('id', $trashedImage->id);
    });
});

test('user without media view permission cannot access the media picker', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('admin.media.picker'));

    $response->assertForbidden();
});

test('media picker exposes image metadata needed by the editor', function () {
    $user = User::factory()->create();
    $user->givePermissionTo($this->permission);

    $media = Media::factory()->create([
        'mime_type' => 'image/webp',
        'metadata' => [
            'width' => 1200,
            'height' => 800,
            'variants' => [
                'webp' => 'media/2026/09/example.webp',
                'thumbnail' => 'media/2026/09/example-thumb.webp',
            ],
        ],
        'alt_text' => 'Contoh gambar',
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.media.picker'));

    $response->assertSuccessful();

    $response->assertViewHas('media', function ($items) use ($media) {
        $item = $items->firstWhere('id', $media->id);

        return $item !== null
            && $item->uuid === $media->uuid
            && $item->alt_text === 'Contoh gambar'
            && data_get($item->metadata, 'width') === 1200
            && data_get($item->metadata, 'height') === 800;
    });
});
