<?php

declare(strict_types=1);

namespace App\ContentBlocks\Data;

use App\ContentBlocks\Contracts\BlockDataContract;
use InvalidArgumentException;

final readonly class ImageBlockData implements BlockDataContract
{
    public function __construct(
        public string $image_uuid,
        public string $alt,
        public ?string $caption = null,
        public ?string $link_url = null,
    ) {
        self::validate([
            'image_uuid' => $this->image_uuid,
            'alt' => $this->alt,
            'caption' => $this->caption,
            'link_url' => $this->link_url,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function validate(array $data): void
    {
        if (! array_key_exists('image_uuid', $data)) {
            throw new InvalidArgumentException(
                'Image block image UUID is required.',
            );
        }

        if (! is_string($data['image_uuid'])) {
            throw new InvalidArgumentException(
                'Image block image UUID must be a string.',
            );
        }

        if (! preg_match(
            '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-5][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/',
            $data['image_uuid'],
        )) {
            throw new InvalidArgumentException(
                'Image block image UUID is invalid.',
            );
        }

        if (! array_key_exists('alt', $data)) {
            throw new InvalidArgumentException(
                'Image block alt text is required.',
            );
        }

        if (! is_string($data['alt'])) {
            throw new InvalidArgumentException(
                'Image block alt text must be a string.',
            );
        }

        if (trim($data['alt']) === '') {
            throw new InvalidArgumentException(
                'Image block alt text cannot be empty.',
            );
        }

        if (
            isset($data['caption'])
            && $data['caption'] !== null
            && ! is_string($data['caption'])
        ) {
            throw new InvalidArgumentException(
                'Image block caption must be a string.',
            );
        }

        if (
            isset($data['link_url'])
            && $data['link_url'] !== null
            && ! is_string($data['link_url'])
        ) {
            throw new InvalidArgumentException(
                'Image block link URL must be a string.',
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
            image_uuid: $data['image_uuid'],
            alt: $data['alt'],
            caption: $data['caption'] ?? null,
            link_url: $data['link_url'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'image_uuid' => $this->image_uuid,
            'alt' => $this->alt,
            'caption' => $this->caption,
            'link_url' => $this->link_url,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function mediaReferences(): array
    {
        return [$this->image_uuid];
    }
}
