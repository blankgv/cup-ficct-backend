<?php

namespace Tests\Feature\AcademicManagement;

use App\Modules\AcademicManagement\Models\Facultad;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\User;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarreraTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthenticationSeeder::class);
        Facultad::create(['codigo' => '187', 'nombre' => 'FICCT', 'abreviatura' => 'FICCT']);
    }

    private function coordinador(): User
    {
        $user = User::factory()->create(['must_change_password' => false]);
        $user->assignRole(RoleName::COORDINADOR);

        return $user;
    }

    public function test_crea_facultad(): void
    {
        $this->actingAs($this->coordinador(), 'api')
            ->postJson('/api/academic-management/facultades', ['codigo' => '999', 'nombre' => 'Otra', 'abreviatura' => 'OT'])
            ->assertCreated()
            ->assertJsonPath('data.codigo', '999');
    }

    public function test_crea_carrera_con_codigo_valido(): void
    {
        $this->actingAs($this->coordinador(), 'api')
            ->postJson('/api/academic-management/carreras', ['codigo' => '187-09', 'nombre' => 'Ing. Sistemas', 'facultad_codigo' => '187'])
            ->assertCreated()
            ->assertJsonPath('data.codigo', '187-09')
            ->assertJsonPath('data.facultad_codigo', '187');

        $this->assertDatabaseHas('carreras', ['codigo' => '187-09']);
    }

    public function test_codigo_carrera_debe_empezar_con_facultad(): void
    {
        $this->actingAs($this->coordinador(), 'api')
            ->postJson('/api/academic-management/carreras', ['codigo' => '200-09', 'nombre' => 'Mala', 'facultad_codigo' => '187'])
            ->assertStatus(422);
    }

    public function test_facultad_inexistente_es_rechazada(): void
    {
        $this->actingAs($this->coordinador(), 'api')
            ->postJson('/api/academic-management/carreras', ['codigo' => '500-01', 'nombre' => 'X', 'facultad_codigo' => '500'])
            ->assertStatus(422);
    }

    public function test_lista_carreras_por_facultad(): void
    {
        $coord = $this->coordinador();
        $this->actingAs($coord, 'api')->postJson('/api/academic-management/carreras', ['codigo' => '187-09', 'nombre' => 'Sis', 'facultad_codigo' => '187'])->assertCreated();

        $this->actingAs($coord, 'api')
            ->getJson('/api/academic-management/carreras?facultad=187')
            ->assertOk()
            ->assertJsonPath('data.0.codigo', '187-09');
    }

    public function test_sin_permiso_no_accede(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);
        $user->assignRole(RoleName::DOCENTE);

        $this->actingAs($user, 'api')
            ->getJson('/api/academic-management/carreras')
            ->assertForbidden();
    }
}
