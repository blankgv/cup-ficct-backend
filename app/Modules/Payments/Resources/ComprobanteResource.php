<?php

namespace App\Modules\Payments\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formatea un comprobante de pago para la respuesta API.
 *
 * @mixin \App\Modules\Payments\Models\Comprobante
 */
class ComprobanteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pago_id' => $this->pago_id,
            'nombre_original' => $this->nombre_original,
            'mime' => $this->mime,
            'tamano' => $this->tamano,
            'created_at' => $this->created_at,
        ];
    }
}
