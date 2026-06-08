<?php

namespace App\Modules\ApplicantAdmission\Requests;

use Illuminate\Foundation\Http\FormRequest;

// El postulante completa/edita su propio perfil (no cambia documento ni email).
class CompletarPerfilRequest extends FormRequest
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
            'nombres' => ['sometimes', 'string', 'max:255'],
            'apellidos' => ['sometimes', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'fecha_nacimiento' => ['sometimes', 'date'],
            'colegio' => ['sometimes', 'string', 'max:255'],
            'ciudad' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
