<?php

namespace App\Modules\ApplicantAdmission\Enums;

// Estado de la convocatoria.
enum EstadoConvocatoria: string
{
    case ABIERTA = 'ABIERTA';
    case CERRADA = 'CERRADA';
}
