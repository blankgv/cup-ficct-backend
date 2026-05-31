<?php

namespace App\Modules\AcademicManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formatea un módulo para la respuesta API.
 *
 * @mixin \App\Modules\AcademicManagement\Models\Modulo
 */
class ModuloResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'numero' => $this->numero,
            'nombre' => $this->nombre,
            'ubicacion' => $this->ubicacion,
            'created_at' => $this->created_at,
        ];
    }
}
