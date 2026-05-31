<?php

namespace App\Modules\AcademicManagement\Services;

use App\Modules\AcademicManagement\DTOs\CreateAulaDTO;
use App\Modules\AcademicManagement\DTOs\UpdateAulaDTO;
use App\Modules\AcademicManagement\Models\Aula;
use App\Modules\AcademicManagement\Repositories\AulaRepository;
use Illuminate\Database\Eloquent\Collection;

// Lógica de negocio de aulas.
class AulaService
{
    public function __construct(private readonly AulaRepository $aulas) {}

    public function listForModulo(string $moduloNumero): Collection
    {
        return $this->aulas->forModulo($moduloNumero);
    }

    public function find(string $moduloNumero, int $numero): ?Aula
    {
        return $this->aulas->find($moduloNumero, $numero);
    }

    public function create(CreateAulaDTO $data): Aula
    {
        return $this->aulas->create($data->toArray());
    }

    public function update(Aula $aula, UpdateAulaDTO $data): Aula
    {
        $aula->update($data->toArray());

        return $aula;
    }

    public function delete(Aula $aula): void
    {
        $aula->delete();
    }
}
