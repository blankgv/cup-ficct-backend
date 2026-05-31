<?php

namespace App\Modules\AcademicManagement\Requests;

use App\Modules\AcademicManagement\Enums\DiaSemana;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la creación de un horario.
class StoreHorarioRequest extends FormRequest
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
            'dia' => ['required', Rule::enum(DiaSemana::class)],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'aula_modulo_numero' => ['required', 'string'],
            'aula_numero' => [
                'required', 'integer',
                Rule::exists('aulas', 'numero')->where('modulo_numero', $this->input('aula_modulo_numero')),
            ],
        ];
    }
}
