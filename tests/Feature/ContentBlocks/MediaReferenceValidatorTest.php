<?php

declare(strict_types=1);

use App\ContentBlocks\Validation\MediaReferenceValidator;
use App\Models\Media;
use InvalidArgumentException;
use Illuminate\Support\Str;

it('accepts an existing active media UUID', function (): void {
    $media = Media::factory()->create();

    $validator = new MediaReferenceValidator();

    $validator->validate($media->uuid);

    expect(true)->toBeTrue();
});

it('rejects a non existing media UUID', function (): void {
    $validator = new MediaReferenceValidator();

    expect(
        fn() => $validator->validate((string) Str::uuid())
    )->toThrow(InvalidArgumentException::class);
});

it('rejects a soft deleted media UUID', function (): void {
    $media = Media::factory()->create();

    $media->delete();

    expect($media->trashed())->toBeTrue();

    $validator = new MediaReferenceValidator();

    expect(
        fn() => $validator->validate($media->uuid)
    )->toThrow(InvalidArgumentException::class);
});

it('accepts multiple existing active media UUIDs', function (): void {
    $mediaOne = Media::factory()->create();
    $mediaTwo = Media::factory()->create();

    $validator = new MediaReferenceValidator();

    $validator->validateMany([
        $mediaOne->uuid,
        $mediaTwo->uuid,
    ]);

    expect(true)->toBeTrue();
});

it('rejects when one media UUID does not exist', function (): void {
    $media = Media::factory()->create();

    $validator = new MediaReferenceValidator();

    expect(
        fn() => $validator->validateMany([
            $media->uuid,
            (string) Str::uuid(),
        ])
    )->toThrow(InvalidArgumentException::class);
});

it('rejects when one media UUID has been soft deleted', function (): void {
    $mediaOne = Media::factory()->create();
    $mediaTwo = Media::factory()->create();

    $mediaTwo->delete();

    $validator = new MediaReferenceValidator();

    expect(
        fn() => $validator->validateMany([
            $mediaOne->uuid,
            $mediaTwo->uuid,
        ])
    )->toThrow(InvalidArgumentException::class);
});

it('accepts an empty collection of media references', function (): void {
    $validator = new MediaReferenceValidator();

    $validator->validateMany([]);

    expect(true)->toBeTrue();
});

it('does not query soft deleted media as active references', function (): void {
    $media = Media::factory()->create();

    $media->delete();

    expect(
        Media::query()
            ->where('uuid', $media->uuid)
            ->exists()
    )->toBeFalse();

    $validator = new MediaReferenceValidator();

    expect(
        fn() => $validator->validate($media->uuid)
    )->toThrow(InvalidArgumentException::class);
});
