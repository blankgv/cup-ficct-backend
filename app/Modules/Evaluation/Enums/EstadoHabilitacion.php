<?php

namespace App\Modules\Evaluation\Enums;

// Habilitación según el porcentaje de asistencia.
enum EstadoHabilitacion: string
{
    case HABILITADO = 'HABILITADO';
    case INHABILITADO = 'INHABILITADO';
}
