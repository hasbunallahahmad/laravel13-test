<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->canAssignRole($this->input('role'));
    }

    private function canAssignRole(?string $role): bool
    {
        if ($role === null) {
            return true;
        }

        return ! (
            in_array($role, ['admin', 'super-admin'], true)
            && ! $this->user()->hasRole('super-admin')
        );
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => [
                'nullable',
                'string',
                'min:12',
                'confirmed',
            ],
            'role' => [
                'nullable',
                'string',
                Rule::exists('roles', 'name')
                    ->where(fn($query) => $query->where('guard_name', 'web')),
            ],
        ];
    }
}
