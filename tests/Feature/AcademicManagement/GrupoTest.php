<?php

namespace Tests\Feature\AcademicManagement;

use App\Modules\AcademicManagement\Models\Grupo;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\User;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrupoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthenticationSeeder::class);
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
            'codigo' => 'A',
            'turno' => 'MANANA',
            'capacidad' => 70,
            'gestion' => '2026',
        ], $override);
    }

    public function test_crea_grupo(): void
    {
        $this->actingAs($this->coordinador(), 'api')
            ->postJson('/api/academic-management/grupos', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.codigo', 'A')
            ->assertJsonPath('data.turno', 'MANANA');

        $this->assertDatabaseHas('grupos', ['codigo' => 'A', 'gestion' => '2026']);
    }

    public function test_capacidad_mayor_a_70_es_rechazada(): void
    {
        $this->actingAs($this->coordinador(), 'api')
            ->postJson('/api/academic-management/grupos', $this->payload(['capacidad' => 71]))
            ->assertStatus(422);
    }

    public function test_codigo_duplicado_en_misma_gestion_es_rechazado(): void
    {
        Grupo::create($this->payload());

        $this->actingAs($this->coordinador(), 'api')
            ->postJson('/api/academic-management/grupos', $this->payload())
            ->assertStatus(422);
    }

    public function test_mismo_codigo_en_otra_gestion_es_valido(): void
    {
        Grupo::create($this->payload(['gestion' => '2025']));

        $this->actingAs($this->coordinador(), 'api')
            ->postJson('/api/academic-management/grupos', $this->payload(['gestion' => '2026']))
            ->assertCreated();
    }

    public function test_sin_permiso_academic_no_accede(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);
        $user->assignRole(RoleName::POSTULANTE);

        $this->actingAs($user, 'api')
            ->getJson('/api/academic-management/grupos')
            ->assertForbidden();
    }
}
