<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// Notas (1 parcial por materia) y asistencia (6 fechas) para cada inscrito.
class EvaluationSeeder extends Seeder
{
    private const MATERIAS = ['MAT', 'FIS', 'ING', 'COM'];
    private const FECHAS = 6;

    public function run(): void
    {
        if (DB::table('notas')->count() > 0) {
            return;
        }

        // Fecha base por convocatoria (inicio de clases) para generar las asistencias.
        $inicios = DB::table('convocatorias')->pluck('fecha_inicio', 'id');

        $inscripciones = DB::table('inscripciones')->select('postulante_documento', 'convocatoria_id')->get();

        $notas = [];
        $asistencias = [];

        foreach ($inscripciones as $ins) {
            $doc = $ins->postulante_documento;
            $convId = $ins->convocatoria_id;
            $base = Carbon::parse($inicios[$convId]);
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

                // Asistencia: mayormente PRESENTE; una falta ocasional.
                for ($f = 0; $f < self::FECHAS; $f++) {
                    $falta = (($semilla + $mi + $f) % 7) === 0;
                    $asistencias[] = [
                        'postulante_documento' => $doc,
                        'convocatoria_id' => $convId,
                        'materia_sigla' => $sigla,
                        'fecha' => $base->copy()->addDays($f * 2)->toDateString(),
                        'estado' => $falta ? 'AUSENTE' : 'PRESENTE',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        foreach (array_chunk($notas, 1000) as $chunk) {
            DB::table('notas')->insert($chunk);
        }
        foreach (array_chunk($asistencias, 1000) as $chunk) {
            DB::table('asistencias')->insert($chunk);
        }
    }
}
