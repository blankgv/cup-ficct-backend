<?php

namespace App\Modules\AcademicManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formatea un feriado para la respuesta API.
 *
 * @mixin \App\Modules\AcademicManagement\Models\Feriado
 */
class FeriadoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'fecha' => $this->fecha,
            'descripcion' => $this->descripcion,
            'gestion' => $this->gestion,
        ];
    }
}
