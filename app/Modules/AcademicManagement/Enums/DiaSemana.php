<?php

namespace App\Modules\AcademicManagement\Enums;

// Días de clase (lunes a sábado).
enum DiaSemana: string
{
    case LUNES = 'LUNES';
    case MARTES = 'MARTES';
    case MIERCOLES = 'MIERCOLES';
    case JUEVES = 'JUEVES';
    case VIERNES = 'VIERNES';
    case SABADO = 'SABADO';
}
