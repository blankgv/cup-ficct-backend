<?php

namespace App\Modules\AcademicManagement\Services;

use App\Modules\AcademicManagement\DTOs\CreateDocenteDTO;
use App\Modules\AcademicManagement\DTOs\UpdateDocenteDTO;
use App\Modules\AcademicManagement\Models\Docente;
use App\Modules\AcademicManagement\Repositories\DocenteRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

// Lógica de negocio de docentes.
class DocenteService
{
    public function __construct(private readonly DocenteRepository $docentes) {}

    public function list(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return $this->docentes->paginate($search, $perPage);
    }

    public function create(CreateDocenteDTO $data): Docente
    {
        return $this->docentes->create($data->toArray());
    }

    public function update(Docente $docente, UpdateDocenteDTO $data): Docente
    {
        $docente->update($data->toArray());

        return $docente;
    }

    public function delete(Docente $docente): void
    {
        $docente->delete();
    }
}
