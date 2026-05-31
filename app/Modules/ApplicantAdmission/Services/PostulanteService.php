<?php

namespace App\Modules\ApplicantAdmission\Services;

use App\Modules\ApplicantAdmission\DTOs\CreatePostulanteDTO;
use App\Modules\ApplicantAdmission\DTOs\UpdatePostulanteDTO;
use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\ApplicantAdmission\Repositories\PostulanteRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

// Lógica de negocio de postulantes.
class PostulanteService
{
    public function __construct(private readonly PostulanteRepository $postulantes) {}

    public function list(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return $this->postulantes->paginate($search, $perPage);
    }

    public function create(CreatePostulanteDTO $data): Postulante
    {
        return $this->postulantes->create($data->toArray());
    }

    public function update(Postulante $postulante, UpdatePostulanteDTO $data): Postulante
    {
        $postulante->update($data->toArray());

        return $postulante;
    }

    public function delete(Postulante $postulante): void
    {
        $postulante->delete();
    }
}
