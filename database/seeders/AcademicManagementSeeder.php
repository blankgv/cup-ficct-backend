<?php

namespace Database\Seeders;

use App\Modules\AcademicManagement\Models\Aula;
use App\Modules\AcademicManagement\Models\Carrera;
use App\Modules\AcademicManagement\Models\Facultad;
use App\Modules\AcademicManagement\Models\Materia;
use App\Modules\AcademicManagement\Models\Modulo;
use App\Modules\AcademicManagement\Models\Periodo;
use Illuminate\Database\Seeder;

// Catálogos académicos base (FICCT). Cada tabla se puebla solo si está vacía.
class AcademicManagementSeeder extends Seeder
{
    public function run(): void
    {
        $this->facultades();
        $this->carreras();
        $this->materias();
        $this->modulos();
        $this->aulas();
        $this->periodos();
    }

    private function facultades(): void
    {
        if (Facultad::count() > 0) {
            return;
        }

        Facultad::create([
            'codigo' => '183',
            'nombre' => 'Facultad de Ingeniería en Ciencias de la Computación y Telecomunicaciones',
            'abreviatura' => 'FICCT',
        ]);
    }

    private function carreras(): void
    {
        if (Carrera::count() > 0) {
            return;
        }

        $carreras = [
            ['codigo' => '183-01', 'nombre' => 'Ingeniería en Sistemas', 'facultad_codigo' => '183'],
            ['codigo' => '183-02', 'nombre' => 'Ingeniería Informática', 'facultad_codigo' => '183'],
            ['codigo' => '183-03', 'nombre' => 'Ingeniería en Redes y Telecomunicaciones', 'facultad_codigo' => '183'],
            ['codigo' => '183-04', 'nombre' => 'Ingeniería en Robótica', 'facultad_codigo' => '183'],
        ];

        foreach ($carreras as $c) {
            Carrera::create($c);
        }
    }

    private function materias(): void
    {
        if (Materia::count() > 0) {
            return;
        }

        $materias = [
            ['nombre' => 'Matemáticas', 'sigla' => 'MAT', 'peso' => 0.25],
            ['nombre' => 'Física',      'sigla' => 'FIS', 'peso' => 0.25],
            ['nombre' => 'Inglés',      'sigla' => 'ING', 'peso' => 0.25],
            ['nombre' => 'Computación', 'sigla' => 'COM', 'peso' => 0.25],
        ];

        foreach ($materias as $m) {
            Materia::create($m);
        }
    }

    private function modulos(): void
    {
        if (Modulo::count() > 0) {
            return;
        }

        Modulo::create([
            'numero' => '236',
            'nombre' => 'Módulo 236',
            'ubicacion' => 'Módulos universitarios',
        ]);
    }

    private function aulas(): void
    {
        if (Aula::count() > 0) {
            return;
        }

        // 8 aulas comunes de capacidad 70 (una por grupo) + un laboratorio.
        for ($i = 1; $i <= 8; $i++) {
            Aula::create([
                'modulo_numero' => '236',
                'numero' => $i,
                'nombre' => "Aula 236-{$i}",
                'capacidad' => 70,
                'piso' => $i <= 4 ? 1 : 2,
                'tipo' => 'COMUN',
            ]);
        }

        Aula::create([
            'modulo_numero' => '236',
            'numero' => 9,
            'nombre' => 'Laboratorio 236-9',
            'capacidad' => 40,
            'piso' => 1,
            'tipo' => 'LABORATORIO',
        ]);
    }

    private function periodos(): void
    {
        if (Periodo::count() > 0) {
            return;
        }

        // El CUP dura ~mes y medio antes de cada semestre universitario (enero y junio).
        Periodo::create([
            'codigo' => '2/2025', 'gestion' => '2025',
            'fecha_inicio_clases' => '2025-06-09', 'fecha_fin_clases' => '2025-07-25',
        ]);
        Periodo::create([
            'codigo' => '1/2026', 'gestion' => '2026',
            'fecha_inicio_clases' => '2026-01-05', 'fecha_fin_clases' => '2026-02-20',
        ]);
    }
}
