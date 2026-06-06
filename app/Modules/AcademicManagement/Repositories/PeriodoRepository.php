<?php

namespace App\Modules\AcademicManagement\Repositories;

use App\Modules\AcademicManagement\Models\Periodo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

// Acceso a datos de periodos.
class PeriodoRepository
{
    public function paginate(?string $search, int $perPage): LengthAwarePaginator
    {
        return Periodo::query()
            ->when($search, function ($q) use ($search) {
                $q->where(fn ($w) => $w->whereLike('codigo', "%{$search}%")->orWhereLike('gestion', "%{$search}%"));
            })
            ->orderByDesc('fecha_inicio_clases')
            ->paginate($perPage);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Periodo
    {
        return Periodo::create($attributes);
    }
}
