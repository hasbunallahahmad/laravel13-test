<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;
use InvalidArgumentException;

class ImageProcessingService
{
    private const MAX_SOURCE_WIDTH = 4000;

    private const MAX_SOURCE_HEIGHT = 4000;

    private const THUMBNAIL_MAX_SIZE = 400;

    private const WEBP_QUALITY = 82;

    private const THUMBNAIL_QUALITY = 80;

    public function __construct(
        private readonly ImageManager $manager = new ImageManager(
            new Driver(),
        ),
    ) {
    }

    /**
     * Process an uploaded image and create optimized derivatives.
     *
     * @return array{
     *     original_path: string,
     *     webp_path: string|null,
     *     thumbnail_path: string,
     *     width: int,
     *     height: int,
     *     mime_type: string
     * }
     */
    public function process(
        UploadedFile $file,
        string $directory,
        string $filename,
    ): array {
        $mimeType = $this->detectMimeType($file);

        $image = $this->manager->decodeSplFileInfo($file);

        $width = $image->width();
        $height = $image->height();

        $this->validateDimensions($width, $height);

        $extension = $this->extensionForMimeType($mimeType);

        $originalPath = $this->storeOriginal(
            file: $file,
            directory: $directory,
            filename: $filename,
            extension: $extension,
        );

        $webpPath = null;

        if ($mimeType !== 'image/webp') {
            $webpPath = $this->buildWebp(
                image: $image,
                directory: $directory,
                filename: $filename,
            );
        }

        $thumbnailPath = $this->buildThumbnail(
            image: $image,
            directory: $directory,
            filename: $filename,
        );

        return [
            'original_path' => $originalPath,
            'webp_path' => $webpPath,
            'thumbnail_path' => $thumbnailPath,
            'width' => $width,
            'height' => $height,
            'mime_type' => $mimeType,
        ];
    }

    private function detectMimeType(UploadedFile $file): string
    {
        $mimeType = $file->getMimeType();

        if (! in_array($mimeType, [
            'image/jpeg',
            'image/png',
            'image/webp',
        ], true)) {
            throw new InvalidArgumentException(
                'Unsupported image type.',
            );
        }

        return $mimeType;
    }

    private function validateDimensions(
        int $width,
        int $height,
    ): void {
        if (
            $width > self::MAX_SOURCE_WIDTH
            || $height > self::MAX_SOURCE_HEIGHT
        ) {
            throw new InvalidArgumentException(
                'Image dimensions exceed the maximum allowed size.',
            );
        }
    }

    private function storeOriginal(
        UploadedFile $file,
        string $directory,
        string $filename,
        string $extension,
    ): string {
        $path = trim($directory, '/')
            .'/'
            .$filename
            .'.'
            .$extension;

        Storage::disk('public')->put(
            $path,
            file_get_contents($file->getRealPath()),
        );

        return $path;
    }

    private function buildWebp(
        object $image,
        string $directory,
        string $filename,
    ): string {
        $encoded = $image->encodeUsingFormat(
            Format::WEBP,
            quality: self::WEBP_QUALITY,
        );

        $path = trim($directory, '/')
            .'/'
            .$filename
            .'.webp';

        Storage::disk('public')->put(
            $path,
            (string) $encoded,
        );

        return $path;
    }

    private function buildThumbnail(
        object $image,
        string $directory,
        string $filename,
    ): string {
        $thumbnail = clone $image;

        $thumbnail->scaleDown(
            width: self::THUMBNAIL_MAX_SIZE,
            height: self::THUMBNAIL_MAX_SIZE,
        );

        $encoded = $thumbnail->encodeUsingFormat(
            Format::WEBP,
            quality: self::THUMBNAIL_QUALITY,
        );

        $path = trim($directory, '/')
            .'/'
            .$filename
            .'-thumb.webp';

        Storage::disk('public')->put(
            $path,
            (string) $encoded,
        );

        return $path;
    }

    private function extensionForMimeType(string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw new InvalidArgumentException(
                'Unsupported image type.',
            ),
        };
    }
}