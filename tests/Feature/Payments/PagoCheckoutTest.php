<?php

namespace Tests\Feature\Payments;

use App\Modules\AcademicManagement\Models\Carrera;
use App\Modules\AcademicManagement\Models\Facultad;
use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\ApplicantAdmission\Models\Postulacion;
use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\User;
use App\Modules\Payments\Gateways\PaymentGateway;
use App\Modules\Payments\Gateways\StripeGateway;
use App\Modules\Payments\Models\Pago;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagoCheckoutTest extends TestCase
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
        Postulacion::create(['postulante_documento' => '9876543', 'convocatoria_id' => $conv->id, 'carrera_primera_codigo' => '187-09', 'carrera_segunda_codigo' => '187-10', 'estado' => 'VERIFICADO']);
        $this->pago = Pago::create([
            'postulante_documento' => '9876543', 'convocatoria_id' => $conv->id, 'monto' => 350.00,
            'concepto' => 'Inscripción', 'metodo' => 'TARJETA', 'fecha_pago' => '2026-01-15 10:30', 'estado' => 'PENDIENTE',
        ]);
    }

    private function admin(): User
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole(RoleName::ADMINISTRADOR);

        return $u;
    }

    public function test_checkout_devuelve_url(): void
    {
        // Pasarela por defecto + Stripe falso para no llamar a la API real.
        config(['payments.default_gateway' => 'stripe']);
        $this->app->bind(StripeGateway::class, fn () => new class implements PaymentGateway
        {
            public function checkout(Pago $pago): string
            {
                return 'https://checkout.stripe.com/test/'.$pago->id;
            }
        });

        $this->actingAs($this->admin(), 'api')
            ->postJson("/api/payments/pagos/{$this->pago->id}/checkout")
            ->assertOk()
            ->assertJsonPath('url', "https://checkout.stripe.com/test/{$this->pago->id}");
    }

    public function test_checkout_pasarela_no_soportada_da_422(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->postJson("/api/payments/pagos/{$this->pago->id}/checkout?gateway=bitcoin")
            ->assertStatus(422);
    }

    public function test_webhook_marca_pagado(): void
    {
        config(['services.stripe.webhook_secret' => 'whsec_test']);

        $payload = json_encode([
            'id' => 'evt_1',
            'type' => 'checkout.session.completed',
            'data' => ['object' => ['metadata' => ['pago_id' => (string) $this->pago->id]]],
        ]);

        $t = time();
        $sig = hash_hmac('sha256', "{$t}.{$payload}", 'whsec_test');

        $this->call('POST', '/api/payments/webhook/stripe', [], [], [], [
            'HTTP_Stripe-Signature' => "t={$t},v1={$sig}",
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertOk();

        $this->assertDatabaseHas('pagos', ['id' => $this->pago->id, 'estado' => 'PAGADO']);
    }

    public function test_webhook_firma_invalida_da_400(): void
    {
        config(['services.stripe.webhook_secret' => 'whsec_test']);

        $this->call('POST', '/api/payments/webhook/stripe', [], [], [], [
            'HTTP_Stripe-Signature' => 't=1,v1=malo',
            'CONTENT_TYPE' => 'application/json',
        ], '{"type":"checkout.session.completed"}')->assertStatus(400);
    }
}
