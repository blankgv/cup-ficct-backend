<?php

namespace App\Modules\AcademicManagement\Services;

use App\Modules\AcademicManagement\DTOs\CreateCarreraDTO;
use App\Modules\AcademicManagement\DTOs\UpdateCarreraDTO;
use App\Modules\AcademicManagement\Models\Carrera;
use App\Modules\AcademicManagement\Repositories\CarreraRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

// Lógica de negocio de carreras.
class CarreraService
{
    public function __construct(private readonly CarreraRepository $carreras) {}

    public function list(?string $search, ?string $facultad, int $perPage = 15): LengthAwarePaginator
    {
        return $this->carreras->paginate($search, $facultad, $perPage);
    }

    public function create(CreateCarreraDTO $data): Carrera
    {
        return $this->carreras->create($data->toArray());
    }

    public function update(Carrera $carrera, UpdateCarreraDTO $data): Carrera
    {
        $carrera->update($data->toArray());

        return $carrera;
    }

    public function delete(Carrera $carrera): void
    {
        $carrera->delete();
    }
}
