<?php

namespace Tests\Feature\AcademicManagement;

use App\Modules\AcademicManagement\Models\Grupo;
use App\Modules\AcademicManagement\Models\Materia;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\User;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrupoMateriaTest extends TestCase
{
    use RefreshDatabase;

    private Grupo $grupo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthenticationSeeder::class);

        $this->grupo = Grupo::create(['codigo' => 'A', 'turno' => 'MANANA', 'capacidad' => 70, 'gestion' => '2026']);
        foreach (['MAT', 'FIS', 'ING', 'COM'] as $sigla) {
            Materia::create(['sigla' => $sigla, 'nombre' => $sigla, 'peso' => 0.25]);
        }
    }

    private function coordinador(): User
    {
        $user = User::factory()->create(['must_change_password' => false]);
        $user->assignRole(RoleName::COORDINADOR);

        return $user;
    }

    public function test_sync_asigna_materias_al_grupo(): void
    {
        $this->actingAs($this->coordinador(), 'api')
            ->putJson("/api/academic-management/grupos/{$this->grupo->id}/materias", [
                'siglas' => ['MAT', 'FIS', 'ING', 'COM'],
            ])
            ->assertOk()
            ->assertJsonCount(4, 'data');

        $this->assertDatabaseHas('grupo_materia', ['grupo_id' => $this->grupo->id, 'materia_sigla' => 'MAT']);
    }

    public function test_sync_reemplaza_las_existentes(): void
    {
        $this->grupo->materias()->sync(['MAT', 'FIS']);

        $this->actingAs($this->coordinador(), 'api')
            ->putJson("/api/academic-management/grupos/{$this->grupo->id}/materias", ['siglas' => ['ING']])
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->assertDatabaseMissing('grupo_materia', ['grupo_id' => $this->grupo->id, 'materia_sigla' => 'MAT']);
    }

    public function test_agrega_y_quita_una_materia(): void
    {
        $coordinador = $this->coordinador();

        $this->actingAs($coordinador, 'api')
            ->postJson("/api/academic-management/grupos/{$this->grupo->id}/materias", ['sigla' => 'MAT'])
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($coordinador, 'api')
            ->deleteJson("/api/academic-management/grupos/{$this->grupo->id}/materias/MAT")
            ->assertOk();

        $this->assertDatabaseMissing('grupo_materia', ['grupo_id' => $this->grupo->id, 'materia_sigla' => 'MAT']);
    }

    public function test_sigla_inexistente_es_rechazada(): void
    {
        $this->actingAs($this->coordinador(), 'api')
            ->postJson("/api/academic-management/grupos/{$this->grupo->id}/materias", ['sigla' => 'XXX'])
            ->assertStatus(422);
    }
}
