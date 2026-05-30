<?php

namespace Tests\Feature\Authentication;

use App\Modules\Authentication\Authorization\Permission as Perm;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\User;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->seed(AuthenticationSeeder::class);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        $admin->assignRole(RoleName::ADMINISTRADOR);

        return $admin;
    }

    public function test_admin_lista_roles_y_permisos(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->getJson('/api/auth/roles')
            ->assertOk()
            ->assertJsonCount(count(RoleName::all()), 'data');

        $this->actingAs($this->admin(), 'api')
            ->getJson('/api/auth/permissions')
            ->assertOk()
            ->assertJsonCount(count(Perm::all()), 'data');
    }

    public function test_admin_crea_rol_con_permisos(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->postJson('/api/auth/roles', [
                'name' => 'AUXILIAR',
                'permissions' => [Perm::APPLICANT_MANAGE, Perm::REPORT_VIEW],
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'AUXILIAR')
            ->assertJsonPath('data.permissions', [Perm::APPLICANT_MANAGE, Perm::REPORT_VIEW]);

        $this->assertDatabaseHas('roles', ['name' => 'AUXILIAR', 'guard_name' => 'api']);
    }

    public function test_admin_sincroniza_permisos_de_un_rol(): void
    {
        $create = $this->actingAs($this->admin(), 'api')
            ->postJson('/api/auth/roles', ['name' => 'AUXILIAR', 'permissions' => [Perm::REPORT_VIEW]])
            ->json('data.id');

        $this->actingAs($this->admin(), 'api')
            ->putJson("/api/auth/roles/{$create}/permissions", [
                'permissions' => [Perm::PAYMENT_MANAGE],
            ])
            ->assertOk()
            ->assertJsonPath('data.permissions', [Perm::PAYMENT_MANAGE]);
    }

    public function test_usuario_sin_role_manage_no_accede(): void
    {
        $docente = User::factory()->create(['must_change_password' => false]);
        $docente->assignRole(RoleName::DOCENTE);

        $this->actingAs($docente, 'api')
            ->getJson('/api/auth/roles')
            ->assertForbidden();
    }
}
