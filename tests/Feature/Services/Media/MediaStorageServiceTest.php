<?php

declare(strict_types=1);

use App\Services\Media\MediaStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('it stores uploaded media using a generated filename', function () {
    $file = UploadedFile::fake()->image(
        'original-image.jpg',
    );

    $service = app(MediaStorageService::class);

    $stored = $service->store($file);

    expect($stored)
        ->toHaveKey('disk', 'public')
        ->toHaveKey('path')
        ->toHaveKey('file_name')
        ->toHaveKey('mime_type')
        ->toHaveKey('extension', 'jpg')
        ->toHaveKey('size');

    expect($stored['file_name'])
        ->not->toBe('original-image.jpg')
        ->toEndWith('.jpg');

    Storage::disk('public')->assertExists(
        $stored['path']
            . '/'
            . $stored['file_name'],
    );
});

test('it stores media in year and month directories', function () {
    $file = UploadedFile::fake()->image(
        'photo.png',
    );

    $service = app(MediaStorageService::class);

    $stored = $service->store($file);

    expect($stored['path'])
        ->toBe(
            'media/' . now()->format('Y/m'),
        );
});

test('it can delete stored media', function () {
    $file = UploadedFile::fake()->image(
        'photo.jpg',
    );

    $service = app(MediaStorageService::class);

    $stored = $service->store($file);

    $result = $service->delete(
        $stored['disk'],
        $stored['path'],
        $stored['file_name'],
    );

    expect($result)->toBeTrue();

    Storage::disk('public')->assertMissing(
        $stored['path']
            . '/'
            . $stored['file_name'],
    );
});
