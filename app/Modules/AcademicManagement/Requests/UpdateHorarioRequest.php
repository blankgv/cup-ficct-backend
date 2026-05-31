<?php

namespace App\Modules\AcademicManagement\Requests;

use App\Modules\AcademicManagement\Enums\DiaSemana;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la edición de un horario (la PK no cambia).
class UpdateHorarioRequest extends FormRequest
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
            'dia' => ['sometimes', Rule::enum(DiaSemana::class)],
            'hora_inicio' => ['sometimes', 'date_format:H:i'],
            'hora_fin' => ['sometimes', 'date_format:H:i'],
            'aula_modulo_numero' => ['sometimes', 'string', 'required_with:aula_numero'],
            'aula_numero' => [
                'sometimes', 'integer', 'required_with:aula_modulo_numero',
                Rule::exists('aulas', 'numero')->where('modulo_numero', $this->input('aula_modulo_numero')),
            ],
        ];
    }
}
