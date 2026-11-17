<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\HomepageCta\Concerns;

use App\Services\HomepageCta\HomepageCtaButtonDestinationValidator;
use Illuminate\Validation\Rule;

trait HasHomepageCtaRules
{
    protected function homepageCtaRules(
        bool $allowButtonUuid = false,
    ): array {
        $destinationValidator = app(
            HomepageCtaButtonDestinationValidator::class,
        );

        $buttonKeys = [
            'label',
            'type',
            'url',
            'route_name',
            'target',
            'sort_order',
            'is_active',
        ];

        if ($allowButtonUuid) {
            $buttonKeys[] = 'uuid';
        }

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],

            'buttons' => ['required', 'array', 'min:1'],

            'buttons.*' => [
                'required',
                'array:'.implode(',', $buttonKeys),
            ],

            'buttons.*.label' => [
                'required',
                'string',
                'max:100',
            ],

            'buttons.*.type' => [
                'required',
                'string',
                Rule::in(['url', 'route']),
            ],

            'buttons.*.url' => [
                'nullable',
                'string',
                'max:2048',
                'required_if:buttons.*.type,url',
                'prohibited_if:buttons.*.type,route',
                function (
                    string $attribute,
                    mixed $value,
                    \Closure $fail,
                ) use ($destinationValidator): void {
                    if (
                        $value !== null
                        && (
                            ! is_string($value)
                            || ! $destinationValidator->isValidUrl($value)
                        )
                    ) {
                        $fail(
                            'The '.$attribute.' field must contain a valid and safe URL.',
                        );
                    }
                },
            ],

            'buttons.*.route_name' => [
                'nullable',
                'string',
                'max:100',
                'required_if:buttons.*.type,route',
                'prohibited_if:buttons.*.type,url',
                function (
                    string $attribute,
                    mixed $value,
                    \Closure $fail,
                ) use ($destinationValidator): void {
                    if (
                        $value !== null
                        && (
                            ! is_string($value)
                            || ! $destinationValidator->isAllowedRoute($value)
                        )
                    ) {
                        $fail(
                            'The '.$attribute.' field is not an allowed route.',
                        );
                    }
                },
            ],

            'buttons.*.target' => [
                'nullable',
                'string',
                Rule::in(['_self', '_blank']),
            ],

            'buttons.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'buttons.*.is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }
}
