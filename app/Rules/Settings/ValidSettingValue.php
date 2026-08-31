<?php

declare(strict_types=1);

namespace App\Rules\Settings;

use App\Support\Settings\SettingValueCaster;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class ValidSettingValue implements ValidationRule
{
    public function __construct(
        private readonly string $type,
    ) {}

    /**
     * Validate a setting value based on its configured type.
     *
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail,
    ): void {
        if (! SettingValueCaster::supports($this->type)) {
            $fail("Unsupported setting type: {$this->type}");

            return;
        }

        match ($this->type) {
            'string' => $this->validateString(
                $attribute,
                $value,
                $fail,
            ),

            'integer' => $this->validateInteger(
                $attribute,
                $value,
                $fail,
            ),

            'boolean' => $this->validateBoolean(
                $attribute,
                $value,
                $fail,
            ),

            'json' => $this->validateJson(
                $attribute,
                $value,
                $fail,
            ),

            'null' => $this->validateNull(
                $attribute,
                $value,
                $fail,
            ),
        };
    }

    private function validateString(
        string $attribute,
        mixed $value,
        Closure $fail,
    ): void {
        if ($value !== null && ! is_string($value)) {
            $fail("The {$attribute} must be a string.");
        }
    }

    private function validateInteger(
        string $attribute,
        mixed $value,
        Closure $fail,
    ): void {
        if ($value === null) {
            return;
        }

        if (
            ! is_int($value)
            && ! (
                is_string($value)
                && preg_match('/^-?\d+$/', $value) === 1
            )
        ) {
            $fail("The {$attribute} must be an integer.");
        }
    }

    private function validateBoolean(
        string $attribute,
        mixed $value,
        Closure $fail,
    ): void {
        if ($value === null) {
            return;
        }

        $validValues = [
            true,
            false,
            1,
            0,
            '1',
            '0',
            'true',
            'false',
        ];

        if (! in_array($value, $validValues, true)) {
            $fail("The {$attribute} must be a boolean.");
        }
    }

    private function validateJson(
        string $attribute,
        mixed $value,
        Closure $fail,
    ): void {
        if ($value === null) {
            return;
        }

        if (! is_string($value)) {
            $fail("The {$attribute} must be valid JSON.");

            return;
        }

        try {
            json_decode(
                $value,
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        } catch (\JsonException) {
            $fail("The {$attribute} must be valid JSON.");
        }
    }

    private function validateNull(
        string $attribute,
        mixed $value,
        Closure $fail,
    ): void {
        if ($value !== null) {
            $fail("The {$attribute} must be null.");
        }
    }
}
