<?php

namespace Tests\Feature\AcademicManagement;

use App\Modules\AcademicManagement\Models\Materia;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\User;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MateriaTest extends TestCase
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

    public function test_coordinador_crea_materia(): void
    {
        $this->actingAs($this->user(RoleName::COORDINADOR), 'api')
            ->postJson('/api/academic-management/materias', [
                'nombre' => 'Matemática',
                'sigla' => 'MAT',
                'peso' => 0.40,
            ])
            ->assertCreated()
            ->assertJsonPath('data.sigla', 'MAT')
            ->assertJsonPath('data.peso', 0.4);

        $this->assertDatabaseHas('materias', ['sigla' => 'MAT']);
    }

    public function test_lista_materias(): void
    {
        Materia::create(['nombre' => 'Física', 'sigla' => 'FIS', 'peso' => 0.30]);

        $this->actingAs($this->user(RoleName::COORDINADOR), 'api')
            ->getJson('/api/academic-management/materias')
            ->assertOk()
            ->assertJsonPath('data.0.sigla', 'FIS');
    }

    public function test_peso_invalido_es_rechazado(): void
    {
        $this->actingAs($this->user(RoleName::COORDINADOR), 'api')
            ->postJson('/api/academic-management/materias', [
                'nombre' => 'Mala',
                'sigla' => 'MAL',
                'peso' => 1.5,
            ])
            ->assertStatus(422);
    }

    public function test_sin_permiso_academic_no_accede(): void
    {
        $this->actingAs($this->user(RoleName::POSTULANTE), 'api')
            ->getJson('/api/academic-management/materias')
            ->assertForbidden();
    }
}
