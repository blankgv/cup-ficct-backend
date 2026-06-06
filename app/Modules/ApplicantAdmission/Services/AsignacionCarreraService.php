<?php

namespace App\Modules\ApplicantAdmission\Services;

use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\ApplicantAdmission\Models\Inscripcion;
use App\Modules\ApplicantAdmission\Models\Postulacion;
use App\Modules\Evaluation\Enums\EstadoHabilitacion;
use App\Modules\Evaluation\Services\AsistenciaService;
use App\Modules\Evaluation\Services\NotaService;
use App\Modules\Payments\Enums\EstadoPago;
use App\Modules\Payments\Models\Pago;
use Illuminate\Support\Facades\DB;

// Asigna la carrera definitiva a los inscritos aprobados, por nota y cupos.
class AsignacionCarreraService
{
    // Promedio mínimo para aprobar el CUP.
    private const APROBACION = 60;

    public function __construct(
        private readonly NotaService $notas,
        private readonly AsistenciaService $asistencias,
    ) {}

    /**
     * Regenera la asignación de carreras de la convocatoria.
     *
     * @return array<string, mixed>
     */
    public function generar(Convocatoria $convocatoria): array
    {
        return DB::transaction(function () use ($convocatoria) {
            // Reinicia las asignaciones previas (idempotencia por regeneración).
            Inscripcion::where('convocatoria_id', $convocatoria->id)->update(['carrera_asignada_codigo' => null]);

            // Cupos ofertados por carrera (capacidad; no se mutan en BD).
            $cupos = [];
            foreach ($convocatoria->carreras as $carrera) {
                $cupos[$carrera->codigo] = (int) $carrera->pivot->cupos;
            }

            $postulaciones = Postulacion::where('convocatoria_id', $convocatoria->id)->get()->keyBy('postulante_documento');
            $primerPago = $this->primerPagoPorPostulante($convocatoria->id);
            $elegibles = $this->elegibles($convocatoria, $postulaciones, $primerPago);

            $asignados = 0;
            $sinCupo = 0;
            $porCarrera = [];

            foreach ($elegibles as $e) {
                $carrera = match (true) {
                    ($cupos[$e['primera']] ?? 0) > 0 => $e['primera'],
                    ($cupos[$e['segunda']] ?? 0) > 0 => $e['segunda'],
                    default => null,
                };

                if ($carrera === null) {
                    $sinCupo++;

                    continue;
                }

                $cupos[$carrera]--;
                Inscripcion::where('convocatoria_id', $convocatoria->id)
                    ->where('postulante_documento', $e['documento'])
                    ->update(['carrera_asignada_codigo' => $carrera]);

                $asignados++;
                $porCarrera[$carrera] = ($porCarrera[$carrera] ?? 0) + 1;
            }

            return [
                'elegibles' => count($elegibles),
                'asignados' => $asignados,
                'sin_cupo' => $sinCupo,
                'por_carrera' => $porCarrera,
            ];
        });
    }

    /**
     * Inscritos aprobados (promedio ≥ 60) y habilitados (asistencia ≥ 80%),
     * ordenados por promedio desc y, a igualdad, por quien pagó primero.
     *
     * @param \Illuminate\Support\Collection<string, Postulacion> $postulaciones
     * @param array<string, string> $primerPago
     * @return list<array<string, mixed>>
     */
    private function elegibles(Convocatoria $convocatoria, $postulaciones, array $primerPago): array
    {
        $elegibles = [];

        $inscripciones = Inscripcion::with('postulante')->where('convocatoria_id', $convocatoria->id)->get();

        foreach ($inscripciones as $inscripcion) {
            $postulante = $inscripcion->postulante;
            $postulacion = $postulaciones->get($inscripcion->postulante_documento);

            if ($postulante === null || $postulacion === null) {
                continue;
            }

            $boletin = $this->notas->boletin($postulante, $convocatoria->id);
            if ($boletin['promedio_final'] < self::APROBACION) {
                continue;
            }

            $reporte = $this->asistencias->reporte($postulante, $convocatoria->id);
            if ($reporte['estado'] !== EstadoHabilitacion::HABILITADO->value) {
                continue;
            }

            $elegibles[] = [
                'documento' => $inscripcion->postulante_documento,
                'promedio' => (float) $boletin['promedio_final'],
                'primer_pago' => $primerPago[$inscripcion->postulante_documento] ?? '9999-12-31 23:59:59',
                'primera' => $postulacion->carrera_primera_codigo,
                'segunda' => $postulacion->carrera_segunda_codigo,
            ];
        }

        // Mayor nota primero; a igualdad, quien pagó antes.
        usort($elegibles, fn ($a, $b) => $b['promedio'] <=> $a['promedio'] ?: strcmp($a['primer_pago'], $b['primer_pago']));

        return $elegibles;
    }

    /**
     * Fecha del primer pago PAGADO por postulante.
     *
     * @return array<string, string>
     */
    private function primerPagoPorPostulante(int $convocatoriaId): array
    {
        return Pago::query()
            ->where('convocatoria_id', $convocatoriaId)
            ->where('estado', EstadoPago::PAGADO->value)
            ->selectRaw('postulante_documento, MIN(fecha_pago) as fp')
            ->groupBy('postulante_documento')
            ->pluck('fp', 'postulante_documento')
            ->all();
    }
}
