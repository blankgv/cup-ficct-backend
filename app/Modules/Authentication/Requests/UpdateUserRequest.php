<?php

namespace App\Modules\Authentication\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la edición de un usuario.
class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'username' => ['sometimes', 'nullable', 'string', 'max:255'],
            'role' => ['sometimes', 'string', Rule::exists('roles', 'name')],
        ];
    }
}
