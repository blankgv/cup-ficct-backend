<?php

namespace App\Modules\Evaluation\Requests;

use App\Modules\Evaluation\Enums\EstadoAsistencia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la carga masiva de asistencia de una fecha para una materia del grupo.
class BatchAsistenciasRequest extends FormRequest
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
            'fecha' => ['required', 'date'],
            'asistencias' => ['required', 'array', 'min:1'],
            'asistencias.*.postulante_documento' => ['required', 'string'],
            'asistencias.*.estado' => ['required', Rule::enum(EstadoAsistencia::class)],
        ];
    }
}
