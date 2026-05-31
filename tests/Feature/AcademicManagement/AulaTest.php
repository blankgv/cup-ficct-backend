<?php

namespace Tests\Feature\AcademicManagement;

use App\Modules\AcademicManagement\Models\Aula;
use App\Modules\AcademicManagement\Models\Modulo;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\User;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AulaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthenticationSeeder::class);
        Modulo::create(['numero' => '236', 'nombre' => 'Módulo 236']);
        Modulo::create(['numero' => '237', 'nombre' => 'Módulo 237']);
    }

    private function coordinador(): User
    {
        $user = User::factory()->create(['must_change_password' => false]);
        $user->assignRole(RoleName::COORDINADOR);

        return $user;
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'numero' => 1,
            'nombre' => 'Laboratorio A',
            'capacidad' => 40,
            'piso' => 2,
            'tipo' => 'LABORATORIO',
        ], $override);
    }

    public function test_crea_aula_en_modulo(): void
    {
        $this->actingAs($this->coordinador(), 'api')
            ->postJson('/api/academic-management/modulos/236/aulas', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.modulo_numero', '236')
            ->assertJsonPath('data.numero', 1)
            ->assertJsonPath('data.tipo', 'LABORATORIO');

        $this->assertDatabaseHas('aulas', ['modulo_numero' => '236', 'numero' => 1]);
    }

    public function test_numero_duplicado_en_mismo_modulo_es_rechazado(): void
    {
        Aula::create($this->payload(['modulo_numero' => '236']));

        $this->actingAs($this->coordinador(), 'api')
            ->postJson('/api/academic-management/modulos/236/aulas', $this->payload())
            ->assertStatus(422);
    }

    public function test_mismo_numero_en_distinto_modulo_es_valido(): void
    {
        Aula::create($this->payload(['modulo_numero' => '236']));

        $this->actingAs($this->coordinador(), 'api')
            ->postJson('/api/academic-management/modulos/237/aulas', $this->payload())
            ->assertCreated();

        $this->assertDatabaseHas('aulas', ['modulo_numero' => '237', 'numero' => 1]);
    }

    public function test_lista_aulas_del_modulo(): void
    {
        Aula::create($this->payload(['modulo_numero' => '236', 'numero' => 1]));
        Aula::create($this->payload(['modulo_numero' => '237', 'numero' => 5]));

        $this->actingAs($this->coordinador(), 'api')
            ->getJson('/api/academic-management/modulos/236/aulas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.numero', 1);
    }

    public function test_actualiza_aula(): void
    {
        Aula::create($this->payload(['modulo_numero' => '236']));

        $this->actingAs($this->coordinador(), 'api')
            ->putJson('/api/academic-management/modulos/236/aulas/1', ['capacidad' => 60, 'tipo' => 'AUDITORIO'])
            ->assertOk()
            ->assertJsonPath('data.capacidad', 60)
            ->assertJsonPath('data.tipo', 'AUDITORIO');
    }

    public function test_elimina_aula(): void
    {
        Aula::create($this->payload(['modulo_numero' => '236']));

        $this->actingAs($this->coordinador(), 'api')
            ->deleteJson('/api/academic-management/modulos/236/aulas/1')
            ->assertOk();

        $this->assertDatabaseMissing('aulas', ['modulo_numero' => '236', 'numero' => 1]);
    }

    public function test_sin_permiso_academic_no_accede(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);
        $user->assignRole(RoleName::POSTULANTE);

        $this->actingAs($user, 'api')
            ->getJson('/api/academic-management/modulos/236/aulas')
            ->assertForbidden();
    }
}
