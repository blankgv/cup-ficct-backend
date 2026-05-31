<?php

namespace App\Modules\ApplicantAdmission\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la edición de un postulante (documento editable; cascada vía FK ON UPDATE).
class UpdatePostulanteRequest extends FormRequest
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
        $doc = $this->route('postulante');

        return [
            'documento' => ['sometimes', 'string', 'max:50', Rule::unique('postulantes', 'documento')->ignore($doc, 'documento')],
            'nombres' => ['sometimes', 'string', 'max:255'],
            'apellidos' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('postulantes', 'email')->ignore($doc, 'documento')],
            'telefono' => ['nullable', 'string', 'max:50'],
            'fecha_nacimiento' => ['sometimes', 'date'],
            'colegio' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
