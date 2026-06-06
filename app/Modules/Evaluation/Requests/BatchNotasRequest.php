<?php

namespace App\Modules\Evaluation\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Valida la carga masiva de notas de un examen para una materia del grupo.
class BatchNotasRequest extends FormRequest
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
            'numero' => ['required', 'integer', 'min:1'],
            'notas' => ['required', 'array', 'min:1'],
            'notas.*.postulante_documento' => ['required', 'string'],
            'notas.*.valor' => ['required', 'numeric', 'between:0,100'],
        ];
    }
}
