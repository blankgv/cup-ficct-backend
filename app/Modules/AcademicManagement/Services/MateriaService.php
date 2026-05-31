<?php

namespace App\Modules\AcademicManagement\Services;

use App\Modules\AcademicManagement\DTOs\CreateMateriaDTO;
use App\Modules\AcademicManagement\DTOs\UpdateMateriaDTO;
use App\Modules\AcademicManagement\Models\Materia;
use App\Modules\AcademicManagement\Repositories\MateriaRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

// Lógica de negocio de materias.
class MateriaService
{
    public function __construct(private readonly MateriaRepository $materias) {}

    public function list(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return $this->materias->paginate($search, $perPage);
    }

    public function create(CreateMateriaDTO $data): Materia
    {
        return $this->materias->create($data->toArray());
    }

    public function update(Materia $materia, UpdateMateriaDTO $data): Materia
    {
        $materia->update($data->toArray());

        return $materia;
    }

    public function delete(Materia $materia): void
    {
        $materia->delete();
    }
}
