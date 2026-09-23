<?php

declare(strict_types=1);

namespace App\ContentBlocks\Data;

use App\ContentBlocks\Contracts\BlockDataContract;
use InvalidArgumentException;

final readonly class HeroBlockData implements BlockDataContract
{
    public function __construct(
        public string $title,
        public ?string $subtitle = null,
        public ?string $image_uuid = null,
        public ?string $button_text = null,
        public ?string $button_url = null,
        public string $alignment = 'left',
    ) {
        if (trim($this->title) === '') {
            throw new InvalidArgumentException(
                'Hero block title cannot be empty.',
            );
        }

        if (! in_array($this->alignment, ['left', 'center', 'right'], true)) {
            throw new InvalidArgumentException(
                'Hero block alignment is invalid.',
            );
        }

        if ($this->image_uuid !== null && ! preg_match(
            '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-5][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/',
            $this->image_uuid
        )) {
            throw new InvalidArgumentException(
                'Hero block image UUID is invalid.',
            );
        }

        if ($this->button_text !== null && trim($this->button_text) === '') {
            throw new InvalidArgumentException(
                'Hero block button text cannot be empty.',
            );
        }

        if ($this->button_url !== null && trim($this->button_url) === '') {
            throw new InvalidArgumentException(
                'Hero block button URL cannot be empty.',
            );
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function validate(array $data): void
    {
        if (! array_key_exists('title', $data)) {
            throw new InvalidArgumentException(
                'Hero block title is required.',
            );
        }

        if (! is_string($data['title'])) {
            throw new InvalidArgumentException(
                'Hero block title must be a string.',
            );
        }

        if (trim($data['title']) === '') {
            throw new InvalidArgumentException(
                'Hero block title cannot be empty.',
            );
        }

        if (
            isset($data['subtitle'])
            && $data['subtitle'] !== null
            && ! is_string($data['subtitle'])
        ) {
            throw new InvalidArgumentException(
                'Hero block subtitle must be a string.',
            );
        }

        if (
            isset($data['image_uuid'])
            && $data['image_uuid'] !== null
            && (
                ! is_string($data['image_uuid'])
                || ! preg_match(
                    '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-5][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/',
                    $data['image_uuid']
                )
            )
        ) {
            throw new InvalidArgumentException(
                'Hero block image UUID is invalid.',
            );
        }

        if (
            isset($data['button_text'])
            && $data['button_text'] !== null
            && ! is_string($data['button_text'])
        ) {
            throw new InvalidArgumentException(
                'Hero block button text must be a string.',
            );
        }

        if (
            isset($data['button_url'])
            && $data['button_url'] !== null
            && ! is_string($data['button_url'])
        ) {
            throw new InvalidArgumentException(
                'Hero block button URL must be a string.',
            );
        }

        $alignment = $data['alignment'] ?? 'left';

        if (
            ! is_string($alignment)
            || ! in_array($alignment, ['left', 'center', 'right'], true)
        ) {
            throw new InvalidArgumentException(
                'Hero block alignment is invalid.',
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
            title: $data['title'],
            subtitle: $data['subtitle'] ?? null,
            image_uuid: $data['image_uuid'] ?? null,
            button_text: $data['button_text'] ?? null,
            button_url: $data['button_url'] ?? null,
            alignment: $data['alignment'] ?? 'left',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'image_uuid' => $this->image_uuid,
            'button_text' => $this->button_text,
            'button_url' => $this->button_url,
            'alignment' => $this->alignment,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function mediaReferences(): array
    {
        return $this->image_uuid !== null
            ? [$this->image_uuid]
            : [];
    }
}
