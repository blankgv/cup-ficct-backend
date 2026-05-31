<?php

namespace App\Modules\AcademicManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formatea un aula para la respuesta API.
 *
 * @mixin \App\Modules\AcademicManagement\Models\Aula
 */
class AulaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'modulo_numero' => $this->modulo_numero,
            'numero' => $this->numero,
            'nombre' => $this->nombre,
            'capacidad' => $this->capacidad,
            'piso' => $this->piso,
            'tipo' => $this->tipo,
            'created_at' => $this->created_at,
        ];
    }
}
