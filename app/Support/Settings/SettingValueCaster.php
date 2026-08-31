<?php

namespace App\Support\Settings;

use InvalidArgumentException;
use JsonException;

class SettingValueCaster
{
    /**
     * Supported setting value types.
     */
    public const SUPPORTED_TYPES = [
        'string',
        'integer',
        'boolean',
        'json',
        'null',
    ];

    /**
     * Cast a database value into its appropriate PHP type.
     */
    public static function get(
        mixed $value,
        string $type,
    ): mixed {
        return match ($type) {
            'string' => $value === null
                ? null
                : (string) $value,

            'integer' => self::toInteger($value),

            'boolean' => self::toBoolean($value),

            'json' => self::decodeJson($value),

            'null' => null,

            default => throw new InvalidArgumentException(
                "Unsupported setting type: {$type}"
            ),
        };
    }

    /**
     * Convert a PHP value into a database-safe value.
     */
    public static function set(
        mixed $value,
        string $type,
    ): ?string {
        return match ($type) {
            'string' => $value === null
                ? null
                : (string) $value,

            'integer' => self::integerToString($value),

            'boolean' => self::booleanToString($value),

            'json' => self::encodeJson($value),

            'null' => null,

            default => throw new InvalidArgumentException(
                "Unsupported setting type: {$type}"
            ),
        };
    }

    /**
     * Determine whether a setting type is supported.
     */
    public static function supports(string $type): bool
    {
        return in_array(
            $type,
            self::SUPPORTED_TYPES,
            true,
        );
    }

    /**
     * Convert a database value into an integer safely.
     */
    private static function toInteger(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (
            is_string($value)
            && preg_match('/^-?\d+$/', $value) === 1
        ) {
            return (int) $value;
        }

        throw new InvalidArgumentException(
            'Invalid integer setting value.'
        );
    }

    /**
     * Convert an integer into a database representation safely.
     */
    private static function integerToString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! is_int($value)) {
            throw new InvalidArgumentException(
                'Integer setting value must be an integer.'
            );
        }

        return (string) $value;
    }

    /**
     * Convert various database representations into boolean.
     */
    private static function toBoolean(mixed $value): ?bool
    {
        if ($value === null) {
            return null;
        }

        return match ($value) {
            true, 1, '1', 'true' => true,
            false, 0, '0', 'false' => false,

            default => throw new InvalidArgumentException(
                'Invalid boolean setting value.'
            ),
        };
    }

    /**
     * Convert boolean into a database representation.
     */
    private static function booleanToString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! is_bool($value)) {
            throw new InvalidArgumentException(
                'Boolean setting value must be a boolean.'
            );
        }

        return $value ? '1' : '0';
    }

    /**
     * Decode JSON safely.
     */
    private static function decodeJson(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (! is_string($value)) {
            throw new InvalidArgumentException(
                'JSON setting value must be a string.'
            );
        }

        try {
            return json_decode(
                $value,
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $exception) {
            throw new InvalidArgumentException(
                'Invalid JSON setting value.',
                previous: $exception,
            );
        }
    }

    /**
     * Encode a value as JSON safely.
     */
    private static function encodeJson(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            return json_encode(
                $value,
                JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $exception) {
            throw new InvalidArgumentException(
                'Unable to encode setting value as JSON.',
                previous: $exception,
            );
        }
    }
}
