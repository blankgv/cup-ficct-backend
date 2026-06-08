<?php

namespace App\Modules\Evaluation\Services;

use App\Modules\AcademicManagement\Models\Docente;
use App\Modules\ApplicantAdmission\Models\Inscripcion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Restringe el acceso a evaluación: un docente solo opera sobre los grupos/materias
// que tiene asignados. El staff (sin registro de docente) tiene acceso completo.
class EvaluacionAccessGuard
{
    // CI del docente autenticado, o null si es staff (acceso total).
    public function docenteCi(): ?string
    {
        return Docente::query()->where('user_id', Auth::id())->value('ci');
    }

    // Endpoints por grupo+materia (planillas batch).
    public function assertGrupoMateria(int $grupoId, string $materiaSigla): void
    {
        $ci = $this->docenteCi();
        if ($ci === null) {
            return;
        }

        $ok = DB::table('grupo_materia')
            ->where('grupo_id', $grupoId)
            ->where('materia_sigla', $materiaSigla)
            ->where('docente_ci', $ci)
            ->exists();

        abort_unless($ok, 403, 'No tenés asignada esta materia en este grupo.');
    }

    // Boletín / reporte de asistencia: el docente debe dictar en el grupo del inscrito.
    public function assertEnGrupoDeInscripcion(string $documento, int $convocatoriaId): void
    {
        $ci = $this->docenteCi();
        if ($ci === null) {
            return;
        }

        $grupoId = Inscripcion::query()
            ->where('postulante_documento', $documento)
            ->where('convocatoria_id', $convocatoriaId)
            ->value('grupo_id');

        $ok = $grupoId !== null && DB::table('grupo_materia')
            ->where('grupo_id', $grupoId)
            ->where('docente_ci', $ci)
            ->exists();

        abort_unless($ok, 403, 'Este estudiante no pertenece a tus grupos.');
    }

    // Nota/asistencia individual (postulante + convocatoria + materia).
    public function assertMateriaDeInscripcion(string $documento, int $convocatoriaId, string $materiaSigla): void
    {
        $ci = $this->docenteCi();
        if ($ci === null) {
            return;
        }

        $grupoId = Inscripcion::query()
            ->where('postulante_documento', $documento)
            ->where('convocatoria_id', $convocatoriaId)
            ->value('grupo_id');

        $ok = $grupoId !== null && DB::table('grupo_materia')
            ->where('grupo_id', $grupoId)
            ->where('materia_sigla', $materiaSigla)
            ->where('docente_ci', $ci)
            ->exists();

        abort_unless($ok, 403, 'No tenés asignada esta materia en el grupo del estudiante.');
    }

    /**
     * Grupos + materias visibles para el usuario (docente: los suyos; staff: todos).
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    public function misGrupos(): \Illuminate\Support\Collection
    {
        $ci = $this->docenteCi();

        $q = DB::table('grupo_materia as gm')
            ->join('grupos as g', 'g.id', '=', 'gm.grupo_id')
            ->leftJoin('convocatorias as c', 'c.id', '=', 'g.convocatoria_id')
            ->join('materias as m', 'm.sigla', '=', 'gm.materia_sigla')
            ->select(
                'g.id as grupo_id',
                'g.codigo as grupo_codigo',
                'g.turno',
                'g.gestion',
                'g.convocatoria_id',
                'c.nombre as convocatoria_nombre',
                'm.sigla as materia_sigla',
                'm.nombre as materia_nombre',
            );

        if ($ci !== null) {
            $q->where('gm.docente_ci', $ci);
        }

        return $q->orderByDesc('g.gestion')->orderBy('g.codigo')->orderBy('m.sigla')->get();
    }
}
