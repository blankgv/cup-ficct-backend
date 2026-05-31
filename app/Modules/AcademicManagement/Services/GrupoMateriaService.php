<?php

namespace App\Modules\AcademicManagement\Services;

use App\Modules\AcademicManagement\Models\Grupo;
use App\Modules\AcademicManagement\Models\GrupoMateria;
use Illuminate\Database\Eloquent\Collection;

// Gestión de las materias de un grupo (muchos a muchos) y su docente.
class GrupoMateriaService
{
    public function list(Grupo $grupo): Collection
    {
        return $grupo->materias()->orderBy('sigla')->get();
    }

    // Asigna un docente al par grupo-materia.
    public function assignDocente(Grupo $grupo, string $sigla, string $ci): GrupoMateria
    {
        $par = $this->resolvePar($grupo, $sigla);
        $par->update(['docente_ci' => $ci]);

        return $par->load('docente');
    }

    // Quita el docente del par grupo-materia.
    public function removeDocente(Grupo $grupo, string $sigla): GrupoMateria
    {
        $par = $this->resolvePar($grupo, $sigla);
        $par->update(['docente_ci' => null]);

        return $par;
    }

    private function resolvePar(Grupo $grupo, string $sigla): GrupoMateria
    {
        return GrupoMateria::query()
            ->where('grupo_id', $grupo->id)
            ->where('materia_sigla', $sigla)
            ->first() ?? abort(404, 'El grupo no cursa esa materia.');
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
