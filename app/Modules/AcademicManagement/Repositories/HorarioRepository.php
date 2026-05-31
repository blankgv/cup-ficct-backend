<?php

namespace App\Modules\AcademicManagement\Repositories;

use App\Modules\AcademicManagement\Models\Horario;
use Illuminate\Database\Eloquent\Collection;

// Acceso a datos de horarios.
class HorarioRepository
{
    public function forGrupoMateria(int $grupoId, string $sigla): Collection
    {
        return Horario::query()
            ->where('grupo_id', $grupoId)
            ->where('materia_sigla', $sigla)
            ->orderBy('numero')
            ->get();
    }

    public function find(int $grupoId, string $sigla, int $numero): ?Horario
    {
        return Horario::query()
            ->where('grupo_id', $grupoId)
            ->where('materia_sigla', $sigla)
            ->where('numero', $numero)
            ->first();
    }

    public function nextNumero(int $grupoId, string $sigla): int
    {
        return (int) Horario::query()
            ->where('grupo_id', $grupoId)
            ->where('materia_sigla', $sigla)
            ->max('numero') + 1;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Horario
    {
        return Horario::create($attributes);
    }

    // ¿El grupo ya tiene una clase que se cruza ese día/hora?
    public function grupoOverlaps(int $grupoId, string $dia, string $inicio, string $fin, ?int $exceptNumero, ?string $exceptSigla): bool
    {
        return Horario::query()
            ->where('grupo_id', $grupoId)
            ->where('dia', $dia)
            ->where('hora_inicio', '<', $fin)
            ->where('hora_fin', '>', $inicio)
            ->when($exceptNumero !== null, fn ($q) => $q->whereNot(
                fn ($w) => $w->where('materia_sigla', $exceptSigla)->where('numero', $exceptNumero)
            ))
            ->exists();
    }

    // ¿El aula ya está ocupada ese día/hora?
    public function aulaOverlaps(string $moduloNumero, int $aulaNumero, string $dia, string $inicio, string $fin, ?array $except): bool
    {
        return Horario::query()
            ->where('aula_modulo_numero', $moduloNumero)
            ->where('aula_numero', $aulaNumero)
            ->where('dia', $dia)
            ->where('hora_inicio', '<', $fin)
            ->where('hora_fin', '>', $inicio)
            ->when($except !== null, fn ($q) => $q->whereNot(fn ($w) => $w
                ->where('grupo_id', $except['grupo_id'])
                ->where('materia_sigla', $except['materia_sigla'])
                ->where('numero', $except['numero'])
            ))
            ->exists();
    }
}
