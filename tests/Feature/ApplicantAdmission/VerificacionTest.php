<?php

namespace Tests\Feature\ApplicantAdmission;

use App\Modules\AcademicManagement\Models\Carrera;
use App\Modules\AcademicManagement\Models\Facultad;
use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\ApplicantAdmission\Models\Postulacion;
use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\User;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificacionTest extends TestCase
{
    use RefreshDatabase;

    private int $convId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthenticationSeeder::class);

        Facultad::create(['codigo' => '187', 'nombre' => 'FICCT', 'abreviatura' => 'FICCT']);
        Carrera::create(['codigo' => '187-09', 'nombre' => 'Sistemas', 'facultad_codigo' => '187']);
        Carrera::create(['codigo' => '187-10', 'nombre' => 'Informática', 'facultad_codigo' => '187']);
        Postulante::create(['documento' => '9876543', 'nombres' => 'M', 'apellidos' => 'Q', 'email' => 'm@e.com', 'fecha_nacimiento' => '2007-01-01', 'colegio' => 'X']);

        $conv = Convocatoria::create(['nombre' => 'CUP', 'gestion' => '2026', 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-02-01', 'estado' => 'ABIERTA']);
        $this->convId = $conv->id;

        Postulacion::create([
            'postulante_documento' => '9876543', 'convocatoria_id' => $conv->id,
            'carrera_primera_codigo' => '187-09', 'carrera_segunda_codigo' => '187-10', 'estado' => 'PENDIENTE',
        ]);
    }

    private function user(string $role): User
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole($role);

        return $u;
    }

    private function url(string $accion): string
    {
        return "/api/applicant-admission/postulantes/9876543/postulaciones/{$this->convId}/{$accion}";
    }

    public function test_verifica_postulacion(): void
    {
        $this->actingAs($this->user(RoleName::COORDINADOR), 'api')
            ->putJson($this->url('verificar'))
            ->assertOk()
            ->assertJsonPath('data.estado', 'VERIFICADO');

        $this->assertDatabaseHas('postulaciones', ['convocatoria_id' => $this->convId, 'estado' => 'VERIFICADO']);
    }

    public function test_rechaza_con_motivo(): void
    {
        $this->actingAs($this->user(RoleName::COORDINADOR), 'api')
            ->putJson($this->url('rechazar'), ['motivo' => 'Falta certificado'])
            ->assertOk()
            ->assertJsonPath('data.estado', 'RECHAZADO')
            ->assertJsonPath('data.observacion', 'Falta certificado');
    }

    public function test_rechazo_requiere_motivo(): void
    {
        $this->actingAs($this->user(RoleName::COORDINADOR), 'api')
            ->putJson($this->url('rechazar'), [])
            ->assertStatus(422);
    }

    public function test_sin_permiso_verify_no_accede(): void
    {
        $this->actingAs($this->user(RoleName::DOCENTE), 'api')
            ->putJson($this->url('verificar'))
            ->assertForbidden();
    }
}
