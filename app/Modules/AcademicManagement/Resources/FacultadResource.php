<?php

namespace App\Modules\AcademicManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formatea una facultad para la respuesta API.
 *
 * @mixin \App\Modules\AcademicManagement\Models\Facultad
 */
class FacultadResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'abreviatura' => $this->abreviatura,
            'created_at' => $this->created_at,
        ];
    }
}
