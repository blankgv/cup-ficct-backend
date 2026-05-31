<?php

namespace App\Modules\ApplicantAdmission\Services;

use App\Modules\ApplicantAdmission\DTOs\CreateConvocatoriaDTO;
use App\Modules\ApplicantAdmission\DTOs\UpdateConvocatoriaDTO;
use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\ApplicantAdmission\Repositories\ConvocatoriaRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

// Lógica de negocio de convocatorias y sus cupos por carrera.
class ConvocatoriaService
{
    public function __construct(private readonly ConvocatoriaRepository $convocatorias) {}

    public function list(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return $this->convocatorias->paginate($search, $perPage);
    }

    public function create(CreateConvocatoriaDTO $data): Convocatoria
    {
        return $this->convocatorias->create($data->toArray());
    }

    public function update(Convocatoria $convocatoria, UpdateConvocatoriaDTO $data): Convocatoria
    {
        $convocatoria->update($data->toArray());

        return $convocatoria;
    }

    public function delete(Convocatoria $convocatoria): void
    {
        $convocatoria->delete();
    }

    // Carreras con cupos de la convocatoria.
    public function listCupos(Convocatoria $convocatoria): Collection
    {
        return $convocatoria->carreras()->orderBy('codigo')->get();
    }

    // Fija (crea o actualiza) los cupos de una carrera en la convocatoria.
    public function setCupos(Convocatoria $convocatoria, string $carreraCodigo, int $cupos): Collection
    {
        if ($convocatoria->carreras()->where('carreras.codigo', $carreraCodigo)->exists()) {
            $convocatoria->carreras()->updateExistingPivot($carreraCodigo, ['cupos' => $cupos]);
        } else {
            $convocatoria->carreras()->attach($carreraCodigo, ['cupos' => $cupos]);
        }

        return $this->listCupos($convocatoria);
    }

    public function removeCarrera(Convocatoria $convocatoria, string $carreraCodigo): void
    {
        $convocatoria->carreras()->detach($carreraCodigo);
    }
}
