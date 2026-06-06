<?php

namespace App\Modules\Evaluation\Requests;

use App\Modules\Evaluation\Enums\EstadoAsistencia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la carga de una asistencia individual.
class StoreAsistenciaRequest extends FormRequest
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
            'postulante_documento' => ['required', 'string'],
            'convocatoria_id' => ['required', 'integer'],
            'materia_sigla' => ['required', 'string'],
            'fecha' => ['required', 'date'],
            'estado' => ['required', Rule::enum(EstadoAsistencia::class)],
        ];
    }
}
