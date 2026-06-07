<?php

namespace Database\Seeders;

use App\Modules\AcademicManagement\Models\Aula;
use App\Modules\AcademicManagement\Models\Carrera;
use App\Modules\AcademicManagement\Models\Facultad;
use App\Modules\AcademicManagement\Models\Materia;
use App\Modules\AcademicManagement\Models\Modulo;
use App\Modules\AcademicManagement\Models\Periodo;
use Illuminate\Database\Seeder;

// Catálogos académicos base. Cada tabla se puebla solo si está vacía (idempotente).
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

        Facultad::create(['codigo' => '187', 'nombre' => 'Facultad de Ingeniería en Ciencias de la Computación y Telecomunicaciones', 'abreviatura' => 'FICCT']);
    }

    private function carreras(): void
    {
        if (Carrera::count() > 0) {
            return;
        }

        $carreras = [
            ['codigo' => '187-09', 'nombre' => 'Ingeniería de Sistemas', 'facultad_codigo' => '187'],
            ['codigo' => '187-10', 'nombre' => 'Ingeniería Informática', 'facultad_codigo' => '187'],
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

        Modulo::create(['numero' => '236', 'nombre' => 'Módulo 236', 'ubicacion' => 'Campus Universitario']);
    }

    private function aulas(): void
    {
        if (Aula::count() > 0) {
            return;
        }

        $aulas = [
            ['modulo_numero' => '236', 'numero' => 1, 'nombre' => 'Aula 236-1', 'capacidad' => 70, 'piso' => 1, 'tipo' => 'COMUN'],
            ['modulo_numero' => '236', 'numero' => 2, 'nombre' => 'Lab 236-2', 'capacidad' => 30, 'piso' => 1, 'tipo' => 'LABORATORIO'],
        ];

        foreach ($aulas as $a) {
            Aula::create($a);
        }
    }

    private function periodos(): void
    {
        if (Periodo::count() > 0) {
            return;
        }

        Periodo::create([
            'codigo' => '1/2026', 'gestion' => '2026',
            'fecha_inicio_clases' => '2026-03-02', 'fecha_fin_clases' => '2026-06-30',
        ]);
    }
}
