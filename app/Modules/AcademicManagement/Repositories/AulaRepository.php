<?php

namespace App\Modules\AcademicManagement\Repositories;

use App\Modules\AcademicManagement\Models\Aula;
use Illuminate\Database\Eloquent\Collection;

// Acceso a datos de aulas.
class AulaRepository
{
    /**
     * Aulas de un módulo.
     */
    public function forModulo(string $moduloNumero): Collection
    {
        return Aula::query()
            ->where('modulo_numero', $moduloNumero)
            ->orderBy('numero')
            ->get();
    }

    public function find(string $moduloNumero, int $numero): ?Aula
    {
        return Aula::query()
            ->where('modulo_numero', $moduloNumero)
            ->where('numero', $numero)
            ->first();
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Aula
    {
        return Aula::create($attributes);
    }
}
