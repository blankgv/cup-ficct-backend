<?php

namespace Tests\Feature\ApplicantAdmission;

use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\User;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class BatchPostulanteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthenticationSeeder::class);
    }

    private function coordinador(): User
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole(RoleName::COORDINADOR);

        return $u;
    }

    private function csv(string $content): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('postulantes.csv', $content);
    }

    private const HEADER = "documento,nombres,apellidos,email,fecha_nacimiento,colegio,ciudad,telefono\n";

    public function test_carga_masiva_crea_postulantes_y_usuarios(): void
    {
        $csv = self::HEADER
            ."111,Juan,Perez,juan@e.com,2007-01-01,Colegio,Santa Cruz,700\n"
            ."222,Ana,Lopez,ana@e.com,2007-02-02,Colegio,Santa Cruz,\n";

        $this->actingAs($this->coordinador(), 'api')
            ->post('/api/applicant-admission/postulantes/lote', ['archivo' => $this->csv($csv)])
            ->assertOk()
            ->assertJsonPath('creados', 2)
            ->assertJsonPath('omitidos', 0);

        $this->assertDatabaseHas('postulantes', ['documento' => '111', 'email' => 'juan@e.com']);
        $this->assertDatabaseHas('users', ['email' => 'juan@e.com']);

        $p = Postulante::find('111');
        $this->assertNotNull($p->user_id);
        $this->assertTrue($p->user->hasRole(RoleName::POSTULANTE));
        // password = documento, debe cambiarla al primer ingreso
        $this->assertTrue($p->user->must_change_password);
    }

    public function test_omite_duplicados_y_reporta(): void
    {
        Postulante::create([
            'documento' => '111', 'nombres' => 'X', 'apellidos' => 'Y', 'email' => 'existe@e.com',
            'fecha_nacimiento' => '2007-01-01', 'colegio' => 'C', 'ciudad' => 'SC',
        ]);

        $csv = self::HEADER
            ."111,Juan,Perez,juan@e.com,2007-01-01,Colegio,Santa Cruz,700\n"  // documento duplicado
            ."333,Luis,Gomez,luis@e.com,2007-03-03,Colegio,Santa Cruz,\n";     // válido

        $this->actingAs($this->coordinador(), 'api')
            ->post('/api/applicant-admission/postulantes/lote', ['archivo' => $this->csv($csv)])
            ->assertOk()
            ->assertJsonPath('creados', 1)
            ->assertJsonPath('omitidos', 1)
            ->assertJsonPath('errores.0.fila', 2);
    }

    public function test_reporta_fila_invalida(): void
    {
        $csv = self::HEADER
            ."444,Pedro,Soto,correo-malo,2007-04-04,Colegio,Santa Cruz,\n";

        $this->actingAs($this->coordinador(), 'api')
            ->post('/api/applicant-admission/postulantes/lote', ['archivo' => $this->csv($csv)])
            ->assertOk()
            ->assertJsonPath('creados', 0)
            ->assertJsonPath('omitidos', 1);

        $this->assertDatabaseMissing('postulantes', ['documento' => '444']);
    }

    public function test_carga_masiva_por_excel(): void
    {
        $ss = new Spreadsheet();
        $hoja = $ss->getActiveSheet();
        $hoja->fromArray([
            ['documento', 'nombres', 'apellidos', 'email', 'fecha_nacimiento', 'colegio', 'ciudad', 'telefono'],
            ['555', 'Sofia', 'Vaca', 'sofia@e.com', '2007-05-05', 'Colegio', 'Santa Cruz', '777'],
        ]);
        $path = tempnam(sys_get_temp_dir(), 'post').'.xlsx';
        (new Xlsx($ss))->save($path);

        $archivo = new UploadedFile($path, 'postulantes.xlsx', null, null, true);

        $this->actingAs($this->coordinador(), 'api')
            ->post('/api/applicant-admission/postulantes/lote', ['archivo' => $archivo])
            ->assertOk()
            ->assertJsonPath('creados', 1);

        $this->assertDatabaseHas('postulantes', ['documento' => '555', 'email' => 'sofia@e.com']);
        @unlink($path);
    }

    public function test_sin_permiso_no_accede(): void
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole(RoleName::DOCENTE);

        $this->actingAs($u, 'api')
            ->post('/api/applicant-admission/postulantes/lote', ['archivo' => $this->csv(self::HEADER)])
            ->assertForbidden();
    }
}
