<?php

namespace App\Modules\ApplicantAdmission\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formatea un postulante para la respuesta API.
 *
 * @mixin \App\Modules\ApplicantAdmission\Models\Postulante
 */
class PostulanteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'documento' => $this->documento,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'fecha_nacimiento' => $this->fecha_nacimiento?->format('Y-m-d'),
            'colegio' => $this->colegio,
            'ciudad' => $this->ciudad,
            'titulo_bachiller_path' => $this->titulo_bachiller_path,
            'created_at' => $this->created_at,
        ];
    }
}
