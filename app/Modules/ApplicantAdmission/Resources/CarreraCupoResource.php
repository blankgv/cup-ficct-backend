<?php

namespace App\Modules\ApplicantAdmission\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Carrera con sus cupos en una convocatoria.
 *
 * @mixin \App\Modules\AcademicManagement\Models\Carrera
 */
class CarreraCupoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'carrera_codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'cupos' => (int) $this->pivot->cupos,
        ];
    }
}
