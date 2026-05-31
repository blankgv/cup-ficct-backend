<?php

namespace Database\Seeders;

use App\Modules\AcademicManagement\Models\Materia;
use Illuminate\Database\Seeder;

// Materias del CUP (4, peso 0.25 c/u → suma 1.00).
class AcademicManagementSeeder extends Seeder
{
    public function run(): void
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
}
