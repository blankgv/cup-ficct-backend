<?php

namespace App\Modules\ApplicantAdmission\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formatea una postulación para la respuesta API.
 *
 * @mixin \App\Modules\ApplicantAdmission\Models\Postulacion
 */
class PostulacionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'postulante_documento' => $this->postulante_documento,
            'convocatoria_id' => $this->convocatoria_id,
            'carrera_primera_codigo' => $this->carrera_primera_codigo,
            'carrera_segunda_codigo' => $this->carrera_segunda_codigo,
            'estado' => $this->estado,
            'observacion' => $this->observacion,
            'created_at' => $this->created_at,
        ];
    }
}
