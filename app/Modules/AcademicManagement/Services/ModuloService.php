<?php

namespace App\Modules\AcademicManagement\Services;

use App\Modules\AcademicManagement\DTOs\CreateModuloDTO;
use App\Modules\AcademicManagement\DTOs\UpdateModuloDTO;
use App\Modules\AcademicManagement\Models\Modulo;
use App\Modules\AcademicManagement\Repositories\ModuloRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

// Lógica de negocio de módulos.
class ModuloService
{
    public function __construct(private readonly ModuloRepository $modulos) {}

    public function list(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return $this->modulos->paginate($search, $perPage);
    }

    public function create(CreateModuloDTO $data): Modulo
    {
        return $this->modulos->create($data->toArray());
    }

    public function update(Modulo $modulo, UpdateModuloDTO $data): Modulo
    {
        $modulo->update($data->toArray());

        return $modulo;
    }

    public function delete(Modulo $modulo): void
    {
        $modulo->delete();
    }
}
