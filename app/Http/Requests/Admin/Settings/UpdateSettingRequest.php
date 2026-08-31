<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Settings;

use App\Data\Settings\SettingData;
use App\Rules\Settings\ValidSettingValue;
use App\Support\Settings\SettingValueCaster;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateSettingRequest extends FormRequest
{
    /**
     * Determine whether the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'group' => [
                'required',
                'string',
                'min:1',
                'max:100',
            ],

            'key' => [
                'required',
                'string',
                'min:1',
                'max:100',
            ],

            'value' => [
                'nullable',
                new ValidSettingValue(
                    $this->input('type', 'string'),
                ),
            ],

            'type' => [
                'required',
                'string',
                Rule::in(SettingValueCaster::SUPPORTED_TYPES),
            ],

            'is_public' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    /**
     * Transform validated request data into a SettingData DTO.
     */
    public function toData(): SettingData
    {
        $validated = $this->validated();

        return new SettingData(
            group: $validated['group'],
            key: $validated['key'],
            value: $validated['value'] ?? null,
            type: $validated['type'],
            isPublic: $validated['is_public'] ?? false,
        );
    }
}
