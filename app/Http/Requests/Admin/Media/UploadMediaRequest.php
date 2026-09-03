<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Media;

use App\Data\Media\MediaUploadData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rules\File;

final class UploadMediaRequest extends FormRequest
{
    /**
     * Authorization is handled by route middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for media upload.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                File::types([
                    'jpg',
                    'jpeg',
                    'png',
                    'webp',
                    'pdf',
                ])
                    ->max('10mb'),
            ],

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
    public function toData(): MediaUploadData
    {
        return new MediaUploadData(
            file: $this->file('file'),
            altText: $this->validated('alt_text'),
            caption: $this->validated('caption'),
            uploadedBy: $this->user()?->id,
        );
    }
}
