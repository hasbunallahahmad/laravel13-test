<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Settings;

use App\Data\Settings\SettingData;
use App\Models\Setting;
use App\Rules\Settings\ValidSettingValue;
use App\Support\Settings\SettingValueCaster;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use JsonException;

final class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare request data before validation.
     */
    protected function prepareForValidation(): void
    {
        $setting = $this->existingSetting();

        $type = $setting?->type
            ?? $this->input('type', 'string');

        if ($type !== 'boolean' || ! $this->has('value')) {
            return;
        }

        $value = $this->input('value');

        if ($value === '1' || $value === 1) {
            $this->merge([
                'value' => true,
            ]);

            return;
        }

        if ($value === '0' || $value === 0) {
            $this->merge([
                'value' => false,
            ]);
        }
    }

    /**
     * Get the existing setting from trusted route parameters.
     */
    private function existingSetting(): ?Setting
    {
        return Setting::query()
            ->where('group', $this->route('group'))
            ->where('key', $this->route('key'))
            ->first();
    }

    public function rules(): array
    {
        $setting = $this->existingSetting();

        $type = $setting?->type
            ?? $this->input('type', 'string');

        return [
            'value' => [
                'nullable',
                new ValidSettingValue($type),
            ],

            'type' => [
                $setting ? 'sometimes' : 'required',
                'string',
                Rule::in(SettingValueCaster::SUPPORTED_TYPES),
            ],

            'is_public' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function toData(): SettingData
    {
        $validated = $this->validated();

        $existing = $this->existingSetting();

        $type = $existing?->type
            ?? $validated['type'];

        $value = $validated['value'] ?? null;

        if ($type === 'json' && is_string($value)) {
            try {
                $value = json_decode(
                    $value,
                    true,
                    512,
                    JSON_THROW_ON_ERROR,
                );
            } catch (JsonException) {
                // Validation should already reject invalid JSON.
                abort(422);
            }
        }

        return new SettingData(
            group: (string) $this->route('group'),
            key: (string) $this->route('key'),
            value: $value,
            type: $type,
            isPublic: $existing?->is_public
                ?? ($validated['is_public'] ?? false),
        );
    }
}
