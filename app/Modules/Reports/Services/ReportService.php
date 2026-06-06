<?php

namespace App\Modules\Reports\Services;

use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\ApplicantAdmission\Models\Inscripcion;
use App\Modules\ApplicantAdmission\Models\Postulacion;
use App\Modules\Evaluation\Services\NotaService;
use App\Modules\Payments\Models\Pago;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// Construye los reportes (estructura común: titulo, headers, rows). Cada reporte acepta filtros.
class ReportService
{
    public function __construct(private readonly NotaService $notas) {}

    /**
     * Despacha un reporte por su clave (usado por el reporte por voz). Valida lo requerido.
     *
     * @param array<string, mixed> $filtros
     * @return array{titulo:string, headers:list<string>, rows:list<list<mixed>>}
     */
    public function porClave(string $clave, array $filtros): array
    {
        return match ($clave) {
            'estudiantes_por_grupo' => $this->estudiantesPorGrupo($this->conGestion($filtros)),
            'postulantes' => $this->postulantesPorConvocatoria($this->convocatoria($filtros), $filtros),
            'recaudacion' => $this->recaudacion($this->convocatoria($filtros), $filtros),
            'resultados' => $this->resultados($this->convocatoria($filtros), $filtros),
            'asignacion_carreras' => $this->asignacionCarreras($this->convocatoria($filtros), $filtros),
            default => throw ValidationException::withMessages(['reporte' => "No se reconoció el reporte solicitado: «{$clave}»."]),
        };
    }

    /**
     * @param array<string, mixed> $filtros
     * @return array<string, mixed>
     */
    private function conGestion(array $filtros): array
    {
        if (empty($filtros['gestion'])) {
            throw ValidationException::withMessages(['gestion' => 'Falta indicar la gestión (año) para el reporte.']);
        }

        return $filtros;
    }

    /**
     * Resuelve la convocatoria desde los filtros (por id o por nombre/gestión).
     *
     * @param array<string, mixed> $filtros
     */
    private function convocatoria(array $filtros): Convocatoria
    {
        $convocatoria = match (true) {
            ! empty($filtros['convocatoria_id']) => Convocatoria::find((int) $filtros['convocatoria_id']),
            ! empty($filtros['convocatoria']) => Convocatoria::query()
                ->where('nombre', $filtros['convocatoria'])
                ->orWhere('gestion', $filtros['convocatoria'])
                ->orderByDesc('id')
                ->first(),
            default => null,
        };

        if ($convocatoria === null) {
            throw ValidationException::withMessages(['convocatoria' => 'No se identificó la convocatoria del reporte.']);
        }

        return $convocatoria;
    }

    /**
     * Estudiantes por grupo. Filtros: gestion (req), grupo_id, turno, nombre, carrera.
     *
     * @param array<string, mixed> $f
     * @return array{titulo:string, headers:list<string>, rows:list<list<mixed>>}
     */
    public function estudiantesPorGrupo(array $f): array
    {
        $gestion = (string) ($f['gestion'] ?? '');

        $filas = Inscripcion::query()
            ->join('grupos', 'grupos.id', '=', 'inscripciones.grupo_id')
            ->join('postulantes', 'postulantes.documento', '=', 'inscripciones.postulante_documento')
            ->leftJoin('carreras', 'carreras.codigo', '=', 'inscripciones.carrera_asignada_codigo')
            ->where('grupos.gestion', $gestion)
            ->when($f['grupo_id'] ?? null, fn ($q, $v) => $q->where('grupos.id', (int) $v))
            ->when($f['turno'] ?? null, fn ($q, $v) => $q->where('grupos.turno', $v))
            ->when($f['carrera'] ?? null, fn ($q, $v) => $q->where('inscripciones.carrera_asignada_codigo', $v))
            ->when($f['nombre'] ?? null, fn ($q, $v) => $q->where(fn ($w) => $w
                ->whereLike('postulantes.nombres', "%{$v}%")
                ->orWhereLike('postulantes.apellidos', "%{$v}%")))
            ->orderBy('grupos.codigo')
            ->orderBy('postulantes.apellidos')
            ->get([
                'grupos.codigo as grupo', 'grupos.turno', 'inscripciones.postulante_documento as documento',
                'postulantes.nombres', 'postulantes.apellidos', 'carreras.nombre as carrera',
            ]);

        $rows = $filas->map(fn ($r) => [
            $r->grupo, $r->turno, $r->documento,
            trim("{$r->nombres} {$r->apellidos}"), $r->carrera ?? '-',
        ])->all();

        return [
            'titulo' => "Estudiantes por grupo (gestion {$gestion})",
            'headers' => ['Grupo', 'Turno', 'Documento', 'Estudiante', 'Carrera asignada'],
            'rows' => $rows,
        ];
    }

    /**
     * Postulantes por convocatoria. Filtros: estado, carrera, turno_preferencia, nombre.
     *
     * @param array<string, mixed> $f
     * @return array{titulo:string, headers:list<string>, rows:list<list<mixed>>}
     */
    public function postulantesPorConvocatoria(Convocatoria $convocatoria, array $f = []): array
    {
        $rows = Postulacion::with('postulante')
            ->where('convocatoria_id', $convocatoria->id)
            ->when($f['estado'] ?? null, fn ($q, $v) => $q->where('estado', $v))
            ->when($f['turno_preferencia'] ?? null, fn ($q, $v) => $q->where('turno_preferencia', $v))
            ->when($f['carrera'] ?? null, fn ($q, $v) => $q->where(fn ($w) => $w
                ->where('carrera_primera_codigo', $v)->orWhere('carrera_segunda_codigo', $v)))
            ->when($f['nombre'] ?? null, fn ($q, $v) => $q->whereHas('postulante', fn ($p) => $p
                ->whereLike('nombres', "%{$v}%")->orWhereLike('apellidos', "%{$v}%")))
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
     * Recaudación. Filtros: estado, metodo, desde, hasta (fecha_pago).
     *
     * @param array<string, mixed> $f
     * @return array{titulo:string, headers:list<string>, rows:list<list<mixed>>}
     */
    public function recaudacion(Convocatoria $convocatoria, array $f = []): array
    {
        $rows = Pago::query()
            ->where('convocatoria_id', $convocatoria->id)
            ->when($f['estado'] ?? null, fn ($q, $v) => $q->where('estado', $v))
            ->when($f['metodo'] ?? null, fn ($q, $v) => $q->where('metodo', $v))
            ->when($f['desde'] ?? null, fn ($q, $v) => $q->whereDate('fecha_pago', '>=', $v))
            ->when($f['hasta'] ?? null, fn ($q, $v) => $q->whereDate('fecha_pago', '<=', $v))
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
     * Resultados (notas). Filtros: estado (APROBADO/REPROBADO), nota_min, nota_max, grupo_id.
     *
     * @param array<string, mixed> $f
     * @return array{titulo:string, headers:list<string>, rows:list<list<mixed>>}
     */
    public function resultados(Convocatoria $convocatoria, array $f = []): array
    {
        $rows = Inscripcion::with('postulante')
            ->where('convocatoria_id', $convocatoria->id)
            ->when($f['grupo_id'] ?? null, fn ($q, $v) => $q->where('grupo_id', (int) $v))
            ->get()
            ->map(function (Inscripcion $i) use ($convocatoria) {
                $boletin = $this->notas->boletin($i->postulante, $convocatoria->id);

                return [
                    'documento' => $i->postulante_documento,
                    'nombre' => $i->postulante ? trim("{$i->postulante->nombres} {$i->postulante->apellidos}") : '-',
                    'promedio' => (float) $boletin['promedio_final'],
                    'estado' => $boletin['estado'],
                ];
            })
            ->when($f['estado'] ?? null, fn ($c, $v) => $c->where('estado', $v))
            ->when(isset($f['nota_min']), fn ($c) => $c->where('promedio', '>=', (float) $f['nota_min']))
            ->when(isset($f['nota_max']), fn ($c) => $c->where('promedio', '<=', (float) $f['nota_max']))
            ->map(fn ($r) => [$r['documento'], $r['nombre'], $r['promedio'], $r['estado']])
            ->values()
            ->all();

        return [
            'titulo' => "Resultados - {$convocatoria->nombre}",
            'headers' => ['Documento', 'Estudiante', 'Promedio final', 'Estado'],
            'rows' => $rows,
        ];
    }

    /**
     * Asignación de carreras. Filtro: carrera.
     *
     * @param array<string, mixed> $f
     * @return array{titulo:string, headers:list<string>, rows:list<list<mixed>>}
     */
    public function asignacionCarreras(Convocatoria $convocatoria, array $f = []): array
    {
        $asignados = Inscripcion::query()
            ->where('convocatoria_id', $convocatoria->id)
            ->whereNotNull('carrera_asignada_codigo')
            ->select('carrera_asignada_codigo', DB::raw('COUNT(*) as total'))
            ->groupBy('carrera_asignada_codigo')
            ->pluck('total', 'carrera_asignada_codigo')
            ->all();

        $rows = $convocatoria->carreras
            ->when($f['carrera'] ?? null, fn ($c, $v) => $c->where('codigo', $v))
            ->map(function ($carrera) use ($asignados) {
                $cupos = (int) $carrera->pivot->cupos;
                $usados = (int) ($asignados[$carrera->codigo] ?? 0);

                return [$carrera->codigo, $carrera->nombre, $cupos, $usados, max(0, $cupos - $usados)];
            })->values()->all();

        return [
            'titulo' => "Asignacion de carreras - {$convocatoria->nombre}",
            'headers' => ['Carrera', 'Nombre', 'Cupos', 'Asignados', 'Disponibles'],
            'rows' => $rows,
        ];
    }
}
