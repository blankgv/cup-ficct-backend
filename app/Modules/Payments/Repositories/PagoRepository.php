<?php

namespace App\Modules\Payments\Repositories;

use App\Modules\Payments\Models\Pago;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

// Acceso a datos de pagos.
class PagoRepository
{
    public function paginate(?string $postulante, ?int $convocatoria, int $perPage): LengthAwarePaginator
    {
        return Pago::query()
            ->when($postulante, fn ($q) => $q->where('postulante_documento', $postulante))
            ->when($convocatoria, fn ($q) => $q->where('convocatoria_id', $convocatoria))
            ->orderByDesc('fecha_pago')
            ->paginate($perPage);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Pago
    {
        return Pago::create($attributes);
    }
}
