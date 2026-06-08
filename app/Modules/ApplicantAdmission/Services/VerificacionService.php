<?php

namespace App\Modules\ApplicantAdmission\Services;

use App\Modules\ApplicantAdmission\Enums\EstadoPostulacion;
use App\Modules\ApplicantAdmission\Models\Postulacion;
use App\Modules\Payments\DTOs\CreatePagoDTO;
use App\Modules\Payments\Models\Pago;
use App\Modules\Payments\Services\PagoService;
use Illuminate\Support\Facades\DB;

// Verificación de requisitos del postulante (para inscribirse al CUP).
// No determina admisión a la carrera; solo si cumple requisitos.
class VerificacionService
{
    public function __construct(private readonly PagoService $pagos) {}

    // Cumple requisitos → VERIFICADO. Genera el cobro de inscripción (idempotente).
    public function verificar(Postulacion $postulacion): Postulacion
    {
        return DB::transaction(function () use ($postulacion) {
            $postulacion->update([
                'estado' => EstadoPostulacion::VERIFICADO->value,
                'observacion' => null,
            ]);

            $this->generarPagoInscripcion($postulacion);

            return $postulacion;
        });
    }

    // Crea el pago de inscripción (PENDIENTE) si aún no existe para esa convocatoria.
    private function generarPagoInscripcion(Postulacion $postulacion): void
    {
        $concepto = (string) config('payments.inscripcion.concepto');

        $existe = Pago::query()
            ->where('postulante_documento', $postulacion->postulante_documento)
            ->where('convocatoria_id', $postulacion->convocatoria_id)
            ->where('concepto', $concepto)
            ->exists();

        if ($existe) {
            return;
        }

        $this->pagos->create(new CreatePagoDTO(
            postulanteDocumento: $postulacion->postulante_documento,
            convocatoriaId: $postulacion->convocatoria_id,
            monto: (float) config('payments.inscripcion.monto'),
            concepto: $concepto,
            metodo: (string) config('payments.inscripcion.metodo'),
            fechaPago: now()->format('Y-m-d H:i'),
        ));
    }

    // No cumple requisitos → RECHAZADO con motivo.
    public function rechazar(Postulacion $postulacion, string $motivo): Postulacion
    {
        $postulacion->update([
            'estado' => EstadoPostulacion::RECHAZADO->value,
            'observacion' => $motivo,
        ]);

        return $postulacion;
    }
}
