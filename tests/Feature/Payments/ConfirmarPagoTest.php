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
use Tests\TestCase;

class ConfirmarPagoTest extends TestCase
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

    private function pago(string $estado = 'PENDIENTE'): Pago
    {
        return Pago::create([
            'postulante_documento' => '9876543', 'convocatoria_id' => $this->convId,
            'monto' => 350.00, 'concepto' => 'Inscripción CUP 2026', 'metodo' => 'QR',
            'fecha_pago' => '2026-01-15 10:30', 'estado' => $estado,
        ]);
    }

    public function test_confirma_pago_pendiente(): void
    {
        $admin = $this->admin();
        $pago = $this->pago();

        $this->actingAs($admin, 'api')
            ->postJson("/api/payments/pagos/{$pago->id}/confirmar")
            ->assertOk()
            ->assertJsonPath('data.estado', 'PAGADO')
            ->assertJsonPath('data.confirmado_por', $admin->id);

        $this->assertDatabaseHas('pagos', ['id' => $pago->id, 'estado' => 'PAGADO', 'confirmado_por' => $admin->id]);
        $this->assertNotNull($pago->fresh()->confirmado_at);
    }

    public function test_rechaza_pago_con_motivo(): void
    {
        $admin = $this->admin();
        $pago = $this->pago();

        $this->actingAs($admin, 'api')
            ->postJson("/api/payments/pagos/{$pago->id}/rechazar", ['motivo' => 'Comprobante ilegible'])
            ->assertOk()
            ->assertJsonPath('data.estado', 'RECHAZADO')
            ->assertJsonPath('data.motivo_rechazo', 'Comprobante ilegible');

        $this->assertDatabaseHas('pagos', ['id' => $pago->id, 'estado' => 'RECHAZADO', 'motivo_rechazo' => 'Comprobante ilegible']);
    }

    public function test_rechazar_requiere_motivo(): void
    {
        $pago = $this->pago();

        $this->actingAs($this->admin(), 'api')
            ->postJson("/api/payments/pagos/{$pago->id}/rechazar", [])
            ->assertStatus(422);
    }

    public function test_no_confirma_pago_no_pendiente(): void
    {
        $pago = $this->pago('PAGADO');

        $this->actingAs($this->admin(), 'api')
            ->postJson("/api/payments/pagos/{$pago->id}/confirmar")
            ->assertStatus(422);
    }

    public function test_sin_permiso_no_confirma(): void
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole(RoleName::DOCENTE);
        $pago = $this->pago();

        $this->actingAs($u, 'api')
            ->postJson("/api/payments/pagos/{$pago->id}/confirmar")
            ->assertForbidden();
    }
}
