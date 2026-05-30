<?php

namespace Tests\Feature\Authentication;

use App\Modules\Authentication\Models\User;
use App\Modules\Authentication\Authorization\Role as RoleName;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->seed(AuthenticationSeeder::class);
    }

    public function test_login_devuelve_token_y_usuario(): void
    {
        $user = User::factory()->create([
            'email' => 'login@test.com',
            'password' => Hash::make('secret123'),
        ]);
        $user->assignRole(RoleName::DOCENTE);

        $this->postJson('/api/auth/login', [
            'email' => 'login@test.com',
            'password' => 'secret123',
        ])->assertOk()->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
            'user' => ['id', 'email', 'roles', 'permissions'],
        ]);
    }

    public function test_login_con_credenciales_invalidas_falla(): void
    {
        User::factory()->create([
            'email' => 'login@test.com',
            'password' => Hash::make('secret123'),
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'login@test.com',
            'password' => 'malisima',
        ])->assertStatus(401);
    }

    public function test_me_sin_token_devuelve_401(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
    }
}
