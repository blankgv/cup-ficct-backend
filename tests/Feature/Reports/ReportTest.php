<?php

namespace Tests\Feature\Reports;

use App\Modules\AcademicManagement\Models\Carrera;
use App\Modules\AcademicManagement\Models\Facultad;
use App\Modules\AcademicManagement\Models\Grupo;
use App\Modules\AcademicManagement\Models\GrupoMateria;
use App\Modules\AcademicManagement\Models\Materia;
use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\ApplicantAdmission\Models\Inscripcion;
use App\Modules\ApplicantAdmission\Models\Postulacion;
use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\User;
use App\Modules\Evaluation\Models\Nota;
use App\Modules\Payments\Models\Pago;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private Convocatoria $conv;

    private int $grupoId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthenticationSeeder::class);

        Facultad::create(['codigo' => '187', 'nombre' => 'FICCT', 'abreviatura' => 'FICCT']);
        Carrera::create(['codigo' => '187-09', 'nombre' => 'Sistemas', 'facultad_codigo' => '187']);
        Carrera::create(['codigo' => '187-10', 'nombre' => 'Informatica', 'facultad_codigo' => '187']);
        Materia::create(['sigla' => 'MAT', 'nombre' => 'Matemáticas', 'peso' => 0.5]);
        Materia::create(['sigla' => 'FIS', 'nombre' => 'Física', 'peso' => 0.5]);

        $this->conv = Convocatoria::create(['nombre' => 'CUP 2026', 'gestion' => '2026', 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-02-01', 'estado' => 'ABIERTA']);
        $this->conv->carreras()->sync(['187-09' => ['cupos' => 5], '187-10' => ['cupos' => 5]]);

        $grupo = Grupo::create(['codigo' => 'C1-M1', 'turno' => 'MANANA', 'capacidad' => 70, 'gestion' => '2026', 'convocatoria_id' => $this->conv->id]);
        $this->grupoId = $grupo->id;
        GrupoMateria::create(['grupo_id' => $grupo->id, 'materia_sigla' => 'MAT']);
        GrupoMateria::create(['grupo_id' => $grupo->id, 'materia_sigla' => 'FIS']);

        $this->estudiante('1', 'Ana', 'Lopez', 90, '187-09');
        $this->estudiante('2', 'Beto', 'Gomez', 40, null);
    }

    private function estudiante(string $doc, string $nombres, string $apellidos, float $nota, ?string $carreraAsignada): void
    {
        Postulante::create(['documento' => $doc, 'nombres' => $nombres, 'apellidos' => $apellidos, 'email' => "$doc@e.com", 'fecha_nacimiento' => '2007-01-01', 'colegio' => 'X', 'ciudad' => 'SC']);
        Postulacion::create([
            'postulante_documento' => $doc, 'convocatoria_id' => $this->conv->id,
            'carrera_primera_codigo' => '187-09', 'carrera_segunda_codigo' => '187-10', 'estado' => 'VERIFICADO', 'turno_preferencia' => 'MANANA',
        ]);
        Pago::create([
            'postulante_documento' => $doc, 'convocatoria_id' => $this->conv->id, 'monto' => 350.00,
            'concepto' => 'Inscripción', 'metodo' => 'QR', 'fecha_pago' => '2026-01-10 08:00', 'estado' => 'PAGADO',
        ]);
        Inscripcion::create([
            'postulante_documento' => $doc, 'convocatoria_id' => $this->conv->id,
            'grupo_id' => $this->grupoId, 'fecha_asignacion' => '2026-02-02 09:00', 'carrera_asignada_codigo' => $carreraAsignada,
        ]);
        Nota::create(['postulante_documento' => $doc, 'convocatoria_id' => $this->conv->id, 'materia_sigla' => 'MAT', 'numero' => 1, 'valor' => $nota]);
        Nota::create(['postulante_documento' => $doc, 'convocatoria_id' => $this->conv->id, 'materia_sigla' => 'FIS', 'numero' => 1, 'valor' => $nota]);
    }

    private function admin(): User
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole(RoleName::ADMINISTRADOR);

        return $u;
    }

    private function coordinador(): User
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole(RoleName::COORDINADOR);

        return $u;
    }

    public function test_estudiantes_por_grupo_json_filtra_nombre(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->getJson('/api/reports/estudiantes-por-grupo?gestion=2026&nombre=Ana')
            ->assertOk()
            ->assertJsonCount(1, 'rows')
            ->assertJsonPath('rows.0.2', '1');
    }

    public function test_estudiantes_por_grupo_gestion_requerida(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->getJson('/api/reports/estudiantes-por-grupo')
            ->assertStatus(422);
    }

    public function test_postulantes_json(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->getJson("/api/reports/convocatorias/{$this->conv->id}/postulantes")
            ->assertOk()
            ->assertJsonCount(2, 'rows');
    }

    public function test_recaudacion_json(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->getJson("/api/reports/convocatorias/{$this->conv->id}/recaudacion")
            ->assertOk()
            ->assertJsonPath('rows.0.0', 'PAGADO');
    }

    public function test_resultados_json(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->getJson("/api/reports/convocatorias/{$this->conv->id}/resultados")
            ->assertOk()
            ->assertJsonCount(2, 'rows');
    }

    public function test_asignacion_carreras_json(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->getJson("/api/reports/convocatorias/{$this->conv->id}/asignacion-carreras")
            ->assertOk()
            ->assertJsonCount(2, 'rows');
    }

    public function test_export_excel(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->get("/api/reports/convocatorias/{$this->conv->id}/postulantes?format=excel")
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_export_pdf(): void
    {
        $this->actingAs($this->admin(), 'api')
            ->get("/api/reports/convocatorias/{$this->conv->id}/postulantes?format=pdf")
            ->assertOk();
    }

    public function test_export_sin_permiso_export_403(): void
    {
        // Coordinador tiene report.view pero NO report.export.
        $this->actingAs($this->coordinador(), 'api')
            ->get("/api/reports/convocatorias/{$this->conv->id}/postulantes?format=excel")
            ->assertForbidden();

        // Pero sí puede ver el JSON.
        $this->actingAs($this->coordinador(), 'api')
            ->getJson("/api/reports/convocatorias/{$this->conv->id}/postulantes")
            ->assertOk();
    }

    public function test_sin_report_view_403(): void
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole(RoleName::DOCENTE);

        $this->actingAs($u, 'api')
            ->getJson("/api/reports/convocatorias/{$this->conv->id}/postulantes")
            ->assertForbidden();
    }
}
