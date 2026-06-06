<?php

namespace Tests\Feature\AcademicManagement;

use App\Modules\AcademicManagement\Models\Feriado;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\User;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FeriadoTest extends TestCase
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

    public function test_crea_feriado(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->postJson('/api/academic-management/feriados', [
                'fecha' => '2026-05-01', 'descripcion' => 'Día del Trabajo', 'gestion' => '2026',
            ])
            ->assertCreated()
            ->assertJsonPath('data.fecha', '2026-05-01');

        $this->assertDatabaseHas('feriados', ['fecha' => '2026-05-01', 'gestion' => '2026']);
    }

    public function test_lista_filtra_por_gestion(): void
    {
        Feriado::create(['fecha' => '2026-05-01', 'descripcion' => 'A', 'gestion' => '2026']);
        Feriado::create(['fecha' => '2025-05-01', 'descripcion' => 'B', 'gestion' => '2025']);

        $this->actingAs($this->admin(), 'api')
            ->getJson('/api/academic-management/feriados?gestion=2026')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.fecha', '2026-05-01');
    }

    public function test_elimina_feriado(): void
    {
        Feriado::create(['fecha' => '2026-05-01', 'descripcion' => 'A', 'gestion' => '2026']);

        $this->actingAs($this->admin(), 'api')
            ->deleteJson('/api/academic-management/feriados/2026-05-01')
            ->assertOk();

        $this->assertDatabaseMissing('feriados', ['fecha' => '2026-05-01']);
    }

    public function test_importa_desde_api(): void
    {
        config(['services.feriados.api_url' => 'https://date.nager.at/api/v3/PublicHolidays', 'services.feriados.pais' => 'BO']);

        Http::fake([
            '*/2026/BO' => Http::response([
                ['date' => '2026-01-01', 'localName' => 'Año Nuevo', 'name' => 'New Year'],
                ['date' => '2026-05-01', 'localName' => 'Día del Trabajo', 'name' => 'Labour Day'],
            ]),
        ]);

        $this->actingAs($this->admin(), 'api')
            ->postJson('/api/academic-management/feriados/importar', ['gestion' => '2026'])
            ->assertOk()
            ->assertJson(['importados' => 2]);

        $this->assertDatabaseHas('feriados', ['fecha' => '2026-01-01', 'descripcion' => 'Año Nuevo', 'gestion' => '2026']);
        $this->assertDatabaseHas('feriados', ['fecha' => '2026-05-01', 'descripcion' => 'Día del Trabajo', 'gestion' => '2026']);
    }

    public function test_importar_sin_config_da_422(): void
    {
        config(['services.feriados.api_url' => '', 'services.feriados.pais' => '']);

        $this->actingAs($this->admin(), 'api')
            ->postJson('/api/academic-management/feriados/importar', ['gestion' => '2026'])
            ->assertStatus(422);
    }

    public function test_sin_permiso_no_accede(): void
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole(RoleName::POSTULANTE);

        $this->actingAs($u, 'api')
            ->getJson('/api/academic-management/feriados')
            ->assertForbidden();
    }
}
