<?php

namespace App\Modules\AcademicManagement\Repositories;

use App\Modules\AcademicManagement\Models\Docente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

// Acceso a datos de docentes.
class DocenteRepository
{
    public function paginate(?string $search, int $perPage): LengthAwarePaginator
    {
        return Docente::query()
            ->when($search, function ($q) use ($search) {
                $q->where(fn ($w) => $w
                    ->whereLike('ci', "%{$search}%")
                    ->orWhereLike('nombres', "%{$search}%")
                    ->orWhereLike('apellidos', "%{$search}%"));
            })
            ->orderBy('apellidos')
            ->paginate($perPage);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Docente
    {
        return Docente::create($attributes);
    }
}
