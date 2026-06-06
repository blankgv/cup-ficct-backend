<?php

namespace App\Modules\ApplicantAdmission\Services;

use App\Modules\AcademicManagement\Enums\Turno;
use App\Modules\AcademicManagement\Models\Grupo;
use App\Modules\ApplicantAdmission\Enums\EstadoPostulacion;
use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\ApplicantAdmission\Models\Inscripcion;
use App\Modules\Payments\Enums\EstadoPago;
use App\Modules\Payments\Models\Pago;
use Illuminate\Support\Facades\DB;

// Genera grupos y asigna automáticamente a los postulantes elegibles.
class AsignacionGrupoService
{
    // Capacidad máxima de estudiantes por grupo.
    private const CAPACIDAD = 70;

    /**
     * Regenera grupos e inscripciones de la convocatoria.
     *
     * @return array<string, int>
     */
    public function generar(Convocatoria $convocatoria): array
    {
        return DB::transaction(function () use ($convocatoria) {
            // Borra lo autogenerado previo (idempotencia por regeneración).
            Inscripcion::where('convocatoria_id', $convocatoria->id)->delete();
            Grupo::where('convocatoria_id', $convocatoria->id)->delete();

            $elegibles = $this->elegibles($convocatoria->id);
            $total = $elegibles->count();

            if ($total === 0) {
                return ['grupos_creados' => 0, 'inscritos' => 0, 'manana' => 0, 'tarde' => 0];
            }

            // k grupos por turno: 70 máx por grupo, igual cantidad mañana/tarde.
            $k = (int) ceil($total / (self::CAPACIDAD * 2));
            $grupos = $this->crearGrupos($convocatoria, $k);

            $contador = [Turno::MANANA->value => 0, Turno::TARDE->value => 0];

            foreach ($elegibles as $elegible) {
                $preferido = $this->turnoPreferido($elegible->turno_preferencia);
                $otro = $preferido === Turno::MANANA->value ? Turno::TARDE->value : Turno::MANANA->value;

                // Va a su turno preferido; si está lleno, rebalsa al otro.
                $grupoId = $this->tomarGrupo($grupos[$preferido]);
                $turnoFinal = $preferido;
                if ($grupoId === null) {
                    $grupoId = $this->tomarGrupo($grupos[$otro]);
                    $turnoFinal = $otro;
                }

                Inscripcion::create([
                    'postulante_documento' => $elegible->postulante_documento,
                    'convocatoria_id' => $convocatoria->id,
                    'grupo_id' => $grupoId,
                    'fecha_asignacion' => now(),
                ]);

                $contador[$turnoFinal]++;
            }

            return [
                'grupos_creados' => $k * 2,
                'inscritos' => $total,
                'manana' => $contador[Turno::MANANA->value],
                'tarde' => $contador[Turno::TARDE->value],
            ];
        });
    }

    // Postulantes VERIFICADO con pago PAGADO, ordenados por su primer pago (paga antes = elige antes).
    private function elegibles(int $convocatoriaId): \Illuminate\Support\Collection
    {
        return DB::table('postulaciones')
            ->join('pagos', function ($join) use ($convocatoriaId) {
                $join->on('pagos.postulante_documento', '=', 'postulaciones.postulante_documento')
                    ->where('pagos.convocatoria_id', '=', $convocatoriaId)
                    ->where('pagos.estado', '=', EstadoPago::PAGADO->value);
            })
            ->where('postulaciones.convocatoria_id', $convocatoriaId)
            ->where('postulaciones.estado', EstadoPostulacion::VERIFICADO->value)
            ->groupBy('postulaciones.postulante_documento', 'postulaciones.turno_preferencia')
            ->select('postulaciones.postulante_documento', 'postulaciones.turno_preferencia')
            ->selectRaw('MIN(pagos.fecha_pago) as primer_pago')
            ->orderBy('primer_pago')
            ->get();
    }

    /**
     * Crea k grupos por turno y devuelve su control de capacidad.
     *
     * @return array<string, list<array{id:int, libres:int}>>
     */
    private function crearGrupos(Convocatoria $convocatoria, int $k): array
    {
        $grupos = [Turno::MANANA->value => [], Turno::TARDE->value => []];
        $prefijo = [Turno::MANANA->value => 'M', Turno::TARDE->value => 'T'];

        for ($i = 1; $i <= $k; $i++) {
            foreach ([Turno::MANANA->value, Turno::TARDE->value] as $turno) {
                $grupo = Grupo::create([
                    'codigo' => sprintf('C%d-%s%d', $convocatoria->id, $prefijo[$turno], $i),
                    'turno' => $turno,
                    'capacidad' => self::CAPACIDAD,
                    'gestion' => $convocatoria->gestion,
                    'convocatoria_id' => $convocatoria->id,
                ]);

                $grupos[$turno][] = ['id' => $grupo->id, 'libres' => self::CAPACIDAD];
            }
        }

        return $grupos;
    }

    // Devuelve el id del primer grupo con espacio del turno (y descuenta un cupo), o null si está lleno.
    private function tomarGrupo(array &$grupos): ?int
    {
        foreach ($grupos as $i => $grupo) {
            if ($grupos[$i]['libres'] > 0) {
                $grupos[$i]['libres']--;

                return $grupos[$i]['id'];
            }
        }

        return null;
    }

    // Sin preferencia válida → mañana por defecto.
    private function turnoPreferido(?string $turno): string
    {
        return in_array($turno, [Turno::MANANA->value, Turno::TARDE->value], true)
            ? $turno
            : Turno::MANANA->value;
    }
}
