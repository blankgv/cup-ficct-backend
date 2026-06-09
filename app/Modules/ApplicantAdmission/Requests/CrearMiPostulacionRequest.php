<?php

namespace App\Modules\ApplicantAdmission\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// El postulante crea su propia postulación (convocatoria, carreras y turno).
class CrearMiPostulacionRequest extends FormRequest
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
            'convocatoria_id' => ['required', 'integer', 'exists:convocatorias,id'],
            'carrera_primera_codigo' => ['required', 'string', 'exists:carreras,codigo'],
            'carrera_segunda_codigo' => [
                'required', 'string', 'exists:carreras,codigo', 'different:carrera_primera_codigo',
            ],
            'turno_preferencia' => ['required', Rule::in(['MANANA', 'TARDE', 'NOCHE'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'carrera_segunda_codigo.different' => 'La 2ª opción debe ser distinta de la 1ª.',
        ];
    }
}
