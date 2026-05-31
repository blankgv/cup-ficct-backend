<?php

namespace Tests\Feature\AcademicManagement;

use App\Modules\AcademicManagement\Models\Docente;
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

    private function docente(): Docente
    {
        return Docente::create([
            'ci' => '1234567', 'nombres' => 'Juan', 'apellidos' => 'Pérez',
            'email' => 'jperez@cup-ficct.local',
        ]);
    }

    public function test_asigna_docente_al_grupo_materia(): void
    {
        $this->grupo->materias()->sync(['FIS']);
        $this->docente();

        $this->actingAs($this->coordinador(), 'api')
            ->putJson("/api/academic-management/grupos/{$this->grupo->id}/materias/FIS/docente", ['ci' => '1234567'])
            ->assertOk()
            ->assertJsonPath('data.docente.ci', '1234567');

        $this->assertDatabaseHas('grupo_materia', [
            'grupo_id' => $this->grupo->id, 'materia_sigla' => 'FIS', 'docente_ci' => '1234567',
        ]);
    }

    public function test_quita_docente_del_grupo_materia(): void
    {
        $this->grupo->materias()->sync(['FIS']);
        $this->docente();
        $coord = $this->coordinador();

        $this->actingAs($coord, 'api')
            ->putJson("/api/academic-management/grupos/{$this->grupo->id}/materias/FIS/docente", ['ci' => '1234567'])
            ->assertOk();

        $this->actingAs($coord, 'api')
            ->deleteJson("/api/academic-management/grupos/{$this->grupo->id}/materias/FIS/docente")
            ->assertOk()
            ->assertJsonPath('data.docente', null);
    }

    public function test_borrar_docente_deja_grupo_materia_sin_docente(): void
    {
        $this->grupo->materias()->sync(['FIS']);
        $docente = $this->docente();

        $this->grupo->materias()->updateExistingPivot('FIS', ['docente_ci' => '1234567']);

        $docente->delete();

        $this->assertDatabaseHas('grupo_materia', [
            'grupo_id' => $this->grupo->id, 'materia_sigla' => 'FIS', 'docente_ci' => null,
        ]);
    }

    public function test_asignar_a_materia_no_cursada_da_404(): void
    {
        $this->docente();

        $this->actingAs($this->coordinador(), 'api')
            ->putJson("/api/academic-management/grupos/{$this->grupo->id}/materias/FIS/docente", ['ci' => '1234567'])
            ->assertNotFound();
    }
}
