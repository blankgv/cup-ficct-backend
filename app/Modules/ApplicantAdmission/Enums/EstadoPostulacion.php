<?php

namespace App\Modules\ApplicantAdmission\Enums;

// Estado de la postulación.
enum EstadoPostulacion: string
{
    case PENDIENTE = 'PENDIENTE';
    case VERIFICADO = 'VERIFICADO';
    case RECHAZADO = 'RECHAZADO';
}
