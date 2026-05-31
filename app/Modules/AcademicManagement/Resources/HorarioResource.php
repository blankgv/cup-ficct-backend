<?php

namespace App\Modules\AcademicManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formatea un horario para la respuesta API.
 *
 * @mixin \App\Modules\AcademicManagement\Models\Horario
 */
class HorarioResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'grupo_id' => $this->grupo_id,
            'materia_sigla' => $this->materia_sigla,
            'numero' => $this->numero,
            'dia' => $this->dia,
            'hora_inicio' => $this->hora_inicio,
            'hora_fin' => $this->hora_fin,
            'aula' => [
                'modulo_numero' => $this->aula_modulo_numero,
                'numero' => $this->aula_numero,
            ],
        ];
    }
}
