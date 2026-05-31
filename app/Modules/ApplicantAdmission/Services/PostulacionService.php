<?php

namespace App\Modules\ApplicantAdmission\Services;

use App\Modules\ApplicantAdmission\DTOs\CreatePostulacionDTO;
use App\Modules\ApplicantAdmission\Enums\EstadoConvocatoria;
use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\ApplicantAdmission\Models\Postulacion;
use App\Modules\ApplicantAdmission\Repositories\PostulacionRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

// Lógica de postulaciones. No descuenta cupos (solo registra la postulación).
class PostulacionService
{
    public function __construct(private readonly PostulacionRepository $postulaciones) {}

    public function listForPostulante(string $documento): Collection
    {
        return $this->postulaciones->forPostulante($documento);
    }

    public function find(string $documento, int $convocatoriaId): ?Postulacion
    {
        return $this->postulaciones->find($documento, $convocatoriaId);
    }

    public function create(CreatePostulacionDTO $data): Postulacion
    {
        $convocatoria = Convocatoria::findOrFail($data->convocatoriaId);

        if ($convocatoria->estado !== EstadoConvocatoria::ABIERTA) {
            throw ValidationException::withMessages([
                'convocatoria_id' => 'La convocatoria no está abierta.',
            ]);
        }

        if ($this->postulaciones->find($data->postulanteDocumento, $data->convocatoriaId) !== null) {
            throw ValidationException::withMessages([
                'convocatoria_id' => 'El postulante ya postuló en esta convocatoria.',
            ]);
        }

        return $this->postulaciones->create($data->toArray());
    }

    public function delete(Postulacion $postulacion): void
    {
        $postulacion->delete();
    }
}
