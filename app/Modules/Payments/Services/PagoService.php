<?php

namespace App\Modules\Payments\Services;

use App\Modules\Payments\DTOs\CreatePagoDTO;
use App\Modules\Payments\DTOs\UpdatePagoDTO;
use App\Modules\Payments\Models\Pago;
use App\Modules\Payments\Repositories\PagoRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

// Lógica de negocio de pagos.
class PagoService
{
    public function __construct(private readonly PagoRepository $pagos) {}

    public function list(?string $postulante, ?int $convocatoria, int $perPage = 15): LengthAwarePaginator
    {
        return $this->pagos->paginate($postulante, $convocatoria, $perPage);
    }

    public function create(CreatePagoDTO $data): Pago
    {
        return $this->pagos->create($data->toArray());
    }

    public function update(Pago $pago, UpdatePagoDTO $data): Pago
    {
        $pago->update($data->toArray());

        return $pago;
    }

    public function delete(Pago $pago): void
    {
        $pago->delete();
    }
}
