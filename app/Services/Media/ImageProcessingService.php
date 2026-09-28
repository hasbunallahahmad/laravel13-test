<?php

declare(strict_types=1);

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class ImageProcessingService
{
    private const MAX_SOURCE_WIDTH = 4000;

    private const MAX_SOURCE_HEIGHT = 4000;

    private const THUMBNAIL_MAX_SIZE = 400;

    private const WEBP_QUALITY = 82;

    private const THUMBNAIL_QUALITY = 80;

    private const STORAGE_DISK = 'public';

    public function __construct(
        private readonly ImageManager $manager = new ImageManager(
            new Driver(),
        ),
    ) {}

    /**
     * Process an uploaded image and create optimized derivatives.
     *
     * If any derivative fails after one or more files have been written,
     * all files created during this processing attempt are removed.
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

        $storedPaths = [];

        try {
            $originalPath = $this->storeOriginal(
                file: $file,
                directory: $directory,
                filename: $filename,
                extension: $this->extensionForMimeType($mimeType),
            );

            $storedPaths[] = $originalPath;

            $webpPath = null;

            if ($this->shouldCreateWebp($mimeType)) {
                $webpPath = $this->buildWebp(
                    image: $image,
                    directory: $directory,
                    filename: $filename,
                );

                $storedPaths[] = $webpPath;
            }

            $thumbnailPath = $this->buildThumbnail(
                image: $image,
                directory: $directory,
                filename: $filename,
            );

            $storedPaths[] = $thumbnailPath;

            return [
                'original_path' => $originalPath,
                'webp_path' => $webpPath,
                'thumbnail_path' => $thumbnailPath,
                'width' => $width,
                'height' => $height,
                'mime_type' => $mimeType,
            ];
        } catch (Throwable $exception) {
            $this->cleanupPaths($storedPaths);

            throw $exception;
        }
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

    private function shouldCreateWebp(string $mimeType): bool
    {
        return $mimeType !== 'image/webp';
    }

    private function storeOriginal(
        UploadedFile $file,
        string $directory,
        string $filename,
        string $extension,
    ): string {
        $path = $this->buildPath(
            directory: $directory,
            filename: $filename,
            suffix: '',
            extension: $extension,
        );

        $contents = file_get_contents($file->getRealPath());

        if ($contents === false) {
            throw new InvalidArgumentException(
                'Unable to read uploaded image.',
            );
        }

        $stored = Storage::disk(self::STORAGE_DISK)->put(
            $path,
            $contents,
        );

        if ($stored === false) {
            throw new RuntimeException(
                'Unable to store original image.',
            );
        }

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

        $path = $this->buildPath(
            directory: $directory,
            filename: $filename,
            suffix: '',
            extension: 'webp',
        );

        $stored = Storage::disk(self::STORAGE_DISK)->put(
            $path,
            (string) $encoded,
        );

        if ($stored === false) {
            throw new RuntimeException(
                'Unable to store WebP image.',
            );
        }

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

        $path = $this->buildPath(
            directory: $directory,
            filename: $filename,
            suffix: '-thumb',
            extension: 'webp',
        );

        $stored = Storage::disk(self::STORAGE_DISK)->put(
            $path,
            (string) $encoded,
        );

        if ($stored === false) {
            throw new RuntimeException(
                'Unable to store thumbnail image.',
            );
        }

        return $path;
    }

    /**
     * @param list<string> $paths
     */
    private function cleanupPaths(array $paths): void
    {
        if ($paths === []) {
            return;
        }

        Storage::disk(self::STORAGE_DISK)->delete($paths);
    }

    private function buildPath(
        string $directory,
        string $filename,
        string $suffix,
        string $extension,
    ): string {
        return trim($directory, '/')
            . '/'
            . $filename
            . $suffix
            . '.'
            . $extension;
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
