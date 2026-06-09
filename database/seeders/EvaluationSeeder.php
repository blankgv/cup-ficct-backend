<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// Notas (1 parcial por materia) y asistencia (todos los días hábiles del periodo,
// ~90% presente) para cada inscrito.
class EvaluationSeeder extends Seeder
{
    private const MATERIAS = ['MAT', 'FIS', 'ING', 'COM'];

    public function run(): void
    {
        if (DB::table('notas')->count() > 0) {
            return;
        }

        @ini_set('memory_limit', '512M');

        // Días hábiles (lun–vie) de cada convocatoria → las sesiones esperadas.
        $convs = DB::table('convocatorias')->get(['id', 'fecha_inicio', 'fecha_fin']);
        $diasPorConv = [];
        foreach ($convs as $c) {
            $diasPorConv[$c->id] = $this->diasHabiles($c->fecha_inicio, $c->fecha_fin);
        }

        $inscripciones = DB::table('inscripciones')->select('postulante_documento', 'convocatoria_id')->get();

        $notas = [];
        $asistencias = [];

        foreach ($inscripciones as $ins) {
            $doc = $ins->postulante_documento;
            $convId = $ins->convocatoria_id;
            $semilla = (int) substr($doc, -4);

            foreach (self::MATERIAS as $mi => $sigla) {
                // Nota 48–95 (mezcla de aprobados y reprobados).
                $valor = 48 + (($semilla + $mi * 13) % 48);
                $notas[] = [
                    'postulante_documento' => $doc,
                    'convocatoria_id' => $convId,
                    'materia_sigla' => $sigla,
                    'numero' => 1,
                    'valor' => $valor,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Asistencia en cada día hábil; ~1 de cada 11 es falta (>80% → habilitado).
                foreach ($diasPorConv[$convId] as $idx => $fecha) {
                    $falta = (($semilla + $mi + $idx) % 11) === 0;
                    $asistencias[] = [
                        'postulante_documento' => $doc,
                        'convocatoria_id' => $convId,
                        'materia_sigla' => $sigla,
                        'fecha' => $fecha,
                        'estado' => $falta ? 'AUSENTE' : 'PRESENTE',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Volcar por lotes para no agotar memoria (78k+ filas de asistencia).
            if (count($asistencias) >= 4000) {
                DB::table('asistencias')->insert($asistencias);
                $asistencias = [];
            }
        }

        foreach (array_chunk($notas, 1000) as $chunk) {
            DB::table('notas')->insert($chunk);
        }
        if ($asistencias !== []) {
            DB::table('asistencias')->insert($asistencias);
        }
    }

    /** @return list<string> Fechas (Y-m-d) de lunes a viernes en el rango. */
    private function diasHabiles(string $desde, string $hasta): array
    {
        $dias = [];
        $cursor = Carbon::parse($desde);
        $fin = Carbon::parse($hasta);
        while ($cursor->lte($fin)) {
            if ($cursor->isWeekday()) {
                $dias[] = $cursor->toDateString();
            }
            $cursor->addDay();
        }

        return $dias;
    }
}
