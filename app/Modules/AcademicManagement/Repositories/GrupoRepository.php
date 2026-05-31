<?php

namespace App\Modules\AcademicManagement\Repositories;

use App\Modules\AcademicManagement\Models\Grupo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

// Acceso a datos de grupos.
class GrupoRepository
{
    public function paginate(?string $search, ?string $gestion, int $perPage): LengthAwarePaginator
    {
        return Grupo::query()
            ->when($search, fn ($q) => $q->whereLike('codigo', "%{$search}%"))
            ->when($gestion, fn ($q) => $q->where('gestion', $gestion))
            ->orderBy('gestion')
            ->orderBy('codigo')
            ->paginate($perPage);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Grupo
    {
        return Grupo::create($attributes);
    }
}
