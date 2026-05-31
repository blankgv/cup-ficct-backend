<?php

namespace Tests\Feature\AcademicManagement;

use App\Modules\AcademicManagement\Models\Docente;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\User;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocenteTest extends TestCase
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

    private function payload(array $o = []): array
    {
        return array_merge([
            'ci' => '1234567',
            'nombres' => 'Juan Carlos',
            'apellidos' => 'Pérez López',
            'email' => 'jperez@cup-ficct.local',
            'telefono' => '70000000',
            'profesion' => 'Ing. Matemático',
        ], $o);
    }

    public function test_crea_docente(): void
    {
        $this->actingAs($this->coordinador(), 'api')
            ->postJson('/api/academic-management/docentes', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.ci', '1234567');

        $this->assertDatabaseHas('docentes', ['ci' => '1234567', 'email' => 'jperez@cup-ficct.local']);
    }

    public function test_ci_duplicado_es_rechazado(): void
    {
        Docente::create($this->payload());

        $this->actingAs($this->coordinador(), 'api')
            ->postJson('/api/academic-management/docentes', $this->payload(['email' => 'otro@cup-ficct.local']))
            ->assertStatus(422);
    }

    public function test_actualiza_docente(): void
    {
        Docente::create($this->payload());

        $this->actingAs($this->coordinador(), 'api')
            ->putJson('/api/academic-management/docentes/1234567', ['profesion' => 'Físico'])
            ->assertOk()
            ->assertJsonPath('data.profesion', 'Físico');
    }

    public function test_elimina_docente(): void
    {
        Docente::create($this->payload());

        $this->actingAs($this->coordinador(), 'api')
            ->deleteJson('/api/academic-management/docentes/1234567')
            ->assertOk();

        $this->assertDatabaseMissing('docentes', ['ci' => '1234567']);
    }

    public function test_sin_permiso_academic_no_accede(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);
        $user->assignRole(RoleName::POSTULANTE);

        $this->actingAs($user, 'api')
            ->getJson('/api/academic-management/docentes')
            ->assertForbidden();
    }
}
