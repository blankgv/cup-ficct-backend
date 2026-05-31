<?php

namespace App\Modules\ApplicantAdmission\Services;

use App\Modules\ApplicantAdmission\Enums\EstadoPostulacion;
use App\Modules\ApplicantAdmission\Models\Postulacion;

// Verificación de requisitos del postulante (para inscribirse al CUP).
// No determina admisión a la carrera; solo si cumple requisitos.
class VerificacionService
{
    // Cumple requisitos → VERIFICADO.
    public function verificar(Postulacion $postulacion): Postulacion
    {
        $postulacion->update([
            'estado' => EstadoPostulacion::VERIFICADO->value,
            'observacion' => null,
        ]);

        return $postulacion;
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
