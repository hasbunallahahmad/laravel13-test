<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Menu;

use App\Data\Menu\MenuData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

final class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_-]+$/',
                Rule::unique('menus', 'name'),
            ],

            'label' => [
                'required',
                'string',
                'max:255',
            ],

            'type' => [
                'required',
                Rule::in([
                    'route',
                    'url',
                ]),
            ],

            'url' => [
                'nullable',
                'string',
                'max:2048',
                'url:http,https',
                'required_if:type,url',
                'prohibited_if:type,route',
            ],

            'route_name' => [
                'nullable',
                'string',
                'max:255',
                'required_if:type,route',
                'prohibited_if:type,url',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value !== null && ! Route::has($value)) {
                        $fail('The selected route name does not exist.');
                    }
                },
            ],

            'target' => [
                'nullable',
                Rule::in([
                    '_self',
                    '_blank',
                ]),
            ],

            'icon' => [
                'nullable',
                'string',
                'max:100',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('menus', 'id')
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    public function toData(): MenuData
    {
        $validated = $this->validated();

        return new MenuData(
            name: $validated['name'],
            label: $validated['label'],
            type: $validated['type'],
            url: $validated['url'] ?? null,
            routeName: $validated['route_name'] ?? null,
            target: $validated['target'] ?? '_self',
            icon: $validated['icon'] ?? null,
            sortOrder: (int) ($validated['sort_order'] ?? 0),
            isActive: (bool) ($validated['is_active'] ?? true),
            parentId: isset($validated['parent_id'])
                ? (int) $validated['parent_id']
                : null,
        );
    }
}
