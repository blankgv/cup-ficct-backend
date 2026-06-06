<?php

namespace App\Modules\Evaluation\Enums;

// Estado de una asistencia.
enum EstadoAsistencia: string
{
    case PRESENTE = 'PRESENTE';
    case AUSENTE = 'AUSENTE';
    case JUSTIFICADO = 'JUSTIFICADO';
}
