<?php

namespace App\Modules\Evaluation\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Valida la carga de una nota individual.
class StoreNotaRequest extends FormRequest
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
            'numero' => ['required', 'integer', 'min:1'],
            'valor' => ['required', 'numeric', 'between:0,100'],
        ];
    }
}
