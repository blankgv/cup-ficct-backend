<?php

namespace App\Modules\AcademicManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formatea una materia para la respuesta API.
 *
 * @mixin \App\Modules\AcademicManagement\Models\Materia
 */
class MateriaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'sigla' => $this->sigla,
            'peso' => (float) $this->peso,
            'created_at' => $this->created_at,
        ];
    }
}
