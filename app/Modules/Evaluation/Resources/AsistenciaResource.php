<?php

namespace App\Modules\Evaluation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formatea una asistencia para la respuesta API.
 *
 * @mixin \App\Modules\Evaluation\Models\Asistencia
 */
class AsistenciaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'postulante_documento' => $this->postulante_documento,
            'convocatoria_id' => $this->convocatoria_id,
            'materia_sigla' => $this->materia_sigla,
            'fecha' => $this->fecha?->format('Y-m-d'),
            'estado' => $this->estado,
        ];
    }
}
