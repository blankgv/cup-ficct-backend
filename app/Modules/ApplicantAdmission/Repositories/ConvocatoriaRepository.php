<?php

namespace App\Modules\ApplicantAdmission\Repositories;

use App\Modules\ApplicantAdmission\Models\Convocatoria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

// Acceso a datos de convocatorias.
class ConvocatoriaRepository
{
    public function paginate(?string $search, int $perPage): LengthAwarePaginator
    {
        return Convocatoria::query()
            ->when($search, fn ($q) => $q->where(fn ($w) => $w
                ->whereLike('nombre', "%{$search}%")
                ->orWhereLike('gestion', "%{$search}%")))
            ->orderByDesc('fecha_inicio')
            ->paginate($perPage);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Convocatoria
    {
        return Convocatoria::create($attributes);
    }
}
