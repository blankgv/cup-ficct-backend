<?php

namespace App\Modules\Evaluation\Repositories;

use App\Modules\Evaluation\Models\Asistencia;
use Illuminate\Database\Eloquent\Collection;

// Acceso a datos de asistencias.
class AsistenciaRepository
{
    /**
     * Crea o actualiza una asistencia por su clave compuesta.
     * Se evita updateOrCreate: con `fecha` en la PK, el UPDATE de Eloquent bindea
     * la fecha como datetime y no coincide con el valor guardado.
     *
     * @param array{postulante_documento:string, convocatoria_id:int, materia_sigla:string, fecha:string, estado:string} $data
     */
    public function upsert(array $data): Asistencia
    {
        $claves = [
            'postulante_documento' => $data['postulante_documento'],
            'convocatoria_id' => $data['convocatoria_id'],
            'materia_sigla' => $data['materia_sigla'],
            'fecha' => $data['fecha'],
        ];

        if (Asistencia::where($claves)->exists()) {
            Asistencia::where($claves)->update(['estado' => $data['estado']]);
        } else {
            Asistencia::create($data);
        }

        return Asistencia::where($claves)->firstOrFail();
    }

    public function forPostulante(string $documento, int $convocatoriaId): Collection
    {
        return Asistencia::query()
            ->where('postulante_documento', $documento)
            ->where('convocatoria_id', $convocatoriaId)
            ->orderBy('materia_sigla')
            ->orderBy('fecha')
            ->get();
    }
}
