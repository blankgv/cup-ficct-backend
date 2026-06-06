<?php

namespace Tests\Feature\AcademicManagement;

use App\Modules\AcademicManagement\Models\Periodo;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\User;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthenticationSeeder::class);
    }

    private function admin(): User
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole(RoleName::ADMINISTRADOR);

        return $u;
    }

    private function payload(array $o = []): array
    {
        return array_merge([
            'codigo' => '1/2026',
            'gestion' => '2026',
            'fecha_inicio_clases' => '2026-03-02',
            'fecha_fin_clases' => '2026-06-30',
        ], $o);
    }

    public function test_crea_periodo(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->postJson('/api/academic-management/periodos', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.codigo', '1/2026');

        $this->assertDatabaseHas('periodos', ['codigo' => '1/2026', 'gestion' => '2026']);
    }

    public function test_codigo_unico(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin, 'api')->postJson('/api/academic-management/periodos', $this->payload())->assertCreated();

        $this->actingAs($admin, 'api')
            ->postJson('/api/academic-management/periodos', $this->payload())
            ->assertStatus(422);
    }

    public function test_fin_antes_de_inicio_es_rechazado(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->postJson('/api/academic-management/periodos', $this->payload(['fecha_fin_clases' => '2026-03-01']))
            ->assertStatus(422);
    }

    public function test_actualiza_periodo(): void
    {
        $periodo = Periodo::create($this->payload());

        $this->actingAs($this->admin(), 'api')
            ->putJson("/api/academic-management/periodos/{$periodo->id}", ['fecha_fin_clases' => '2026-07-15'])
            ->assertOk()
            ->assertJsonPath('data.fecha_fin_clases', '2026-07-15');
    }

    public function test_elimina_periodo(): void
    {
        $periodo = Periodo::create($this->payload());

        $this->actingAs($this->admin(), 'api')
            ->deleteJson("/api/academic-management/periodos/{$periodo->id}")
            ->assertOk();

        $this->assertDatabaseMissing('periodos', ['id' => $periodo->id]);
    }

    public function test_sin_permiso_no_accede(): void
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole(RoleName::POSTULANTE);

        $this->actingAs($u, 'api')
            ->postJson('/api/academic-management/periodos', $this->payload())
            ->assertForbidden();
    }
}
