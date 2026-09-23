<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Content;

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Models\Content;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Services\Content\ContentHtmlSanitizer;

class StoreContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Content::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
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
                'unique:contents,slug',
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
        $slug = trim((string) $this->input('slug', ''));

        $body = (string) $this->input('body', '');

        $sanitizer = app(ContentHtmlSanitizer::class);

        $this->merge([
            'slug' => $slug === ''
                ? Str::slug((string) $this->input('title', ''))
                : $slug,
            'body' => $sanitizer->sanitize($body),
        ]);
    }
}
