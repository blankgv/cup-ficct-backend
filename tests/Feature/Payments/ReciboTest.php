<?php

namespace Tests\Feature\Payments;

use App\Modules\AcademicManagement\Models\Carrera;
use App\Modules\AcademicManagement\Models\Facultad;
use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\ApplicantAdmission\Models\Postulacion;
use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\User;
use App\Modules\Payments\Models\Pago;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReciboTest extends TestCase
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

    private function postulante(): User
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole(RoleName::POSTULANTE);

        return $u;
    }

    private function pago(string $estado): Pago
    {
        return Pago::create([
            'postulante_documento' => '9876543', 'convocatoria_id' => $this->convId,
            'monto' => 350.00, 'concepto' => 'Inscripción CUP 2026', 'metodo' => 'QR',
            'fecha_pago' => '2026-01-15 10:30', 'estado' => $estado,
        ]);
    }

    public function test_genera_recibo_de_pago_pagado(): void
    {
        Storage::fake('r2');
        $pago = $this->pago('PAGADO');

        $this->actingAs($this->postulante(), 'api')
            ->get("/api/payments/pagos/{$pago->id}/recibo")
            ->assertRedirect();

        Storage::disk('r2')->assertExists("recibos/{$pago->id}.pdf");
    }

    public function test_no_genera_recibo_si_no_esta_pagado(): void
    {
        Storage::fake('r2');
        $pago = $this->pago('PENDIENTE');

        $this->actingAs($this->postulante(), 'api')
            ->getJson("/api/payments/pagos/{$pago->id}/recibo")
            ->assertStatus(422);

        Storage::disk('r2')->assertMissing("recibos/{$pago->id}.pdf");
    }
}
