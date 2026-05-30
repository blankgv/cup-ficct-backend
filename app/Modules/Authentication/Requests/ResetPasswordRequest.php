<?php

namespace App\Modules\Authentication\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Valida el reseteo de contraseña con token.
class ResetPasswordRequest extends FormRequest
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
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];
    }
}
