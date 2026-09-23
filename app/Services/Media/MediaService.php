<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Data\Media\MediaUpdateData;
use App\Data\Media\MediaUploadData;
use App\Models\Media;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;


final class MediaService
{
    public function __construct(
        private readonly MediaStorageService $storage,
        private readonly ImageProcessingService $imageProcessor,
    ) {}

    public function upload(MediaUploadData $data): Media
    {
        if ($this->isProcessableImage($data->file->getMimeType())) {
            return $this->uploadImage($data);
        }

        return $this->uploadNonImage($data);
    }

    public function update(Media $media, MediaUpdateData $data): Media
    {
        $media->update([
            'alt_text' => $data->altText,
            'caption' => $data->caption,
        ]);

        return $media->refresh();
    }

    public function forceDelete(Media $media): void
    {
        $variantPaths = $this->extractVariantPaths($media);

        $media->forceDelete();

        $this->storage->delete(
            $media->disk,
            $media->path,
            $media->file_name,
        );

        foreach ($variantPaths as $variantPath) {
            Storage::disk($media->disk)->delete($variantPath);
        }
    }


    /**
     * @return list<string>
     */
    private function extractVariantPaths(Media $media): array
    {
        $variants = $media->metadata['variants'] ?? [];

        if (! is_array($variants)) {
            return [];
        }

        return array_values(
            array_filter(
                $variants,
                static fn (mixed $path): bool =>
                    is_string($path) && $path !== '',
            ),
        );
    }

    private function uploadImage(MediaUploadData $data): Media
    {
        $directory = 'media/'.now()->format('Y/m');

        $filename = (string) Str::uuid();

        $processed = $this->imageProcessor->process(
            file: $data->file,
            directory: $directory,
            filename: $filename,
        );

        try {
            return DB::transaction(function () use (
                $data,
                $processed,
            ): Media {
                return Media::query()->create([
                    'original_name' => $data->file->getClientOriginalName(),
                    'file_name' => basename($processed['original_path']),
                    'disk' => 'public',
                    'path' => dirname($processed['original_path']),
                    'mime_type' => $processed['mime_type'],
                    'extension' => pathinfo(
                        $processed['original_path'],
                        PATHINFO_EXTENSION,
                    ),
                    'size' => $data->file->getSize(),
                    'metadata' => [
                        'width' => $processed['width'],
                        'height' => $processed['height'],
                        'variants' => [
                            'webp' => $processed['webp_path'],
                            'thumbnail' => $processed['thumbnail_path'],
                        ],
                    ],
                    'alt_text' => $data->altText,
                    'caption' => $data->caption,
                    'uploaded_by' => $data->uploadedBy,
                ]);
            });
        } catch (Throwable $exception) {
            $this->deleteProcessedAssets($processed);

            throw $exception;
        }
    }

    private function uploadNonImage(MediaUploadData $data): Media
    {
        $stored = $this->storage->store($data->file);

        try {
            return DB::transaction(function () use (
                $data,
                $stored,
            ): Media {
                return Media::query()->create([
                    'original_name' => $data->file->getClientOriginalName(),
                    'file_name' => $stored['file_name'],
                    'disk' => $stored['disk'],
                    'path' => $stored['path'],
                    'mime_type' => $stored['mime_type'],
                    'extension' => $stored['extension'],
                    'size' => $stored['size'],
                    'metadata' => $this->extractMetadata(
                        $data,
                        $stored,
                    ),
                    'alt_text' => $data->altText,
                    'caption' => $data->caption,
                    'uploaded_by' => $data->uploadedBy,
                ]);
            });
        } catch (Throwable $exception) {
            $this->storage->delete(
                $stored['disk'],
                $stored['path'],
                $stored['file_name'],
            );

            throw $exception;
        }
    }

    private function isProcessableImage(
        ?string $mimeType,
    ): bool {
        return in_array($mimeType, [
            'image/jpeg',
            'image/png',
            'image/webp',
        ], true);
    }

    /**
     * @param array{
     *     original_path: string,
     *     webp_path: string|null,
     *     thumbnail_path: string,
     *     width: int,
     *     height: int,
     *     mime_type: string
     * } $processed
     */
    private function deleteProcessedAssets(array $processed): void
    {
        $paths = [
            $processed['original_path'],
            $processed['webp_path'],
            $processed['thumbnail_path'],
        ];

        Storage::disk('public')->delete(
            array_values(
                array_filter(
                    $paths,
                    static fn (?string $path): bool => $path !== null,
                ),
            ),
        );
    }

    /**
     * @param array{
     *     disk: string,
     *     path: string,
     *     file_name: string,
     *     mime_type: string,
     *     extension: string,
     *     size: int
     * } $stored
     * @return array<string, mixed>
     */
    private function extractMetadata(
        MediaUploadData $data,
        array $stored,
    ): array {
        if (! str_starts_with($stored['mime_type'], 'image/')) {
            return [];
        }

        $dimensions = @getimagesize(
            $data->file->getRealPath(),
        );

        if ($dimensions === false) {
            return [];
        }

        return [
            'width' => $dimensions[0],
            'height' => $dimensions[1],
        ];
    }
}
