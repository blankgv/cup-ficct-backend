<?php

namespace App\Modules\AcademicManagement\Repositories;

use App\Modules\AcademicManagement\Models\Modulo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

// Acceso a datos de módulos.
class ModuloRepository
{
    public function paginate(?string $search, int $perPage): LengthAwarePaginator
    {
        return Modulo::query()
            ->when($search, function ($q) use ($search) {
                $q->where(fn ($w) => $w->whereLike('numero', "%{$search}%")->orWhereLike('nombre', "%{$search}%"));
            })
            ->orderBy('numero')
            ->paginate($perPage);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Modulo
    {
        return Modulo::create($attributes);
    }
}
