<?php

namespace App\Modules\Evaluation\Repositories;

use App\Modules\Evaluation\Models\Nota;
use Illuminate\Database\Eloquent\Collection;

// Acceso a datos de notas.
class NotaRepository
{
    /**
     * Crea o actualiza una nota por su clave compuesta.
     *
     * @param array{postulante_documento:string, convocatoria_id:int, materia_sigla:string, numero:int, valor:float|string} $data
     */
    public function upsert(array $data): Nota
    {
        return Nota::updateOrCreate(
            [
                'postulante_documento' => $data['postulante_documento'],
                'convocatoria_id' => $data['convocatoria_id'],
                'materia_sigla' => $data['materia_sigla'],
                'numero' => $data['numero'],
            ],
            ['valor' => $data['valor']],
        );
    }

    public function forPostulante(string $documento, int $convocatoriaId): Collection
    {
        return Nota::query()
            ->where('postulante_documento', $documento)
            ->where('convocatoria_id', $convocatoriaId)
            ->orderBy('materia_sigla')
            ->orderBy('numero')
            ->get();
    }
}
