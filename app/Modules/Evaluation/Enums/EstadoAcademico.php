<?php

namespace App\Modules\Evaluation\Enums;

// Resultado académico según el promedio final.
enum EstadoAcademico: string
{
    case APROBADO = 'APROBADO';
    case REPROBADO = 'REPROBADO';
}
