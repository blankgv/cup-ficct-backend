<?php

namespace Tests\Feature\ApplicantAdmission;

use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\User;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TituloTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthenticationSeeder::class);
        Postulante::create([
            'documento' => '9876543', 'nombres' => 'M', 'apellidos' => 'Q',
            'email' => 'm@e.com', 'fecha_nacimiento' => '2007-01-01', 'colegio' => 'X', 'ciudad' => 'SC',
        ]);
    }

    private function coordinador(): User
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole(RoleName::COORDINADOR);

        return $u;
    }

    public function test_sube_titulo_a_r2(): void
    {
        Storage::fake('r2');

        $this->actingAs($this->coordinador(), 'api')
            ->postJson('/api/applicant-admission/postulantes/9876543/titulo', [
                'titulo' => UploadedFile::fake()->create('titulo.pdf', 100, 'application/pdf'),
            ])
            ->assertOk()
            ->assertJsonPath('data.titulo_bachiller_path', 'titulos_bachiller/9876543.pdf');

        Storage::disk('r2')->assertExists('titulos_bachiller/9876543.pdf');
    }

    public function test_reemplaza_titulo_existente(): void
    {
        Storage::fake('r2');
        $coord = $this->coordinador();

        $this->actingAs($coord, 'api')->postJson('/api/applicant-admission/postulantes/9876543/titulo', [
            'titulo' => UploadedFile::fake()->create('viejo.pdf', 50, 'application/pdf'),
        ])->assertOk();

        $this->actingAs($coord, 'api')->postJson('/api/applicant-admission/postulantes/9876543/titulo', [
            'titulo' => UploadedFile::fake()->create('nuevo.pdf', 60, 'application/pdf'),
        ])->assertOk();

        Storage::disk('r2')->assertExists('titulos_bachiller/9876543.pdf');
    }

    public function test_rechaza_archivo_no_permitido(): void
    {
        Storage::fake('r2');

        $this->actingAs($this->coordinador(), 'api')
            ->postJson('/api/applicant-admission/postulantes/9876543/titulo', [
                'titulo' => UploadedFile::fake()->create('virus.exe', 10),
            ])
            ->assertStatus(422);
    }

    public function test_descarga_sin_titulo_da_404(): void
    {
        $this->actingAs($this->coordinador(), 'api')
            ->getJson('/api/applicant-admission/postulantes/9876543/titulo')
            ->assertNotFound();
    }
}
