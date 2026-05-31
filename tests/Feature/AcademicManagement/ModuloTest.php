<?php

namespace Tests\Feature\AcademicManagement;

use App\Modules\AcademicManagement\Models\Modulo;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\User;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuloTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthenticationSeeder::class);
    }

    private function user(string $role): User
    {
        $user = User::factory()->create(['must_change_password' => false]);
        $user->assignRole($role);

        return $user;
    }

    public function test_coordinador_crea_modulo(): void
    {
        $this->actingAs($this->user(RoleName::COORDINADOR), 'api')
            ->postJson('/api/academic-management/modulos', [
                'numero' => '236',
                'nombre' => 'Módulo 236',
                'ubicacion' => 'Campus central',
            ])
            ->assertCreated()
            ->assertJsonPath('data.numero', '236');

        $this->assertDatabaseHas('modulos', ['numero' => '236']);
    }

    public function test_lista_modulos(): void
    {
        Modulo::create(['numero' => '200', 'nombre' => 'Módulo 200']);

        $this->actingAs($this->user(RoleName::COORDINADOR), 'api')
            ->getJson('/api/academic-management/modulos')
            ->assertOk()
            ->assertJsonPath('data.0.numero', '200');
    }

    public function test_actualiza_el_numero(): void
    {
        Modulo::create(['numero' => '236', 'nombre' => 'Módulo 236']);

        $this->actingAs($this->user(RoleName::COORDINADOR), 'api')
            ->putJson('/api/academic-management/modulos/236', ['numero' => '237'])
            ->assertOk()
            ->assertJsonPath('data.numero', '237');

        $this->assertDatabaseHas('modulos', ['numero' => '237']);
        $this->assertDatabaseMissing('modulos', ['numero' => '236']);
    }

    public function test_sin_permiso_academic_no_accede(): void
    {
        $this->actingAs($this->user(RoleName::POSTULANTE), 'api')
            ->getJson('/api/academic-management/modulos')
            ->assertForbidden();
    }
}
