<?php

namespace App\Modules\ApplicantAdmission\Repositories;

use App\Modules\ApplicantAdmission\Models\Postulante;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

// Acceso a datos de postulantes.
class PostulanteRepository
{
    public function paginate(?string $search, int $perPage): LengthAwarePaginator
    {
        return Postulante::query()
            ->when($search, function ($q) use ($search) {
                $q->where(fn ($w) => $w
                    ->whereLike('documento', "%{$search}%")
                    ->orWhereLike('nombres', "%{$search}%")
                    ->orWhereLike('apellidos', "%{$search}%"));
            })
            ->orderBy('apellidos')
            ->paginate($perPage);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Postulante
    {
        return Postulante::create($attributes);
    }
}
