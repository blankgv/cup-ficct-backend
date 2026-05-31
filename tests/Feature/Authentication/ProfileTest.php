<?php

namespace Tests\Feature\Authentication;

use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\User;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthenticationSeeder::class);
    }

    private function user(): User
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole(RoleName::DOCENTE);

        return $u;
    }

    public function test_actualiza_su_username(): void
    {
        $this->actingAs($this->user(), 'api')
            ->putJson('/api/auth/me/profile', ['username' => 'jperez'])
            ->assertOk()
            ->assertJsonPath('data.username', 'jperez');
    }

    public function test_sube_su_foto_a_r2(): void
    {
        Storage::fake('r2');
        $user = $this->user();

        $this->actingAs($user, 'api')
            ->postJson('/api/auth/me/foto', ['foto' => UploadedFile::fake()->create('yo.png', 100, 'image/png')])
            ->assertOk()
            ->assertJsonPath('data.foto_perfil_path', "fotos_perfil/{$user->id}.png");

        Storage::disk('r2')->assertExists("fotos_perfil/{$user->id}.png");
    }

    public function test_foto_rechaza_no_imagen(): void
    {
        Storage::fake('r2');

        $this->actingAs($this->user(), 'api')
            ->postJson('/api/auth/me/foto', ['foto' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf')])
            ->assertStatus(422);
    }

    public function test_descarga_sin_foto_da_404(): void
    {
        $this->actingAs($this->user(), 'api')
            ->getJson('/api/auth/me/foto')
            ->assertNotFound();
    }

    public function test_perfil_requiere_autenticacion(): void
    {
        $this->putJson('/api/auth/me/profile', ['username' => 'x'])->assertStatus(401);
    }
}
