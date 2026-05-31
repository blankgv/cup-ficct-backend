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
            'sigla' => $this->sigla,
            'nombre' => $this->nombre,
            'peso' => (float) $this->peso,
            'created_at' => $this->created_at,
        ];
    }
}
