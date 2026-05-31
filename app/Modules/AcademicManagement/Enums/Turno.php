<?php

namespace App\Modules\AcademicManagement\Enums;

// Turnos del grupo (lista fija).
enum Turno: string
{
    case MANANA = 'MANANA';
    case TARDE = 'TARDE';
    case NOCHE = 'NOCHE';
}
