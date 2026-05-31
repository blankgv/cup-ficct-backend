<?php

namespace App\Modules\AcademicManagement\Repositories;

use App\Modules\AcademicManagement\Models\Materia;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

// Acceso a datos de materias.
class MateriaRepository
{
    public function paginate(?string $search, int $perPage): LengthAwarePaginator
    {
        return Materia::query()
            ->when($search, function ($q) use ($search) {
                $q->where(fn ($w) => $w->whereLike('nombre', "%{$search}%")->orWhereLike('sigla', "%{$search}%"));
            })
            ->orderBy('nombre')
            ->paginate($perPage);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Materia
    {
        return Materia::create($attributes);
    }
}
