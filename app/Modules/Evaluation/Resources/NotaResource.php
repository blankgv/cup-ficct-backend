<?php

namespace App\Modules\Evaluation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formatea una nota para la respuesta API.
 *
 * @mixin \App\Modules\Evaluation\Models\Nota
 */
class NotaResource extends JsonResource
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
            'numero' => $this->numero,
            'valor' => (float) $this->valor,
        ];
    }
}
