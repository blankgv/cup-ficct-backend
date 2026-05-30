<?php

namespace App\Modules\Authentication\Authorization;

// Roles del sistema (actores).
final class Role
{
    public const ADMINISTRADOR = 'ADMINISTRADOR';
    public const COORDINADOR = 'COORDINADOR';
    public const DOCENTE = 'DOCENTE';
    public const POSTULANTE = 'POSTULANTE';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [self::ADMINISTRADOR, self::COORDINADOR, self::DOCENTE, self::POSTULANTE];
    }
}
