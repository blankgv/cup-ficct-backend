<?php

namespace App\Modules\AcademicManagement\Repositories;

use App\Modules\AcademicManagement\Models\Feriado;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

// Acceso a datos de feriados.
class FeriadoRepository
{
    public function paginate(?string $gestion, int $perPage): LengthAwarePaginator
    {
        return Feriado::query()
            ->when($gestion, fn ($q) => $q->where('gestion', $gestion))
            ->orderBy('fecha')
            ->paginate($perPage);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Feriado
    {
        return Feriado::create($attributes);
    }

    // Crea o actualiza un feriado por su fecha.
    public function upsert(string $fecha, string $descripcion, string $gestion): Feriado
    {
        return Feriado::updateOrCreate(
            ['fecha' => $fecha],
            ['descripcion' => $descripcion, 'gestion' => $gestion],
        );
    }
}
