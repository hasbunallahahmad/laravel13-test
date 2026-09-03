<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Media;

use App\Data\Media\MediaUpdateData;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateMediaRequest extends FormRequest
{
    /**
     * Authorization is handled by route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for media metadata update.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'alt_text' => [
                'nullable',
                'string',
                'max:255',
            ],

            'caption' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    /**
     * Convert validated request into DTO.
     */
    public function toData(): MediaUpdateData
    {
        return new MediaUpdateData(
            altText: $this->validated('alt_text'),
            caption: $this->validated('caption'),
        );
    }
}
