<?php

namespace App\Modules\Authentication\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Valida la solicitud de enlace de recuperación.
class ForgotPasswordRequest extends FormRequest
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
            'email' => ['required', 'email'],
        ];
    }
}
