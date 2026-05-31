<?php

namespace App\Modules\AcademicManagement\Enums;

// Tipos de aula (lista fija).
enum AulaTipo: string
{
    case COMUN = 'COMUN';
    case LABORATORIO = 'LABORATORIO';
    case AUDITORIO = 'AUDITORIO';
}
