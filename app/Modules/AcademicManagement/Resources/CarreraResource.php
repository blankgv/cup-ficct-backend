<?php

namespace App\Modules\AcademicManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formatea una carrera para la respuesta API.
 *
 * @mixin \App\Modules\AcademicManagement\Models\Carrera
 */
class CarreraResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'facultad_codigo' => $this->facultad_codigo,
            'created_at' => $this->created_at,
        ];
    }
}
