<?php

namespace App\Modules\AcademicManagement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Asociación grupo-materia con su docente.
 *
 * @mixin \App\Modules\AcademicManagement\Models\GrupoMateria
 */
class GrupoMateriaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'grupo_id' => $this->grupo_id,
            'materia_sigla' => $this->materia_sigla,
            'docente' => $this->docente ? new DocenteResource($this->docente) : null,
        ];
    }
}
