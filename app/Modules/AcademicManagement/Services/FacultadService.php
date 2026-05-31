<?php

namespace App\Modules\AcademicManagement\Services;

use App\Modules\AcademicManagement\DTOs\CreateFacultadDTO;
use App\Modules\AcademicManagement\DTOs\UpdateFacultadDTO;
use App\Modules\AcademicManagement\Models\Facultad;
use App\Modules\AcademicManagement\Repositories\FacultadRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

// Lógica de negocio de facultades.
class FacultadService
{
    public function __construct(private readonly FacultadRepository $facultades) {}

    public function list(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return $this->facultades->paginate($search, $perPage);
    }

    public function create(CreateFacultadDTO $data): Facultad
    {
        return $this->facultades->create($data->toArray());
    }

    public function update(Facultad $facultad, UpdateFacultadDTO $data): Facultad
    {
        $facultad->update($data->toArray());

        return $facultad;
    }

    public function delete(Facultad $facultad): void
    {
        $facultad->delete();
    }
}
