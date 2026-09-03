<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Menu;

use Illuminate\Foundation\Http\FormRequest;
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
                'required_if:type,url',
                'prohibited_if:type,route',
            ],

            'route_name' => [
                'nullable',
                'string',
                'max:255',
                'required_if:type,route',
                'prohibited_if:type,url',
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
                'exists:menus,id',
            ],
        ];
    }
}
