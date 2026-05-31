<?php

namespace App\Modules\ApplicantAdmission\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la postulación (las carreras deben estar ofertadas en la convocatoria).
class StorePostulacionRequest extends FormRequest
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
        $convocatoria = $this->input('convocatoria_id');

        return [
            'convocatoria_id' => ['required', 'integer', Rule::exists('convocatorias', 'id')],
            'carrera_primera_codigo' => [
                'required', 'string',
                Rule::exists('carrera_convocatoria', 'carrera_codigo')->where('convocatoria_id', $convocatoria),
            ],
            'carrera_segunda_codigo' => [
                'required', 'string', 'different:carrera_primera_codigo',
                Rule::exists('carrera_convocatoria', 'carrera_codigo')->where('convocatoria_id', $convocatoria),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'carrera_primera_codigo.exists' => 'La carrera de primera opción no está ofertada en esta convocatoria.',
            'carrera_segunda_codigo.exists' => 'La carrera de segunda opción no está ofertada en esta convocatoria.',
        ];
    }
}
