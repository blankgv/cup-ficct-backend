<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use App\Modules\Authentication\Authorization\Role as RoleName;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->seed(AuthenticationSeeder::class);
    }

    public function test_usuario_con_pass_pendiente_es_bloqueado_hasta_cambiarla(): void
    {
        $admin = User::factory()->create([
            'password' => Hash::make('vieja12345'),
            'must_change_password' => true,
        ]);
        $admin->assignRole(RoleName::ADMINISTRADOR);

        // Tiene user.manage pero debe cambiar contraseña -> 403.
        $this->actingAs($admin, 'api')
            ->getJson('/api/auth/users')
            ->assertForbidden();

        // Cambia la contraseña.
        $this->actingAs($admin, 'api')
            ->postJson('/api/auth/change-password', [
                'current_password' => 'vieja12345',
                'new_password' => 'nueva12345',
                'new_password_confirmation' => 'nueva12345',
            ])
            ->assertOk();

        $this->assertFalse($admin->fresh()->must_change_password);

        // Ya puede acceder.
        $this->actingAs($admin->fresh(), 'api')
            ->getJson('/api/auth/users')
            ->assertOk();
    }

    public function test_forgot_password_envia_notificacion(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'reset@test.com']);

        $this->postJson('/api/auth/forgot-password', ['email' => 'reset@test.com'])
            ->assertOk();

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_cambia_la_contrasena(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@test.com',
            'password' => Hash::make('vieja12345'),
        ]);

        $token = Password::broker()->createToken($user);

        $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => 'reset@test.com',
            'password' => 'nueva12345',
            'password_confirmation' => 'nueva12345',
        ])->assertOk();

        $this->postJson('/api/auth/login', [
            'email' => 'reset@test.com',
            'password' => 'nueva12345',
        ])->assertOk();
    }
}
