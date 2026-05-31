<?php

namespace App\Modules\AcademicManagement\Services;

use App\Modules\AcademicManagement\DTOs\CreateGrupoDTO;
use App\Modules\AcademicManagement\DTOs\UpdateGrupoDTO;
use App\Modules\AcademicManagement\Models\Grupo;
use App\Modules\AcademicManagement\Repositories\GrupoRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

// Lógica de negocio de grupos.
class GrupoService
{
    public function __construct(private readonly GrupoRepository $grupos) {}

    public function list(?string $search, ?string $gestion, int $perPage = 15): LengthAwarePaginator
    {
        return $this->grupos->paginate($search, $gestion, $perPage);
    }

    public function create(CreateGrupoDTO $data): Grupo
    {
        return $this->grupos->create($data->toArray());
    }

    public function update(Grupo $grupo, UpdateGrupoDTO $data): Grupo
    {
        $grupo->update($data->toArray());

        return $grupo;
    }

    public function delete(Grupo $grupo): void
    {
        $grupo->delete();
    }
}
