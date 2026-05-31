<?php

namespace App\Modules\AcademicManagement\Repositories;

use App\Modules\AcademicManagement\Models\Facultad;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

// Acceso a datos de facultades.
class FacultadRepository
{
    public function paginate(?string $search, int $perPage): LengthAwarePaginator
    {
        return Facultad::query()
            ->when($search, fn ($q) => $q->where(fn ($w) => $w
                ->whereLike('codigo', "%{$search}%")
                ->orWhereLike('nombre', "%{$search}%")))
            ->orderBy('nombre')
            ->paginate($perPage);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Facultad
    {
        return Facultad::create($attributes);
    }
}
