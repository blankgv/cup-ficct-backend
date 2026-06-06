<?php

namespace App\Modules\Evaluation\Services;

use App\Modules\AcademicManagement\Models\Feriado;
use App\Modules\AcademicManagement\Models\Grupo;
use App\Modules\AcademicManagement\Models\GrupoMateria;
use App\Modules\AcademicManagement\Models\Materia;
use App\Modules\ApplicantAdmission\Models\Inscripcion;
use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\Evaluation\Enums\EstadoAsistencia;
use App\Modules\Evaluation\Enums\EstadoHabilitacion;
use App\Modules\Evaluation\Models\Asistencia;
use App\Modules\Evaluation\Repositories\AsistenciaRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// Lógica de asistencia: registro y cálculo del porcentaje + habilitación.
class AsistenciaService
{
    // Porcentaje mínimo de asistencia para estar habilitado.
    private const UMBRAL = 80;

    public function __construct(private readonly AsistenciaRepository $asistencias) {}

    // Registra/actualiza una asistencia individual.
    public function upsert(array $data): Asistencia
    {
        $inscripcion = $this->inscripcion($data['postulante_documento'], $data['convocatoria_id']);
        $this->garantizarMateriaEnGrupo($inscripcion->grupo_id, $data['materia_sigla']);

        return $this->asistencias->upsert($data);
    }

    /**
     * Carga masiva de una fecha para una materia del grupo.
     *
     * @param list<array{postulante_documento:string, estado:string}> $asistencias
     * @return array<string, mixed>
     */
    public function batch(Grupo $grupo, Materia $materia, string $fecha, array $asistencias): array
    {
        if ($grupo->convocatoria_id === null) {
            throw ValidationException::withMessages(['grupo' => 'El grupo no pertenece a una convocatoria.']);
        }

        $this->garantizarMateriaEnGrupo($grupo->id, $materia->sigla);

        $inscritos = Inscripcion::where('grupo_id', $grupo->id)->pluck('postulante_documento')->all();

        $guardadas = 0;
        $omitidas = [];

        DB::transaction(function () use ($asistencias, $inscritos, $grupo, $materia, $fecha, &$guardadas, &$omitidas) {
            foreach ($asistencias as $asistencia) {
                if (! in_array($asistencia['postulante_documento'], $inscritos, true)) {
                    $omitidas[] = $asistencia['postulante_documento'];

                    continue;
                }

                $this->asistencias->upsert([
                    'postulante_documento' => $asistencia['postulante_documento'],
                    'convocatoria_id' => $grupo->convocatoria_id,
                    'materia_sigla' => $materia->sigla,
                    'fecha' => $fecha,
                    'estado' => $asistencia['estado'],
                ]);

                $guardadas++;
            }
        });

        return ['guardadas' => $guardadas, 'omitidas' => count($omitidas), 'no_inscritos' => $omitidas];
    }

    /**
     * Reporte: % de asistencia por materia, % global y habilitación (≥ 80%).
     *
     * @return array<string, mixed>
     */
    public function reporte(Postulante $postulante, int $convocatoriaId): array
    {
        $inscripcion = $this->inscripcion($postulante->documento, $convocatoriaId);
        $grupo = Grupo::with(['materias', 'periodo'])->findOrFail($inscripcion->grupo_id);
        $porMateria = $this->asistencias->forPostulante($postulante->documento, $convocatoriaId)->groupBy('materia_sigla');

        // Total esperado por el calendario de clases (L–V − feriados); null → se usa lo registrado.
        $esperadas = $this->sesionesEsperadas($grupo);

        $materias = [];
        $totalGlobal = 0;
        $asistidosGlobal = 0;

        foreach ($grupo->materias as $materia) {
            $registros = $porMateria->get($materia->sigla, collect());
            // Justificado cuenta como asistido.
            $asistidosMateria = $registros->filter(
                fn (Asistencia $a) => in_array($a->estado, [EstadoAsistencia::PRESENTE, EstadoAsistencia::JUSTIFICADO], true)
            )->count();

            $total = $esperadas ?? $registros->count();
            $asistidos = min($asistidosMateria, $total);
            $porcentaje = $total > 0 ? round($asistidos / $total * 100, 2) : 0.0;

            $totalGlobal += $total;
            $asistidosGlobal += $asistidos;

            $materias[] = [
                'sigla' => $materia->sigla,
                'nombre' => $materia->nombre,
                'total' => $total,
                'asistidos' => $asistidos,
                'porcentaje' => $porcentaje,
            ];
        }

        $porcentajeGlobal = $totalGlobal > 0 ? round($asistidosGlobal / $totalGlobal * 100, 2) : 0.0;
        $estado = $porcentajeGlobal >= self::UMBRAL ? EstadoHabilitacion::HABILITADO : EstadoHabilitacion::INHABILITADO;

        return [
            'postulante_documento' => $postulante->documento,
            'convocatoria_id' => $convocatoriaId,
            'grupo_id' => $grupo->id,
            'base_calculo' => $esperadas !== null ? 'calendario' : 'registros',
            'sesiones_esperadas' => $esperadas,
            'materias' => $materias,
            'porcentaje_global' => $porcentajeGlobal,
            'estado' => $estado->value,
        ];
    }

    // Sesiones esperadas por el calendario del periodo del grupo: días L–V en el rango, menos feriados.
    private function sesionesEsperadas(Grupo $grupo): ?int
    {
        $periodo = $grupo->periodo;

        if ($periodo === null) {
            return null;
        }

        $inicio = $periodo->fecha_inicio_clases;
        $fin = $periodo->fecha_fin_clases;

        $feriados = array_flip(
            Feriado::where('gestion', $periodo->gestion)
                ->whereBetween('fecha', [$inicio->format('Y-m-d'), $fin->format('Y-m-d')])
                ->pluck('fecha')
                ->map(fn ($f) => substr((string) $f, 0, 10))
                ->all()
        );

        $sesiones = 0;
        for ($dia = $inicio->copy(); $dia->lte($fin); $dia->addDay()) {
            if ($dia->isWeekday() && ! isset($feriados[$dia->format('Y-m-d')])) {
                $sesiones++;
            }
        }

        return $sesiones;
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
