<?php

declare(strict_types=1);

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('media automatically receives a uuid', function () {
    $media = Media::factory()->create([
        'uuid' => null,
    ]);

    expect($media->uuid)
        ->not->toBeEmpty()
        ->toBeString();
});

test('media uses uuid for route model binding', function () {
    $media = new Media();

    expect($media->getRouteKeyName())
        ->toBe('uuid');
});

test('media casts metadata to array', function () {
    $media = Media::factory()->create([
        'metadata' => [
            'width' => 1920,
            'height' => 1080,
        ],
    ]);

    $media->refresh();

    expect($media->metadata)
        ->toBeArray()
        ->toHaveKey('width', 1920)
        ->toHaveKey('height', 1080);
});

test('media casts size to integer', function () {
    $media = Media::factory()->create([
        'size' => 123456,
    ]);

    $media->refresh();

    expect($media->size)
        ->toBeInt()
        ->toBe(123456);
});

test('media belongs to an uploader', function () {
    $user = User::factory()->create();

    $media = Media::factory()->create([
        'uploaded_by' => $user->id,
    ]);

    expect($media->uploader)
        ->toBeInstanceOf(User::class)
        ->and($media->uploader->is($user))
        ->toBeTrue();
});

test('media supports soft deletes', function () {
    $media = Media::factory()->create();

    $media->delete();

    expect(
        Media::query()->find($media->id)
    )->toBeNull();

    expect(
        Media::withTrashed()->find($media->id)
    )->not->toBeNull();
});

test('media generates the correct storage path', function () {
    $media = Media::factory()->create([
        'path' => 'media/2026/09',
        'file_name' => 'example.jpg',
    ]);

    expect($media->storage_path)
        ->toBe('media/2026/09/example.jpg');
});
