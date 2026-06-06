<?php

namespace App\Modules\Payments\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formatea un pago para la respuesta API.
 *
 * @mixin \App\Modules\Payments\Models\Pago
 */
class PagoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'postulante_documento' => $this->postulante_documento,
            'convocatoria_id' => $this->convocatoria_id,
            'monto' => (float) $this->monto,
            'concepto' => $this->concepto,
            'metodo' => $this->metodo,
            'fecha_pago' => $this->fecha_pago?->format('Y-m-d H:i'),
            'estado' => $this->estado,
            'created_at' => $this->created_at,
        ];
    }
}
