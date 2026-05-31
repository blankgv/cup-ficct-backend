<?php

namespace Tests\Feature\ApplicantAdmission;

use App\Modules\AcademicManagement\Models\Carrera;
use App\Modules\AcademicManagement\Models\Facultad;
use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\User;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConvocatoriaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthenticationSeeder::class);
        Facultad::create(['codigo' => '187', 'nombre' => 'FICCT', 'abreviatura' => 'FICCT']);
        Carrera::create(['codigo' => '187-09', 'nombre' => 'Sistemas', 'facultad_codigo' => '187']);
        Carrera::create(['codigo' => '187-10', 'nombre' => 'Informática', 'facultad_codigo' => '187']);
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
            'nombre' => 'Admisión CUP 2026-I',
            'gestion' => '2026',
            'fecha_inicio' => '2026-01-10',
            'fecha_fin' => '2026-02-10',
        ], $o);
    }

    public function test_crea_convocatoria(): void
    {
        $this->actingAs($this->coordinador(), 'api')
            ->postJson('/api/applicant-admission/convocatorias', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.estado', 'ABIERTA');

        $this->assertDatabaseHas('convocatorias', ['nombre' => 'Admisión CUP 2026-I']);
    }

    public function test_fecha_fin_antes_de_inicio_es_rechazada(): void
    {
        $this->actingAs($this->coordinador(), 'api')
            ->postJson('/api/applicant-admission/convocatorias', $this->payload(['fecha_fin' => '2025-12-01']))
            ->assertStatus(422);
    }

    public function test_fija_cupos_de_carrera(): void
    {
        $conv = Convocatoria::create($this->payload() + ['estado' => 'ABIERTA']);

        $this->actingAs($this->coordinador(), 'api')
            ->putJson("/api/applicant-admission/convocatorias/{$conv->id}/cupos", ['carrera_codigo' => '187-09', 'cupos' => 80])
            ->assertOk()
            ->assertJsonPath('data.0.carrera_codigo', '187-09')
            ->assertJsonPath('data.0.cupos', 80);

        $this->assertDatabaseHas('carrera_convocatoria', ['convocatoria_id' => $conv->id, 'carrera_codigo' => '187-09', 'cupos' => 80]);
    }

    public function test_actualiza_cupos_existentes(): void
    {
        $conv = Convocatoria::create($this->payload() + ['estado' => 'ABIERTA']);
        $coord = $this->coordinador();

        $this->actingAs($coord, 'api')->putJson("/api/applicant-admission/convocatorias/{$conv->id}/cupos", ['carrera_codigo' => '187-09', 'cupos' => 80])->assertOk();
        $this->actingAs($coord, 'api')
            ->putJson("/api/applicant-admission/convocatorias/{$conv->id}/cupos", ['carrera_codigo' => '187-09', 'cupos' => 50])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.cupos', 50);
    }

    public function test_quita_carrera_de_convocatoria(): void
    {
        $conv = Convocatoria::create($this->payload() + ['estado' => 'ABIERTA']);
        $coord = $this->coordinador();
        $this->actingAs($coord, 'api')->putJson("/api/applicant-admission/convocatorias/{$conv->id}/cupos", ['carrera_codigo' => '187-09', 'cupos' => 80])->assertOk();

        $this->actingAs($coord, 'api')
            ->deleteJson("/api/applicant-admission/convocatorias/{$conv->id}/cupos/187-09")
            ->assertOk();

        $this->assertDatabaseMissing('carrera_convocatoria', ['convocatoria_id' => $conv->id, 'carrera_codigo' => '187-09']);
    }

    public function test_sin_permiso_no_accede(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);
        $user->assignRole(RoleName::DOCENTE);

        $this->actingAs($user, 'api')
            ->getJson('/api/applicant-admission/convocatorias')
            ->assertForbidden();
    }
}
