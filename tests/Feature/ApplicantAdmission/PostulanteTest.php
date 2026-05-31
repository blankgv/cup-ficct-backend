<?php

namespace Tests\Feature\ApplicantAdmission;

use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\User;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostulanteTest extends TestCase
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
            'documento' => '9876543',
            'nombres' => 'María José',
            'apellidos' => 'Quispe Vargas',
            'email' => 'mquispe@example.com',
            'telefono' => '70000000',
            'fecha_nacimiento' => '2007-03-15',
            'colegio' => 'Colegio Nacional',
            'ciudad' => 'Santa Cruz',
        ], $o);
    }

    public function test_crea_postulante(): void
    {
        $this->actingAs($this->coordinador(), 'api')
            ->postJson('/api/applicant-admission/postulantes', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.documento', '9876543')
            ->assertJsonPath('data.fecha_nacimiento', '2007-03-15');

        $this->assertDatabaseHas('postulantes', ['documento' => '9876543']);
    }

    public function test_documento_duplicado_es_rechazado(): void
    {
        Postulante::create($this->payload());

        $this->actingAs($this->coordinador(), 'api')
            ->postJson('/api/applicant-admission/postulantes', $this->payload(['email' => 'otro@example.com']))
            ->assertStatus(422);
    }

    public function test_busca_postulante(): void
    {
        Postulante::create($this->payload());

        $this->actingAs($this->coordinador(), 'api')
            ->getJson('/api/applicant-admission/postulantes?search=Quispe')
            ->assertOk()
            ->assertJsonPath('data.0.documento', '9876543');
    }

    public function test_actualiza_postulante(): void
    {
        Postulante::create($this->payload());

        $this->actingAs($this->coordinador(), 'api')
            ->putJson('/api/applicant-admission/postulantes/9876543', ['colegio' => 'Otro Colegio'])
            ->assertOk()
            ->assertJsonPath('data.colegio', 'Otro Colegio');
    }

    public function test_elimina_postulante(): void
    {
        Postulante::create($this->payload());

        $this->actingAs($this->coordinador(), 'api')
            ->deleteJson('/api/applicant-admission/postulantes/9876543')
            ->assertOk();

        $this->assertDatabaseMissing('postulantes', ['documento' => '9876543']);
    }

    public function test_sin_permiso_no_accede(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);
        $user->assignRole(RoleName::DOCENTE);

        $this->actingAs($user, 'api')
            ->getJson('/api/applicant-admission/postulantes')
            ->assertForbidden();
    }
}
