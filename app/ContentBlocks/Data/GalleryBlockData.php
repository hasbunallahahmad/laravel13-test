<?php

declare(strict_types=1);

namespace App\ContentBlocks\Data;

use App\ContentBlocks\Contracts\BlockDataContract;
use InvalidArgumentException;

final readonly class GalleryBlockData implements BlockDataContract
{
    /**
     * @param array<int, string> $images
     */
    public function __construct(
        public array $images,
        public int $columns = 3,
        public ?string $caption = null,
        public bool $lightbox = true,
    ) {
        self::validate([
            'images' => $this->images,
            'columns' => $this->columns,
            'caption' => $this->caption,
            'lightbox' => $this->lightbox,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function validate(array $data): void
    {
        if (! array_key_exists('images', $data)) {
            throw new InvalidArgumentException(
                'Gallery block images are required.',
            );
        }

        if (! is_array($data['images'])) {
            throw new InvalidArgumentException(
                'Gallery block images must be an array.',
            );
        }

        if ($data['images'] === []) {
            throw new InvalidArgumentException(
                'Gallery block must contain at least one image.',
            );
        }

        foreach ($data['images'] as $imageUuid) {
            if (! is_string($imageUuid)) {
                throw new InvalidArgumentException(
                    'Gallery block image UUID must be a string.',
                );
            }

            if (! preg_match(
                '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-5][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/',
                $imageUuid,
            )) {
                throw new InvalidArgumentException(
                    'Gallery block contains an invalid image UUID.',
                );
            }
        }

        $columns = $data['columns'] ?? 3;

        if (
            ! is_int($columns)
            || ! in_array($columns, [1, 2, 3, 4], true)
        ) {
            throw new InvalidArgumentException(
                'Gallery block columns must be between 1 and 4.',
            );
        }

        if (
            isset($data['caption'])
            && $data['caption'] !== null
            && ! is_string($data['caption'])
        ) {
            throw new InvalidArgumentException(
                'Gallery block caption must be a string.',
            );
        }

        $lightbox = $data['lightbox'] ?? true;

        if (! is_bool($lightbox)) {
            throw new InvalidArgumentException(
                'Gallery block lightbox must be a boolean.',
            );
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        self::validate($data);

        return new self(
            images: $data['images'],
            columns: $data['columns'] ?? 3,
            caption: $data['caption'] ?? null,
            lightbox: $data['lightbox'] ?? true,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'images' => $this->images,
            'columns' => $this->columns,
            'caption' => $this->caption,
            'lightbox' => $this->lightbox,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function mediaReferences(): array
    {
        return $this->images;
    }
}
