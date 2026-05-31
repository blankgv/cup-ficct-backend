<?php

namespace App\Modules\AcademicManagement\Services;

use App\Modules\AcademicManagement\Models\Grupo;
use Illuminate\Database\Eloquent\Collection;

// Gestión de las materias de un grupo (muchos a muchos).
class GrupoMateriaService
{
    public function list(Grupo $grupo): Collection
    {
        return $grupo->materias()->orderBy('sigla')->get();
    }

    /**
     * Reemplaza todas las materias del grupo.
     *
     * @param list<string> $siglas
     */
    public function sync(Grupo $grupo, array $siglas): Collection
    {
        $grupo->materias()->sync($siglas);

        return $this->list($grupo);
    }

    // Agrega una materia sin quitar las existentes.
    public function attach(Grupo $grupo, string $sigla): Collection
    {
        $grupo->materias()->syncWithoutDetaching([$sigla]);

        return $this->list($grupo);
    }

    public function detach(Grupo $grupo, string $sigla): void
    {
        $grupo->materias()->detach($sigla);
    }
}
