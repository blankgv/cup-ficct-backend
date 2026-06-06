<?php

namespace App\Modules\Evaluation\Services;

use App\Modules\AcademicManagement\Models\Grupo;
use App\Modules\AcademicManagement\Models\GrupoMateria;
use App\Modules\AcademicManagement\Models\Materia;
use App\Modules\ApplicantAdmission\Models\Inscripcion;
use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\Evaluation\Enums\EstadoAcademico;
use App\Modules\Evaluation\Models\Nota;
use App\Modules\Evaluation\Repositories\NotaRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// Lógica de notas: carga y cálculo del promedio ponderado.
class NotaService
{
    // Promedio mínimo para aprobar.
    private const APROBACION = 60;

    public function __construct(private readonly NotaRepository $notas) {}

    // Carga/actualiza una nota individual.
    public function upsert(array $data): Nota
    {
        $inscripcion = $this->inscripcion($data['postulante_documento'], $data['convocatoria_id']);
        $this->garantizarMateriaEnGrupo($inscripcion->grupo_id, $data['materia_sigla']);

        return $this->notas->upsert($data);
    }

    /**
     * Carga masiva de un examen para una materia del grupo.
     *
     * @param list<array{postulante_documento:string, valor:float|string}> $notas
     * @return array<string, mixed>
     */
    public function batch(Grupo $grupo, Materia $materia, int $numero, array $notas): array
    {
        if ($grupo->convocatoria_id === null) {
            throw ValidationException::withMessages(['grupo' => 'El grupo no pertenece a una convocatoria.']);
        }

        $this->garantizarMateriaEnGrupo($grupo->id, $materia->sigla);

        // Documentos realmente inscritos en el grupo.
        $inscritos = Inscripcion::where('grupo_id', $grupo->id)->pluck('postulante_documento')->all();

        $guardadas = 0;
        $omitidas = [];

        DB::transaction(function () use ($notas, $inscritos, $grupo, $materia, $numero, &$guardadas, &$omitidas) {
            foreach ($notas as $nota) {
                if (! in_array($nota['postulante_documento'], $inscritos, true)) {
                    $omitidas[] = $nota['postulante_documento'];

                    continue;
                }

                $this->notas->upsert([
                    'postulante_documento' => $nota['postulante_documento'],
                    'convocatoria_id' => $grupo->convocatoria_id,
                    'materia_sigla' => $materia->sigla,
                    'numero' => $numero,
                    'valor' => $nota['valor'],
                ]);

                $guardadas++;
            }
        });

        return ['guardadas' => $guardadas, 'omitidas' => count($omitidas), 'no_inscritos' => $omitidas];
    }

    /**
     * Boletín: notas por materia, promedio por materia, promedio final ponderado y estado.
     *
     * @return array<string, mixed>
     */
    public function boletin(Postulante $postulante, int $convocatoriaId): array
    {
        $inscripcion = $this->inscripcion($postulante->documento, $convocatoriaId);
        $grupo = Grupo::with('materias')->findOrFail($inscripcion->grupo_id);
        $porMateria = $this->notas->forPostulante($postulante->documento, $convocatoriaId)->groupBy('materia_sigla');

        $materias = [];
        $final = 0.0;

        foreach ($grupo->materias as $materia) {
            $examenes = $porMateria->get($materia->sigla, collect());
            $promedio = $examenes->isNotEmpty() ? round((float) $examenes->avg('valor'), 2) : null;
            $final += ($promedio ?? 0) * (float) $materia->peso;

            $materias[] = [
                'sigla' => $materia->sigla,
                'nombre' => $materia->nombre,
                'peso' => (float) $materia->peso,
                'examenes' => $examenes->sortBy('numero')->map(fn (Nota $n) => [
                    'numero' => $n->numero,
                    'valor' => (float) $n->valor,
                ])->values(),
                'promedio' => $promedio,
            ];
        }

        $final = round($final, 2);

        return [
            'postulante_documento' => $postulante->documento,
            'convocatoria_id' => $convocatoriaId,
            'grupo_id' => $grupo->id,
            'materias' => $materias,
            'promedio_final' => $final,
            'estado' => ($final >= self::APROBACION ? EstadoAcademico::APROBADO : EstadoAcademico::REPROBADO)->value,
        ];
    }

    // El postulante debe estar inscrito en la convocatoria.
    private function inscripcion(string $documento, int $convocatoriaId): Inscripcion
    {
        $inscripcion = Inscripcion::where('postulante_documento', $documento)
            ->where('convocatoria_id', $convocatoriaId)
            ->first();

        if ($inscripcion === null) {
            throw ValidationException::withMessages([
                'postulante_documento' => 'El postulante no está inscrito en la convocatoria.',
            ]);
        }

        return $inscripcion;
    }

    // La materia debe pertenecer al grupo del inscrito.
    private function garantizarMateriaEnGrupo(int $grupoId, string $materiaSigla): void
    {
        $existe = GrupoMateria::where('grupo_id', $grupoId)
            ->where('materia_sigla', $materiaSigla)
            ->exists();

        if (! $existe) {
            throw ValidationException::withMessages([
                'materia_sigla' => 'La materia no pertenece al grupo del postulante.',
            ]);
        }
    }
}
