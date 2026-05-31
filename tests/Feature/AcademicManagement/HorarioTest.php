<?php

namespace Tests\Feature\AcademicManagement;

use App\Modules\AcademicManagement\Models\Aula;
use App\Modules\AcademicManagement\Models\Grupo;
use App\Modules\AcademicManagement\Models\Materia;
use App\Modules\AcademicManagement\Models\Modulo;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\User;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HorarioTest extends TestCase
{
    use RefreshDatabase;

    private Grupo $g1;
    private Grupo $g2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthenticationSeeder::class);

        foreach (['FIS', 'MAT'] as $s) {
            Materia::create(['sigla' => $s, 'nombre' => $s, 'peso' => 0.25]);
        }

        Modulo::create(['numero' => '236', 'nombre' => 'Módulo 236']);
        Aula::create(['modulo_numero' => '236', 'numero' => 14, 'nombre' => 'A', 'capacidad' => 40, 'piso' => 1, 'tipo' => 'COMUN']);
        Aula::create(['modulo_numero' => '236', 'numero' => 15, 'nombre' => 'B', 'capacidad' => 40, 'piso' => 1, 'tipo' => 'COMUN']);

        $this->g1 = Grupo::create(['codigo' => 'A', 'turno' => 'MANANA', 'capacidad' => 70, 'gestion' => '2026']);
        $this->g2 = Grupo::create(['codigo' => 'B', 'turno' => 'MANANA', 'capacidad' => 70, 'gestion' => '2026']);
        $this->g1->materias()->sync(['FIS', 'MAT']);
        $this->g2->materias()->sync(['FIS']);
    }

    private function coordinador(): User
    {
        $user = User::factory()->create(['must_change_password' => false]);
        $user->assignRole(RoleName::COORDINADOR);

        return $user;
    }

    private function payload(array $o = []): array
    {
        return array_merge([
            'dia' => 'LUNES', 'hora_inicio' => '07:00', 'hora_fin' => '09:00',
            'aula_modulo_numero' => '236', 'aula_numero' => 14,
        ], $o);
    }

    public function test_crea_horario_con_numero_automatico(): void
    {
        $coord = $this->coordinador();

        $this->actingAs($coord, 'api')
            ->postJson("/api/academic-management/grupos/{$this->g1->id}/materias/FIS/horarios", $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.numero', 1)
            ->assertJsonPath('data.aula.numero', 14);

        $this->actingAs($coord, 'api')
            ->postJson("/api/academic-management/grupos/{$this->g1->id}/materias/FIS/horarios", $this->payload(['dia' => 'MARTES']))
            ->assertCreated()
            ->assertJsonPath('data.numero', 2);
    }

    public function test_hora_fin_menor_es_rechazada(): void
    {
        $this->actingAs($this->coordinador(), 'api')
            ->postJson("/api/academic-management/grupos/{$this->g1->id}/materias/FIS/horarios", $this->payload(['hora_fin' => '06:00']))
            ->assertStatus(422);
    }

    public function test_grupo_no_puede_solaparse_en_dos_materias(): void
    {
        $coord = $this->coordinador();
        // FIS lunes 07-09.
        $this->actingAs($coord, 'api')
            ->postJson("/api/academic-management/grupos/{$this->g1->id}/materias/FIS/horarios", $this->payload())
            ->assertCreated();

        // MAT lunes 08-10 mismo grupo → solapa.
        $this->actingAs($coord, 'api')
            ->postJson("/api/academic-management/grupos/{$this->g1->id}/materias/MAT/horarios", $this->payload(['hora_inicio' => '08:00', 'hora_fin' => '10:00', 'aula_numero' => 15]))
            ->assertStatus(422);
    }

    public function test_aula_no_puede_doble_reservarse(): void
    {
        $coord = $this->coordinador();
        // g1 FIS lunes 07-09 aula 14.
        $this->actingAs($coord, 'api')
            ->postJson("/api/academic-management/grupos/{$this->g1->id}/materias/FIS/horarios", $this->payload())
            ->assertCreated();

        // g2 FIS lunes 08-10 misma aula 14 → aula ocupada.
        $this->actingAs($coord, 'api')
            ->postJson("/api/academic-management/grupos/{$this->g2->id}/materias/FIS/horarios", $this->payload(['hora_inicio' => '08:00', 'hora_fin' => '10:00']))
            ->assertStatus(422);
    }

    public function test_404_si_grupo_no_cursa_la_materia(): void
    {
        // g2 no cursa MAT.
        $this->actingAs($this->coordinador(), 'api')
            ->postJson("/api/academic-management/grupos/{$this->g2->id}/materias/MAT/horarios", $this->payload())
            ->assertNotFound();
    }

    public function test_elimina_horario(): void
    {
        $coord = $this->coordinador();
        $this->actingAs($coord, 'api')
            ->postJson("/api/academic-management/grupos/{$this->g1->id}/materias/FIS/horarios", $this->payload())
            ->assertCreated();

        $this->actingAs($coord, 'api')
            ->deleteJson("/api/academic-management/grupos/{$this->g1->id}/materias/FIS/horarios/1")
            ->assertOk();

        $this->assertDatabaseMissing('horarios', ['grupo_id' => $this->g1->id, 'materia_sigla' => 'FIS', 'numero' => 1]);
    }
}
