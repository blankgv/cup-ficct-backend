<?php

namespace App\Modules\AcademicManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formatea un periodo para la respuesta API.
 *
 * @mixin \App\Modules\AcademicManagement\Models\Periodo
 */
class PeriodoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'gestion' => $this->gestion,
            'fecha_inicio_clases' => $this->fecha_inicio_clases?->format('Y-m-d'),
            'fecha_fin_clases' => $this->fecha_fin_clases?->format('Y-m-d'),
        ];
    }
}
