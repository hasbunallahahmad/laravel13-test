<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Data\Media\MediaUpdateData;
use App\Data\Media\MediaUploadData;
use App\Models\Media;
use Illuminate\Support\Facades\DB;
use Throwable;

final class MediaService
{
    public function __construct(
        private readonly MediaStorageService $storage,
    ) {}

    public function upload(MediaUploadData $data): Media
    {
        $stored = $this->storage->store($data->file);

        try {
            return DB::transaction(function () use ($data, $stored): Media {
                return Media::query()->create([
                    'original_name' => $data->file->getClientOriginalName(),
                    'file_name' => $stored['file_name'],
                    'disk' => $stored['disk'],
                    'path' => $stored['path'],
                    'mime_type' => $stored['mime_type'],
                    'extension' => $stored['extension'],
                    'size' => $stored['size'],
                    'metadata' => $this->extractMetadata($data, $stored),
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
        $this->storage->delete(
            $media->disk,
            $media->path,
            $media->file_name,
        );

        $media->forceDelete();
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
