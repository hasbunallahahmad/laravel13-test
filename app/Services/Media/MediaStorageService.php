<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Data\Media\MediaUploadData;
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
     * Returns storage metadata.
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
    public function store(
        UploadedFile $file,
    ): array {
        $extension = strtolower(
            $file->extension(),
        );

        $fileName = $this->generateFileName($extension);

        $directory = 'media/'
            . now()->format('Y/m');

        Storage::disk(self::DISK)->putFileAs(
            $directory,
            $file,
            $fileName,
        );

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
     * Delete a stored media file.
     */
    public function delete(
        string $disk,
        string $path,
        string $fileName,
    ): bool {
        $filePath = trim($path, '/')
            . '/'
            . $fileName;

        return Storage::disk($disk)->delete(
            $filePath,
        );
    }
}
