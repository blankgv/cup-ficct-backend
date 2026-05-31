<?php

namespace App\Modules\ApplicantAdmission\Repositories;

use App\Modules\ApplicantAdmission\Models\Postulacion;
use Illuminate\Database\Eloquent\Collection;

// Acceso a datos de postulaciones.
class PostulacionRepository
{
    public function forPostulante(string $documento): Collection
    {
        return Postulacion::query()
            ->where('postulante_documento', $documento)
            ->orderByDesc('convocatoria_id')
            ->get();
    }

    public function find(string $documento, int $convocatoriaId): ?Postulacion
    {
        return Postulacion::query()
            ->where('postulante_documento', $documento)
            ->where('convocatoria_id', $convocatoriaId)
            ->first();
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Postulacion
    {
        return Postulacion::create($attributes);
    }
}
