<?php

namespace Tests\Feature\Payments;

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

class PagoTest extends TestCase
{
    use RefreshDatabase;

    private int $convId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthenticationSeeder::class);

        Facultad::create(['codigo' => '187', 'nombre' => 'FICCT', 'abreviatura' => 'FICCT']);
        Carrera::create(['codigo' => '187-09', 'nombre' => 'Sis', 'facultad_codigo' => '187']);
        Carrera::create(['codigo' => '187-10', 'nombre' => 'Inf', 'facultad_codigo' => '187']);
        Postulante::create(['documento' => '9876543', 'nombres' => 'M', 'apellidos' => 'Q', 'email' => 'm@e.com', 'fecha_nacimiento' => '2007-01-01', 'colegio' => 'X', 'ciudad' => 'SC']);
        $conv = Convocatoria::create(['nombre' => 'CUP', 'gestion' => '2026', 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-02-01', 'estado' => 'ABIERTA']);
        $this->convId = $conv->id;
        Postulacion::create([
            'postulante_documento' => '9876543', 'convocatoria_id' => $conv->id,
            'carrera_primera_codigo' => '187-09', 'carrera_segunda_codigo' => '187-10', 'estado' => 'VERIFICADO',
        ]);
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
            'postulante_documento' => '9876543',
            'convocatoria_id' => $this->convId,
            'monto' => 350.00,
            'concepto' => 'Inscripción CUP 2026',
            'metodo' => 'QR',
            'fecha_pago' => '2026-01-15 10:30',
        ], $o);
    }

    public function test_registra_pago_pendiente(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->postJson('/api/payments/pagos', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.estado', 'PENDIENTE')
            ->assertJsonPath('data.metodo', 'QR');

        $this->assertDatabaseHas('pagos', ['postulante_documento' => '9876543', 'convocatoria_id' => $this->convId, 'estado' => 'PENDIENTE']);
    }

    public function test_rechaza_si_no_existe_postulacion(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->postJson('/api/payments/pagos', $this->payload(['postulante_documento' => '0000000']))
            ->assertStatus(422);
    }

    public function test_metodo_invalido_es_rechazado(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->postJson('/api/payments/pagos', $this->payload(['metodo' => 'CHEQUE']))
            ->assertStatus(422);
    }

    public function test_lista_pagos_por_postulante(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin, 'api')->postJson('/api/payments/pagos', $this->payload())->assertCreated();

        $this->actingAs($admin, 'api')
            ->getJson('/api/payments/pagos?postulante=9876543')
            ->assertOk()
            ->assertJsonPath('data.0.postulante_documento', '9876543');
    }

    public function test_sin_permiso_no_accede(): void
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole(RoleName::DOCENTE);

        $this->actingAs($u, 'api')
            ->getJson('/api/payments/pagos')
            ->assertForbidden();
    }
}
