<?php

namespace Tests\Feature\Seeders;

use App\Modules\AcademicManagement\Models\Aula;
use App\Modules\AcademicManagement\Models\Carrera;
use App\Modules\AcademicManagement\Models\Facultad;
use App\Modules\AcademicManagement\Models\Materia;
use App\Modules\AcademicManagement\Models\Modulo;
use App\Modules\AcademicManagement\Models\Periodo;
use App\Modules\Authentication\Models\Permission;
use App\Modules\Authentication\Models\Role;
use App\Modules\Authentication\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    private function conteos(): array
    {
        return [
            'facultades' => Facultad::count(),
            'carreras' => Carrera::count(),
            'materias' => Materia::count(),
            'modulos' => Modulo::count(),
            'aulas' => Aula::count(),
            'periodos' => Periodo::count(),
            'permissions' => Permission::count(),
            'roles' => Role::count(),
            'users' => User::count(),
        ];
    }

    public function test_seeder_puebla_los_catalogos_esenciales(): void
    {
        $this->seed(DatabaseSeeder::class);

        $c = $this->conteos();
        $this->assertSame(1, $c['facultades']);
        $this->assertSame(2, $c['carreras']);
        $this->assertSame(4, $c['materias']);
        $this->assertSame(1, $c['modulos']);
        $this->assertSame(2, $c['aulas']);
        $this->assertSame(1, $c['periodos']);
        $this->assertSame(4, $c['roles']);
        $this->assertGreaterThan(0, $c['permissions']);
        $this->assertGreaterThan(0, $c['users']);
    }

    public function test_seeder_es_idempotente_tabla_por_tabla(): void
    {
        $this->seed(DatabaseSeeder::class);
        $antes = $this->conteos();

        // Segunda corrida (simula redeploy): no debe duplicar nada.
        $this->seed(DatabaseSeeder::class);

        $this->assertSame($antes, $this->conteos());
    }
}
