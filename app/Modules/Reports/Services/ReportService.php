<?php

namespace App\Modules\Reports\Services;

use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\ApplicantAdmission\Models\Inscripcion;
use App\Modules\ApplicantAdmission\Models\Postulacion;
use App\Modules\Evaluation\Services\NotaService;
use App\Modules\Payments\Models\Pago;
use Illuminate\Support\Facades\DB;

// Construye los reportes (estructura común: titulo, headers, rows).
class ReportService
{
    public function __construct(private readonly NotaService $notas) {}

    /**
     * Estudiantes por grupo (solo grupos de la gestión). Filtro opcional por nombre.
     *
     * @return array{titulo:string, headers:list<string>, rows:list<list<mixed>>}
     */
    public function estudiantesPorGrupo(string $gestion, ?int $grupoId, ?string $nombre): array
    {
        $filas = Inscripcion::query()
            ->join('grupos', 'grupos.id', '=', 'inscripciones.grupo_id')
            ->join('postulantes', 'postulantes.documento', '=', 'inscripciones.postulante_documento')
            ->leftJoin('carreras', 'carreras.codigo', '=', 'inscripciones.carrera_asignada_codigo')
            ->where('grupos.gestion', $gestion)
            ->when($grupoId, fn ($q) => $q->where('grupos.id', $grupoId))
            ->when($nombre, fn ($q) => $q->where(fn ($w) => $w
                ->whereLike('postulantes.nombres', "%{$nombre}%")
                ->orWhereLike('postulantes.apellidos', "%{$nombre}%")))
            ->orderBy('grupos.codigo')
            ->orderBy('postulantes.apellidos')
            ->get([
                'grupos.codigo as grupo', 'grupos.turno', 'inscripciones.postulante_documento as documento',
                'postulantes.nombres', 'postulantes.apellidos', 'carreras.nombre as carrera',
            ]);

        $rows = $filas->map(fn ($f) => [
            $f->grupo, $f->turno, $f->documento,
            trim("{$f->nombres} {$f->apellidos}"), $f->carrera ?? '-',
        ])->all();

        return [
            'titulo' => "Estudiantes por grupo (gestion {$gestion})",
            'headers' => ['Grupo', 'Turno', 'Documento', 'Estudiante', 'Carrera asignada'],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{titulo:string, headers:list<string>, rows:list<list<mixed>>}
     */
    public function postulantesPorConvocatoria(Convocatoria $convocatoria): array
    {
        $rows = Postulacion::with('postulante')
            ->where('convocatoria_id', $convocatoria->id)
            ->get()
            ->map(fn (Postulacion $p) => [
                $p->postulante_documento,
                $p->postulante ? trim("{$p->postulante->nombres} {$p->postulante->apellidos}") : '-',
                $p->carrera_primera_codigo,
                $p->carrera_segunda_codigo,
                $p->estado?->value,
                $p->turno_preferencia?->value ?? '-',
            ])->all();

        return [
            'titulo' => "Postulantes - {$convocatoria->nombre}",
            'headers' => ['Documento', 'Postulante', '1ra opción', '2da opción', 'Estado', 'Turno pref.'],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{titulo:string, headers:list<string>, rows:list<list<mixed>>}
     */
    public function recaudacion(Convocatoria $convocatoria): array
    {
        $rows = Pago::query()
            ->where('convocatoria_id', $convocatoria->id)
            ->selectRaw('estado, metodo, COUNT(*) as cantidad, SUM(monto) as total')
            ->groupBy('estado', 'metodo')
            ->orderBy('estado')
            ->orderBy('metodo')
            ->get()
            ->map(fn ($p) => [$p->estado, $p->metodo, (int) $p->cantidad, number_format((float) $p->total, 2, '.', '')])
            ->all();

        return [
            'titulo' => "Recaudacion - {$convocatoria->nombre}",
            'headers' => ['Estado', 'Método', 'Cantidad', 'Monto'],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{titulo:string, headers:list<string>, rows:list<list<mixed>>}
     */
    public function resultados(Convocatoria $convocatoria): array
    {
        $rows = Inscripcion::with('postulante')
            ->where('convocatoria_id', $convocatoria->id)
            ->get()
            ->map(function (Inscripcion $i) use ($convocatoria) {
                $boletin = $this->notas->boletin($i->postulante, $convocatoria->id);

                return [
                    $i->postulante_documento,
                    $i->postulante ? trim("{$i->postulante->nombres} {$i->postulante->apellidos}") : '-',
                    $boletin['promedio_final'],
                    $boletin['estado'],
                ];
            })->all();

        return [
            'titulo' => "Resultados - {$convocatoria->nombre}",
            'headers' => ['Documento', 'Estudiante', 'Promedio final', 'Estado'],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{titulo:string, headers:list<string>, rows:list<list<mixed>>}
     */
    public function asignacionCarreras(Convocatoria $convocatoria): array
    {
        $asignados = Inscripcion::query()
            ->where('convocatoria_id', $convocatoria->id)
            ->whereNotNull('carrera_asignada_codigo')
            ->select('carrera_asignada_codigo', DB::raw('COUNT(*) as total'))
            ->groupBy('carrera_asignada_codigo')
            ->pluck('total', 'carrera_asignada_codigo')
            ->all();

        $rows = $convocatoria->carreras->map(function ($carrera) use ($asignados) {
            $cupos = (int) $carrera->pivot->cupos;
            $usados = (int) ($asignados[$carrera->codigo] ?? 0);

            return [$carrera->codigo, $carrera->nombre, $cupos, $usados, max(0, $cupos - $usados)];
        })->all();

        return [
            'titulo' => "Asignacion de carreras - {$convocatoria->nombre}",
            'headers' => ['Carrera', 'Nombre', 'Cupos', 'Asignados', 'Disponibles'],
            'rows' => $rows,
        ];
    }
}
