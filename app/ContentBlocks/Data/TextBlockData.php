<?php

declare(strict_types=1);

namespace App\ContentBlocks\Data;

use App\ContentBlocks\Contracts\BlockDataContract;
use InvalidArgumentException;

final readonly class TextBlockData implements BlockDataContract
{
    public function __construct(
        public string $content,
    ) {
        if (trim($this->content) === '') {
            throw new InvalidArgumentException(
                'Text block content cannot be empty.',
            );
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function validate(array $data): void
    {
        if (! array_key_exists('content', $data)) {
            throw new InvalidArgumentException(
                'Text block content is required.',
            );
        }

        if (! is_string($data['content'])) {
            throw new InvalidArgumentException(
                'Text block content must be a string.',
            );
        }

        if (trim($data['content']) === '') {
            throw new InvalidArgumentException(
                'Text block content cannot be empty.',
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
            content: $data['content'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'content' => $this->content,
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
