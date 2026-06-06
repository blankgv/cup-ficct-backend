<?php

namespace App\Modules\Payments\Services;

use App\Modules\Authentication\Models\User;
use App\Modules\Payments\DTOs\CreatePagoDTO;
use App\Modules\Payments\DTOs\UpdatePagoDTO;
use App\Modules\Payments\Enums\EstadoPago;
use App\Modules\Payments\Models\Pago;
use App\Modules\Payments\Repositories\PagoRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

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

    // Confirma manualmente el pago (PENDIENTE -> PAGADO).
    public function confirmar(Pago $pago, User $revisor): Pago
    {
        $this->garantizarPendiente($pago);

        $pago->update([
            'estado' => EstadoPago::PAGADO->value,
            'confirmado_por' => $revisor->id,
            'confirmado_at' => now(),
            'motivo_rechazo' => null,
        ]);

        return $pago;
    }

    // Rechaza manualmente el pago (PENDIENTE -> RECHAZADO) con motivo.
    public function rechazar(Pago $pago, User $revisor, string $motivo): Pago
    {
        $this->garantizarPendiente($pago);

        $pago->update([
            'estado' => EstadoPago::RECHAZADO->value,
            'confirmado_por' => $revisor->id,
            'confirmado_at' => now(),
            'motivo_rechazo' => $motivo,
        ]);

        return $pago;
    }

    // Solo se puede confirmar/rechazar un pago PENDIENTE.
    private function garantizarPendiente(Pago $pago): void
    {
        if ($pago->estado !== EstadoPago::PENDIENTE) {
            throw ValidationException::withMessages([
                'estado' => 'Solo se puede confirmar o rechazar un pago pendiente.',
            ]);
        }
    }
}
