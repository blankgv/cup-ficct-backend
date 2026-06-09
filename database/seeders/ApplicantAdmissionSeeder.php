<?php

namespace Database\Seeders;

use App\Modules\AcademicManagement\Models\Docente;
use App\Modules\AcademicManagement\Models\Periodo;
use App\Modules\ApplicantAdmission\Models\Convocatoria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// Escenario realista del CUP-FICCT: convocatorias, docentes, grupos, horarios y
// 70 estudiantes por grupo (2 grupos mañana + 2 tarde por convocatoria).
class ApplicantAdmissionSeeder extends Seeder
{
    private const CARRERAS = ['183-01', '183-02', '183-03', '183-04'];
    private const MATERIAS = ['MAT', 'FIS', 'ING', 'COM'];
    private const NOMBRES = ['Juan', 'María', 'Carlos', 'Ana', 'Luis', 'Lucía', 'Pedro', 'Sofía', 'Diego', 'Valeria', 'Jorge', 'Camila', 'Andrés', 'Gabriela', 'Mateo', 'Daniela', 'Sergio', 'Paola', 'Iván', 'Rocío'];
    private const APELLIDOS = ['Pérez', 'Gutiérrez', 'Vargas', 'Mamani', 'Flores', 'Quispe', 'Rojas', 'Choque', 'Camacho', 'Suárez', 'Mendoza', 'Cruz', 'Lima', 'Vaca', 'Ortiz', 'Aguilar', 'Salazar', 'Téllez', 'Arce', 'Núñez'];
    private const COLEGIOS = ['Colegio Nacional Florida', 'Colegio La Salle', 'Colegio Don Bosco', 'Colegio Marista', 'Colegio Alemán', 'Unidad Educativa San Calixto'];
    private const CIUDADES = ['Santa Cruz', 'La Paz', 'Cochabamba', 'Sucre', 'Tarija', 'Oruro'];

    // Franjas horarias por turno (4 materias, una por franja, lunes a viernes).
    private const FRANJAS = [
        'MANANA' => ['07:00-08:30', '08:30-10:00', '10:00-11:30', '11:30-13:00'],
        'TARDE' => ['14:00-15:30', '15:30-17:00', '17:00-18:30', '18:30-20:00'],
    ];
    private const DIAS = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES'];

    private string $password = '';
    private int $roleId = 0;
    private int $docenteRoleId = 0;
    private int $docCounter = 4000000;
    private int $aulaCounter = 1;

    public function run(): void
    {
        if (Convocatoria::count() > 0) {
            return;
        }

        $this->password = Hash::make('password');
        $this->roleId = (int) DB::table('roles')->where('name', 'POSTULANTE')->value('id');
        $this->docenteRoleId = (int) DB::table('roles')->where('name', 'DOCENTE')->value('id');

        $docentes = $this->docentes();

        $convocatorias = [
            ['nombre' => 'CUP Junio 2025', 'gestion' => '2025', 'fecha_inicio' => '2025-06-09', 'fecha_fin' => '2025-07-25', 'estado' => 'CERRADA', 'periodo' => '2/2025'],
            ['nombre' => 'CUP Enero 2026', 'gestion' => '2026', 'fecha_inicio' => '2026-01-05', 'fecha_fin' => '2026-02-20', 'estado' => 'ABIERTA', 'periodo' => '1/2026'],
        ];

        foreach ($convocatorias as $def) {
            $this->convocatoria($def, $docentes);
        }
    }

    /** @return list<string> CIs de docentes creados. */
    private function docentes(): array
    {
        $cis = [];
        for ($i = 0; $i < 8; $i++) {
            $ci = (string) (3000000 + $i);
            $email = "docente{$i}@cup-ficct.local";

            // Cuenta de acceso del docente (rol DOCENTE) para el área de evaluación.
            $userId = DB::table('users')->insertGetId([
                'email' => $email,
                'username' => null,
                'password' => $this->password,
                'must_change_password' => false,
                'role_id' => $this->docenteRoleId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Docente::create([
                'ci' => $ci,
                'nombres' => self::NOMBRES[$i % count(self::NOMBRES)],
                'apellidos' => self::APELLIDOS[($i + 5) % count(self::APELLIDOS)],
                'email' => $email,
                'telefono' => '700'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'profesion' => 'Ingeniero',
                'user_id' => $userId,
            ]);
            $cis[] = $ci;
        }

        return $cis;
    }

    /**
     * @param array<string, string> $def
     * @param list<string> $docentes
     */
    private function convocatoria(array $def, array $docentes): void
    {
        $periodoId = Periodo::query()->where('codigo', $def['periodo'])->value('id');

        $conv = Convocatoria::create([
            'nombre' => $def['nombre'],
            'gestion' => $def['gestion'],
            'fecha_inicio' => $def['fecha_inicio'],
            'fecha_fin' => $def['fecha_fin'],
            'estado' => $def['estado'],
        ]);

        // Cupos por carrera (2 grupos por turno × 70 = 140 por carrera, holgado).
        foreach (self::CARRERAS as $carrera) {
            DB::table('carrera_convocatoria')->insert([
                'carrera_codigo' => $carrera,
                'convocatoria_id' => $conv->id,
                'cupos' => 80,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2 grupos mañana + 2 tarde.
        $grupos = [
            ['codigo' => 'M1-'.$def['gestion'], 'turno' => 'MANANA'],
            ['codigo' => 'M2-'.$def['gestion'], 'turno' => 'MANANA'],
            ['codigo' => 'T1-'.$def['gestion'], 'turno' => 'TARDE'],
            ['codigo' => 'T2-'.$def['gestion'], 'turno' => 'TARDE'],
        ];

        $d = 0;
        foreach ($grupos as $g) {
            $grupoId = DB::table('grupos')->insertGetId([
                'codigo' => $g['codigo'],
                'turno' => $g['turno'],
                'capacidad' => 70,
                'gestion' => $def['gestion'],
                'convocatoria_id' => $conv->id,
                'periodo_id' => $periodoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $aula = $this->aulaCounter++;
            $this->materiasYHorarios($grupoId, $g['turno'], $aula, $docentes, $d);
            $this->estudiantes($grupoId, $conv->id, $g['turno']);
            $d++;
        }
    }

    /**
     * Adjunta las 4 materias al grupo (con docente) y crea sus horarios.
     *
     * @param list<string> $docentes
     */
    private function materiasYHorarios(int $grupoId, string $turno, int $aula, array $docentes, int $grupoIdx): void
    {
        $franjas = self::FRANJAS[$turno];

        foreach (self::MATERIAS as $mi => $sigla) {
            $ci = $docentes[($grupoIdx * 4 + $mi) % count($docentes)];

            DB::table('grupo_materia')->insert([
                'grupo_id' => $grupoId,
                'materia_sigla' => $sigla,
                'docente_ci' => $ci,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            [$ini, $fin] = explode('-', $franjas[$mi]);

            // Una sesión por día (lunes a viernes).
            foreach (self::DIAS as $di => $dia) {
                DB::table('horarios')->insert([
                    'grupo_id' => $grupoId,
                    'materia_sigla' => $sigla,
                    'numero' => $di + 1,
                    'dia' => $dia,
                    'hora_inicio' => $ini,
                    'hora_fin' => $fin,
                    'aula_modulo_numero' => '236',
                    'aula_numero' => $aula,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    // 70 estudiantes con cuenta, postulación (VERIFICADO) e inscripción.
    private function estudiantes(int $grupoId, int $convId, string $turno): void
    {
        $users = [];
        $base = [];
        for ($i = 0; $i < 70; $i++) {
            $doc = (string) $this->docCounter++;
            $email = "alumno{$doc}@cup.local";
            $users[] = [
                'email' => $email,
                'username' => null,
                'password' => $this->password,
                'must_change_password' => false,
                'role_id' => $this->roleId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $base[] = ['doc' => $doc, 'email' => $email, 'i' => $i];
        }

        DB::table('users')->insert($users);
        $ids = DB::table('users')
            ->whereIn('email', array_column($base, 'email'))
            ->pluck('id', 'email');

        $postulantes = [];
        $postulaciones = [];
        $inscripciones = [];
        $turnoPref = $turno === 'MANANA' ? 'MANANA' : 'TARDE';

        foreach ($base as $b) {
            $i = $b['i'];
            $carrera = self::CARRERAS[$i % 4];
            $segunda = self::CARRERAS[($i + 1) % 4];

            $postulantes[] = [
                'documento' => $b['doc'],
                'nombres' => self::NOMBRES[$i % count(self::NOMBRES)],
                'apellidos' => self::APELLIDOS[($i + $grupoId) % count(self::APELLIDOS)],
                'email' => $b['email'],
                'telefono' => '6'.str_pad((string) ($this->docCounter + $i), 7, '0', STR_PAD_LEFT),
                'fecha_nacimiento' => sprintf('200%d-%02d-%02d', 4 + ($i % 4), 1 + ($i % 12), 1 + ($i % 27)),
                'colegio' => self::COLEGIOS[$i % count(self::COLEGIOS)],
                'ciudad' => self::CIUDADES[$i % count(self::CIUDADES)],
                'titulo_bachiller_path' => null,
                'user_id' => $ids[$b['email']],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $postulaciones[] = [
                'postulante_documento' => $b['doc'],
                'convocatoria_id' => $convId,
                'carrera_primera_codigo' => $carrera,
                'carrera_segunda_codigo' => $segunda,
                'estado' => 'VERIFICADO',
                'observacion' => null,
                'turno_preferencia' => $turnoPref,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $inscripciones[] = [
                'postulante_documento' => $b['doc'],
                'convocatoria_id' => $convId,
                'grupo_id' => $grupoId,
                'carrera_asignada_codigo' => $carrera,
                'fecha_asignacion' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('postulantes')->insert($postulantes);
        DB::table('postulaciones')->insert($postulaciones);
        DB::table('inscripciones')->insert($inscripciones);
    }
}
