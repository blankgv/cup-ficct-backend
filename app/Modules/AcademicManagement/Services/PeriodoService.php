<?php

namespace App\Modules\AcademicManagement\Services;

use App\Modules\AcademicManagement\DTOs\CreatePeriodoDTO;
use App\Modules\AcademicManagement\DTOs\UpdatePeriodoDTO;
use App\Modules\AcademicManagement\Models\Periodo;
use App\Modules\AcademicManagement\Repositories\PeriodoRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

// Lógica de negocio de periodos.
class PeriodoService
{
    public function __construct(private readonly PeriodoRepository $periodos) {}

    public function list(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return $this->periodos->paginate($search, $perPage);
    }

    public function create(CreatePeriodoDTO $data): Periodo
    {
        return $this->periodos->create($data->toArray());
    }

    public function update(Periodo $periodo, UpdatePeriodoDTO $data): Periodo
    {
        $periodo->update($data->toArray());

        return $periodo;
    }

    public function delete(Periodo $periodo): void
    {
        $periodo->delete();
    }
}
