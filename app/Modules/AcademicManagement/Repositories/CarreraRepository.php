<?php

namespace App\Modules\AcademicManagement\Repositories;

use App\Modules\AcademicManagement\Models\Carrera;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

// Acceso a datos de carreras.
class CarreraRepository
{
    public function paginate(?string $search, ?string $facultad, int $perPage): LengthAwarePaginator
    {
        return Carrera::query()
            ->when($search, fn ($q) => $q->where(fn ($w) => $w
                ->whereLike('codigo', "%{$search}%")
                ->orWhereLike('nombre', "%{$search}%")))
            ->when($facultad, fn ($q) => $q->where('facultad_codigo', $facultad))
            ->orderBy('codigo')
            ->paginate($perPage);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Carrera
    {
        return Carrera::create($attributes);
    }
}
