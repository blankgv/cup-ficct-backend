<?php

namespace Tests\Feature\Authentication;

use App\Modules\Authentication\Models\User;
use App\Modules\Authentication\Authorization\Role as RoleName;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthenticationSeeder::class);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        $admin->assignRole(RoleName::ADMINISTRADOR);

        return $admin;
    }

    public function test_admin_crea_usuario_con_rol_y_forzar_cambio_pass(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->postJson('/api/auth/users', [
                'email' => 'nuevo@test.com',
                'password' => 'secret123',
                'role' => RoleName::DOCENTE,
            ])
            ->assertCreated()
            ->assertJsonPath('data.must_change_password', true)
            ->assertJsonPath('data.role', RoleName::DOCENTE);

        $this->assertDatabaseHas('users', ['email' => 'nuevo@test.com']);
    }

    public function test_admin_lista_y_busca_usuarios(): void
    {
        User::factory()->create(['email' => 'buscable@test.com']);

        $this->actingAs($this->admin(), 'api')
            ->getJson('/api/auth/users?search=buscable')
            ->assertOk()
            ->assertJsonPath('data.0.email', 'buscable@test.com');
    }

    public function test_usuario_sin_permiso_no_accede_al_crud(): void
    {
        $docente = User::factory()->create(['must_change_password' => false]);
        $docente->assignRole(RoleName::DOCENTE);

        $this->actingAs($docente, 'api')
            ->getJson('/api/auth/users')
            ->assertForbidden();
    }
}
