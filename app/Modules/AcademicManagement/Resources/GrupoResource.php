<?php

namespace App\Modules\AcademicManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formatea un grupo para la respuesta API.
 *
 * @mixin \App\Modules\AcademicManagement\Models\Grupo
 */
class GrupoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'turno' => $this->turno,
            'capacidad' => $this->capacidad,
            'gestion' => $this->gestion,
            'created_at' => $this->created_at,
        ];
    }
}
