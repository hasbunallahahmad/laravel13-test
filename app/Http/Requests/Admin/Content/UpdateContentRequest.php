<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Content;

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Models\Content;
use App\Services\Content\ContentHtmlSanitizer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $content = $this->route('content');

        return $content instanceof Content
            && ($this->user()?->can('update', $content) ?? false);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $content = $this->route('content');

        return [
            'type' => [
                'required',
                Rule::enum(ContentType::class),
            ],

            'title' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('contents', 'slug')
                    ->ignore($content?->getKey(), 'id'),
            ],

            'excerpt' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'body' => [
                'required',
                'string',
            ],

            'status' => [
                'required',
                Rule::enum(ContentStatus::class),
            ],

            'published_at' => [
                'nullable',
                'date',
            ],

            'author_uuid' => [
                'nullable',
                'uuid',
                'exists:users,uuid',
            ],

            'metadata' => [
                'nullable',
                'array',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $body = (string) $this->input('body', '');

        $sanitizer = app(ContentHtmlSanitizer::class);

        $this->merge([
            'body' => $sanitizer->sanitize($body),
        ]);
    }
}
