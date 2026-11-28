<?php

declare(strict_types=1);

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class MediaStorageService
{
    /**
     * Default storage disk.
     */
    private const DISK = 'public';

    /**
     * Store uploaded media securely.
     *
     * @return array{
     *     disk: string,
     *     path: string,
     *     file_name: string,
     *     mime_type: string,
     *     extension: string,
     *     size: int
     * }
     */
    public function store(UploadedFile $file): array
    {
        $extension = strtolower($file->extension());

        $fileName = $this->generateFileName($extension);

        $directory = 'media/' . now()->format('Y/m');

        $stored = Storage::disk(self::DISK)->putFileAs(
            $directory,
            $file,
            $fileName,
        );

        if ($stored === false) {
            throw new \RuntimeException(
                'Failed to store uploaded media.',
            );
        }

        return [
            'disk' => self::DISK,
            'path' => $directory,
            'file_name' => $fileName,
            'mime_type' => $file->getMimeType()
                ?? 'application/octet-stream',
            'extension' => $extension,
            'size' => $file->getSize(),
        ];
    }

    private function generateFileName(string $extension): string
    {
        return Str::uuid() . '.' . $extension;
    }

    /**
     * Delete the original media file.
     */
    public function delete(
        string $disk,
        string $path,
        string $fileName,
    ): bool {
        [$normalizedPath] = $this->validateMediaLocation(
            $disk,
            $path,
            $fileName,
        );

        $filePath = $normalizedPath . '/' . $fileName;

        return Storage::disk(self::DISK)->delete($filePath);
    }

    /**
     * Delete a processed media variant.
     *
     * Only the expected WebP and thumbnail paths are allowed.
     */
    public function deleteVariant(
        string $disk,
        string $path,
        string $fileName,
        string $variantPath,
    ): bool {
        [$normalizedPath] = $this->validateMediaLocation(
            $disk,
            $path,
            $fileName,
        );

        $filenameWithoutExtension = pathinfo(
            $fileName,
            PATHINFO_FILENAME,
        );

        $allowedPaths = [
            $normalizedPath . '/'
                . $filenameWithoutExtension . '.webp',

            $normalizedPath . '/'
                . $filenameWithoutExtension . '-thumb.webp',
        ];

        $normalizedVariantPath = trim($variantPath, '/');

        if (! in_array($normalizedVariantPath, $allowedPaths, true)) {
            throw new \InvalidArgumentException(
                'Invalid media variant location.',
            );
        }

        return Storage::disk(self::DISK)->delete(
            $normalizedVariantPath,
        );
    }

    /**
     * Validate and normalize the media storage location.
     *
     * @return array{0: string}
     */
    private function validateMediaLocation(
        string $disk,
        string $path,
        string $fileName,
    ): array {
        $normalizedPath = trim($path, '/');

        if (
            $disk !== self::DISK
            || ! preg_match(
                '#\Amedia/\d{4}/(?:0[1-9]|1[0-2])\z#D',
                $normalizedPath,
            )
            || ! preg_match(
                '/\A[a-f0-9-]{36}\.[a-z0-9]+\z/i',
                $fileName,
            )
        ) {
            throw new \InvalidArgumentException(
                'Invalid media storage location.',
            );
        }

        return [$normalizedPath];
    }
}
