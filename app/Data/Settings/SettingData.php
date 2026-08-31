<?php

namespace App\Data\Settings;

use App\Support\Settings\SettingValueCaster;
use InvalidArgumentException;

readonly class SettingData
{
    /**
     * Create a new setting data object.
     */
    public function __construct(
        public string $group,
        public string $key,
        public mixed $value,
        public string $type = 'string',
        public bool $isPublic = false,
    ) {
        if (! SettingValueCaster::supports($this->type)) {
            throw new InvalidArgumentException(
                "Unsupported setting type: {$this->type}",
            );
        }

        if (trim($this->group) === '') {
            throw new InvalidArgumentException(
                'Setting group cannot be empty.',
            );
        }

        if (trim($this->key) === '') {
            throw new InvalidArgumentException(
                'Setting key cannot be empty.',
            );
        }
    }

    /**
     * Get the full setting key.
     */
    public function fullKey(): string
    {
        return "{$this->group}.{$this->key}";
    }

    /**
     * Create SettingData from a full group.key string.
     */
    public static function fromKey(
        string $fullKey,
        mixed $value,
        string $type = 'string',
        bool $isPublic = false,
    ): self {
        $parts = explode('.', $fullKey);

        if (count($parts) !== 2) {
            throw new InvalidArgumentException(
                'The setting key must use group.key format.',
            );
        }

        return new self(
            group: $parts[0],
            key: $parts[1],
            value: $value,
            type: $type,
            isPublic: $isPublic,
        );
    }
}
