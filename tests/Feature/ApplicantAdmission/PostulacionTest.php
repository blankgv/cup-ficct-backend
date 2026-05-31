<?php

namespace Tests\Feature\ApplicantAdmission;

use App\Modules\AcademicManagement\Models\Carrera;
use App\Modules\AcademicManagement\Models\Facultad;
use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\User;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostulacionTest extends TestCase
{
    use RefreshDatabase;

    private Convocatoria $conv;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthenticationSeeder::class);

        Facultad::create(['codigo' => '187', 'nombre' => 'FICCT', 'abreviatura' => 'FICCT']);
        Carrera::create(['codigo' => '187-09', 'nombre' => 'Sistemas', 'facultad_codigo' => '187']);
        Carrera::create(['codigo' => '187-10', 'nombre' => 'Informática', 'facultad_codigo' => '187']);
        Carrera::create(['codigo' => '187-11', 'nombre' => 'Redes', 'facultad_codigo' => '187']);

        Postulante::create([
            'documento' => '9876543', 'nombres' => 'María', 'apellidos' => 'Quispe',
            'email' => 'm@example.com', 'fecha_nacimiento' => '2007-01-01', 'colegio' => 'X',
        ]);

        $this->conv = Convocatoria::create([
            'nombre' => 'CUP 2026', 'gestion' => '2026',
            'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-02-01', 'estado' => 'ABIERTA',
        ]);
        $this->conv->carreras()->attach('187-09', ['cupos' => 80]);
        $this->conv->carreras()->attach('187-10', ['cupos' => 50]);
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
            'convocatoria_id' => $this->conv->id,
            'carrera_primera_codigo' => '187-09',
            'carrera_segunda_codigo' => '187-10',
        ], $o);
    }

    private function url(): string
    {
        return '/api/applicant-admission/postulantes/9876543/postulaciones';
    }

    public function test_postula_en_estado_pendiente(): void
    {
        $this->actingAs($this->coordinador(), 'api')
            ->postJson($this->url(), $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.estado', 'PENDIENTE')
            ->assertJsonPath('data.carrera_primera_codigo', '187-09');

        $this->assertDatabaseHas('postulaciones', [
            'postulante_documento' => '9876543', 'convocatoria_id' => $this->conv->id, 'estado' => 'PENDIENTE',
        ]);
    }

    public function test_no_postula_dos_veces_en_la_misma_convocatoria(): void
    {
        $coord = $this->coordinador();
        $this->actingAs($coord, 'api')->postJson($this->url(), $this->payload())->assertCreated();
        $this->actingAs($coord, 'api')->postJson($this->url(), $this->payload())->assertStatus(422);
    }

    public function test_primera_y_segunda_no_pueden_ser_iguales(): void
    {
        $this->actingAs($this->coordinador(), 'api')
            ->postJson($this->url(), $this->payload(['carrera_segunda_codigo' => '187-09']))
            ->assertStatus(422);
    }

    public function test_carrera_no_ofertada_es_rechazada(): void
    {
        // 187-11 existe pero NO está ofertada en la convocatoria.
        $this->actingAs($this->coordinador(), 'api')
            ->postJson($this->url(), $this->payload(['carrera_segunda_codigo' => '187-11']))
            ->assertStatus(422);
    }

    public function test_convocatoria_cerrada_no_acepta(): void
    {
        $this->conv->update(['estado' => 'CERRADA']);

        $this->actingAs($this->coordinador(), 'api')
            ->postJson($this->url(), $this->payload())
            ->assertStatus(422);
    }

    public function test_cancela_postulacion(): void
    {
        $coord = $this->coordinador();
        $this->actingAs($coord, 'api')->postJson($this->url(), $this->payload())->assertCreated();

        $this->actingAs($coord, 'api')
            ->deleteJson($this->url()."/{$this->conv->id}")
            ->assertOk();

        $this->assertDatabaseMissing('postulaciones', ['postulante_documento' => '9876543', 'convocatoria_id' => $this->conv->id]);
    }
}
