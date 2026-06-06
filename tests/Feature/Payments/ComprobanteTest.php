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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ComprobanteTest extends TestCase
{
    use RefreshDatabase;

    private Pago $pago;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthenticationSeeder::class);

        Facultad::create(['codigo' => '187', 'nombre' => 'FICCT', 'abreviatura' => 'FICCT']);
        Carrera::create(['codigo' => '187-09', 'nombre' => 'Sis', 'facultad_codigo' => '187']);
        Carrera::create(['codigo' => '187-10', 'nombre' => 'Inf', 'facultad_codigo' => '187']);
        Postulante::create(['documento' => '9876543', 'nombres' => 'M', 'apellidos' => 'Q', 'email' => 'm@e.com', 'fecha_nacimiento' => '2007-01-01', 'colegio' => 'X', 'ciudad' => 'SC']);
        $conv = Convocatoria::create(['nombre' => 'CUP', 'gestion' => '2026', 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-02-01', 'estado' => 'ABIERTA']);
        Postulacion::create([
            'postulante_documento' => '9876543', 'convocatoria_id' => $conv->id,
            'carrera_primera_codigo' => '187-09', 'carrera_segunda_codigo' => '187-10', 'estado' => 'VERIFICADO',
        ]);
        $this->pago = Pago::create([
            'postulante_documento' => '9876543', 'convocatoria_id' => $conv->id,
            'monto' => 350.00, 'concepto' => 'Inscripción CUP 2026', 'metodo' => 'QR',
            'fecha_pago' => '2026-01-15 10:30', 'estado' => 'PENDIENTE',
        ]);
    }

    private function postulante(): User
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole(RoleName::POSTULANTE);

        return $u;
    }

    private function admin(): User
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole(RoleName::ADMINISTRADOR);

        return $u;
    }

    public function test_sube_comprobante_a_r2(): void
    {
        Storage::fake('r2');

        $this->actingAs($this->postulante(), 'api')
            ->postJson("/api/payments/pagos/{$this->pago->id}/comprobantes", [
                'comprobante' => UploadedFile::fake()->create('recibo.pdf', 100, 'application/pdf'),
            ])
            ->assertCreated()
            ->assertJsonPath('data.pago_id', $this->pago->id)
            ->assertJsonPath('data.nombre_original', 'recibo.pdf');

        $this->assertDatabaseHas('comprobantes', ['pago_id' => $this->pago->id, 'nombre_original' => 'recibo.pdf']);
        Storage::disk('r2')->assertExists(Pago::find($this->pago->id)->comprobantes()->first()->path);
    }

    public function test_rechaza_archivo_no_permitido(): void
    {
        Storage::fake('r2');

        $this->actingAs($this->postulante(), 'api')
            ->postJson("/api/payments/pagos/{$this->pago->id}/comprobantes", [
                'comprobante' => UploadedFile::fake()->create('virus.exe', 10),
            ])
            ->assertStatus(422);
    }

    public function test_lista_comprobantes_del_pago(): void
    {
        Storage::fake('r2');
        $this->actingAs($this->postulante(), 'api')->postJson("/api/payments/pagos/{$this->pago->id}/comprobantes", [
            'comprobante' => UploadedFile::fake()->create('recibo.pdf', 100, 'application/pdf'),
        ])->assertCreated();

        $this->actingAs($this->admin(), 'api')
            ->getJson("/api/payments/pagos/{$this->pago->id}/comprobantes")
            ->assertOk()
            ->assertJsonPath('data.0.pago_id', $this->pago->id);
    }

    public function test_lista_requiere_permiso(): void
    {
        $this->actingAs($this->postulante(), 'api')
            ->getJson("/api/payments/pagos/{$this->pago->id}/comprobantes")
            ->assertForbidden();
    }
}
