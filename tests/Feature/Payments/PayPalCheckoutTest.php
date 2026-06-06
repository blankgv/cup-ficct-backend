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
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayPalCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private Pago $pago;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthenticationSeeder::class);

        config([
            'services.paypal.base_url' => 'https://api-m.sandbox.paypal.com',
            'services.paypal.client_id' => 'cid',
            'services.paypal.secret' => 'secret',
            'services.paypal.currency' => 'USD',
            'services.paypal.webhook_id' => 'WH-1',
            'services.paypal.success_url' => 'https://front.test/pagos/exito',
            'services.paypal.cancel_url' => 'https://front.test/pagos/cancelado',
        ]);

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

    public function test_checkout_paypal_devuelve_url_de_aprobacion(): void
    {
        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'tok']),
            '*/v2/checkout/orders' => Http::response([
                'id' => 'ORD-123',
                'links' => [
                    ['rel' => 'self', 'href' => 'https://api/ORD-123'],
                    ['rel' => 'approve', 'href' => 'https://www.paypal.com/checkoutnow?token=ORD-123'],
                ],
            ]),
        ]);

        $this->actingAs($this->admin(), 'api')
            ->postJson("/api/payments/pagos/{$this->pago->id}/checkout?gateway=paypal")
            ->assertOk()
            ->assertJsonPath('url', 'https://www.paypal.com/checkoutnow?token=ORD-123');

        $this->assertDatabaseHas('pagos', ['id' => $this->pago->id, 'gateway' => 'paypal', 'referencia' => 'ORD-123']);
    }

    public function test_webhook_paypal_marca_pagado(): void
    {
        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'tok']),
            '*/v1/notifications/verify-webhook-signature' => Http::response(['verification_status' => 'SUCCESS']),
        ]);

        $payload = json_encode([
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => ['custom_id' => (string) $this->pago->id],
        ]);

        $this->call('POST', '/api/payments/webhook/paypal', [], [], [], [
            'HTTP_paypal-auth-algo' => 'SHA256withRSA',
            'HTTP_paypal-cert-url' => 'https://api/cert',
            'HTTP_paypal-transmission-id' => 'tid',
            'HTTP_paypal-transmission-sig' => 'sig',
            'HTTP_paypal-transmission-time' => '2026-01-15T10:30:00Z',
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertOk();

        $this->assertDatabaseHas('pagos', ['id' => $this->pago->id, 'estado' => 'PAGADO']);
    }

    public function test_webhook_paypal_firma_invalida_da_400(): void
    {
        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'tok']),
            '*/v1/notifications/verify-webhook-signature' => Http::response(['verification_status' => 'FAILURE']),
        ]);

        $payload = json_encode([
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => ['custom_id' => (string) $this->pago->id],
        ]);

        $this->call('POST', '/api/payments/webhook/paypal', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertStatus(400);

        $this->assertDatabaseHas('pagos', ['id' => $this->pago->id, 'estado' => 'PENDIENTE']);
    }
}
