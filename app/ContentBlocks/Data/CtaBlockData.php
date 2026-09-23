<?php

declare(strict_types=1);

namespace App\ContentBlocks\Data;

use App\ContentBlocks\Contracts\BlockDataContract;
use InvalidArgumentException;

final readonly class CtaBlockData implements BlockDataContract
{
    public function __construct(
        public string $title,
        public ?string $description = null,
        public string $button_text = '',
        public string $button_url = '',
        public string $alignment = 'left',
    ) {
        self::validate([
            'title' => $this->title,
            'description' => $this->description,
            'button_text' => $this->button_text,
            'button_url' => $this->button_url,
            'alignment' => $this->alignment,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function validate(array $data): void
    {
        if (! array_key_exists('title', $data)) {
            throw new InvalidArgumentException(
                'CTA block title is required.',
            );
        }

        if (! is_string($data['title'])) {
            throw new InvalidArgumentException(
                'CTA block title must be a string.',
            );
        }

        if (trim($data['title']) === '') {
            throw new InvalidArgumentException(
                'CTA block title cannot be empty.',
            );
        }

        if (
            isset($data['description'])
            && $data['description'] !== null
            && ! is_string($data['description'])
        ) {
            throw new InvalidArgumentException(
                'CTA block description must be a string.',
            );
        }

        if (! array_key_exists('button_text', $data)) {
            throw new InvalidArgumentException(
                'CTA block button text is required.',
            );
        }

        if (! is_string($data['button_text'])) {
            throw new InvalidArgumentException(
                'CTA block button text must be a string.',
            );
        }

        if (trim($data['button_text']) === '') {
            throw new InvalidArgumentException(
                'CTA block button text cannot be empty.',
            );
        }

        if (! array_key_exists('button_url', $data)) {
            throw new InvalidArgumentException(
                'CTA block button URL is required.',
            );
        }

        if (! is_string($data['button_url'])) {
            throw new InvalidArgumentException(
                'CTA block button URL must be a string.',
            );
        }

        if (trim($data['button_url']) === '') {
            throw new InvalidArgumentException(
                'CTA block button URL cannot be empty.',
            );
        }

        $alignment = $data['alignment'] ?? 'left';

        if (
            ! is_string($alignment)
            || ! in_array($alignment, ['left', 'center', 'right'], true)
        ) {
            throw new InvalidArgumentException(
                'CTA block alignment is invalid.',
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
            description: $data['description'] ?? null,
            button_text: $data['button_text'],
            button_url: $data['button_url'],
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
            'description' => $this->description,
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
        return [];
    }
}
