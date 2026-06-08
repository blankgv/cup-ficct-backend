<?php

namespace App\Modules\ApplicantAdmission\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida el auto-registro público de un postulante (datos mínimos + cuenta).
class RegistroPostulanteRequest extends FormRequest
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
            'documento' => ['required', 'string', 'max:50', Rule::unique('postulantes', 'documento')],
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('postulantes', 'email'),
                Rule::unique('users', 'email'),
            ],
            'telefono' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
