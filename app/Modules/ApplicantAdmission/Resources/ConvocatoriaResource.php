<?php

namespace App\Modules\ApplicantAdmission\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formatea una convocatoria para la respuesta API.
 *
 * @mixin \App\Modules\ApplicantAdmission\Models\Convocatoria
 */
class ConvocatoriaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'gestion' => $this->gestion,
            'fecha_inicio' => $this->fecha_inicio?->format('Y-m-d'),
            'fecha_fin' => $this->fecha_fin?->format('Y-m-d'),
            'estado' => $this->estado,
        ];
    }
}
