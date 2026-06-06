<?php

namespace Tests\Feature\Evaluation;

use App\Modules\AcademicManagement\Models\Carrera;
use App\Modules\AcademicManagement\Models\Facultad;
use App\Modules\AcademicManagement\Models\Feriado;
use App\Modules\AcademicManagement\Models\Grupo;
use App\Modules\AcademicManagement\Models\GrupoMateria;
use App\Modules\AcademicManagement\Models\Materia;
use App\Modules\AcademicManagement\Models\Periodo;
use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\ApplicantAdmission\Models\Inscripcion;
use App\Modules\ApplicantAdmission\Models\Postulacion;
use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\User;
use App\Modules\Evaluation\Models\Asistencia;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsistenciaTest extends TestCase
{
    use RefreshDatabase;

    private int $convId;

    private int $grupoId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthenticationSeeder::class);

        Facultad::create(['codigo' => '187', 'nombre' => 'FICCT', 'abreviatura' => 'FICCT']);
        Carrera::create(['codigo' => '187-09', 'nombre' => 'Sis', 'facultad_codigo' => '187']);
        Carrera::create(['codigo' => '187-10', 'nombre' => 'Inf', 'facultad_codigo' => '187']);
        Materia::create(['sigla' => 'MAT', 'nombre' => 'Matemáticas', 'peso' => 0.5]);
        Materia::create(['sigla' => 'FIS', 'nombre' => 'Física', 'peso' => 0.5]);
        Materia::create(['sigla' => 'ING', 'nombre' => 'Inglés', 'peso' => 0.5]);

        $conv = Convocatoria::create(['nombre' => 'CUP', 'gestion' => '2026', 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-02-01', 'estado' => 'ABIERTA']);
        $this->convId = $conv->id;
        $grupo = Grupo::create(['codigo' => 'C1-M1', 'turno' => 'MANANA', 'capacidad' => 70, 'gestion' => '2026', 'convocatoria_id' => $conv->id]);
        $this->grupoId = $grupo->id;
        GrupoMateria::create(['grupo_id' => $grupo->id, 'materia_sigla' => 'MAT']);
        GrupoMateria::create(['grupo_id' => $grupo->id, 'materia_sigla' => 'FIS']);

        $this->inscribir('1');
    }

    private function inscribir(string $doc): void
    {
        Postulante::create(['documento' => $doc, 'nombres' => 'N', 'apellidos' => 'A', 'email' => "$doc@e.com", 'fecha_nacimiento' => '2007-01-01', 'colegio' => 'X', 'ciudad' => 'SC']);
        Postulacion::create([
            'postulante_documento' => $doc, 'convocatoria_id' => $this->convId,
            'carrera_primera_codigo' => '187-09', 'carrera_segunda_codigo' => '187-10', 'estado' => 'VERIFICADO',
        ]);
        Inscripcion::create([
            'postulante_documento' => $doc, 'convocatoria_id' => $this->convId,
            'grupo_id' => $this->grupoId, 'fecha_asignacion' => '2026-02-02 09:00',
        ]);
    }

    private function evaluador(): User
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole(RoleName::ADMINISTRADOR);

        return $u;
    }

    private function marcar(string $materia, string $fecha, string $estado): void
    {
        Asistencia::create([
            'postulante_documento' => '1', 'convocatoria_id' => $this->convId,
            'materia_sigla' => $materia, 'fecha' => $fecha, 'estado' => $estado,
        ]);
    }

    public function test_registra_asistencia_individual(): void
    {
        $this->actingAs($this->evaluador(), 'api')
            ->postJson('/api/evaluation/asistencias', [
                'postulante_documento' => '1', 'convocatoria_id' => $this->convId,
                'materia_sigla' => 'MAT', 'fecha' => '2026-03-01', 'estado' => 'PRESENTE',
            ])
            ->assertOk()
            ->assertJsonPath('data.estado', 'PRESENTE');

        $this->assertDatabaseHas('asistencias', ['postulante_documento' => '1', 'materia_sigla' => 'MAT', 'fecha' => '2026-03-01', 'estado' => 'PRESENTE']);
    }

    public function test_upsert_reemplaza_misma_fecha(): void
    {
        $eval = $this->evaluador();
        $payload = ['postulante_documento' => '1', 'convocatoria_id' => $this->convId, 'materia_sigla' => 'MAT', 'fecha' => '2026-03-01', 'estado' => 'AUSENTE'];

        $this->actingAs($eval, 'api')->postJson('/api/evaluation/asistencias', $payload)->assertOk();
        $this->actingAs($eval, 'api')->postJson('/api/evaluation/asistencias', array_merge($payload, ['estado' => 'PRESENTE']))->assertOk();

        $this->assertSame(1, Asistencia::where('postulante_documento', '1')->where('materia_sigla', 'MAT')->count());
        $this->assertDatabaseHas('asistencias', ['postulante_documento' => '1', 'materia_sigla' => 'MAT', 'fecha' => '2026-03-01', 'estado' => 'PRESENTE']);
    }

    public function test_rechaza_si_no_inscrito(): void
    {
        Postulante::create(['documento' => '99', 'nombres' => 'N', 'apellidos' => 'A', 'email' => '99@e.com', 'fecha_nacimiento' => '2007-01-01', 'colegio' => 'X', 'ciudad' => 'SC']);

        $this->actingAs($this->evaluador(), 'api')
            ->postJson('/api/evaluation/asistencias', [
                'postulante_documento' => '99', 'convocatoria_id' => $this->convId,
                'materia_sigla' => 'MAT', 'fecha' => '2026-03-01', 'estado' => 'PRESENTE',
            ])
            ->assertStatus(422);
    }

    public function test_rechaza_materia_ajena_al_grupo(): void
    {
        $this->actingAs($this->evaluador(), 'api')
            ->postJson('/api/evaluation/asistencias', [
                'postulante_documento' => '1', 'convocatoria_id' => $this->convId,
                'materia_sigla' => 'ING', 'fecha' => '2026-03-01', 'estado' => 'PRESENTE',
            ])
            ->assertStatus(422);
    }

    public function test_estado_invalido_es_rechazado(): void
    {
        $this->actingAs($this->evaluador(), 'api')
            ->postJson('/api/evaluation/asistencias', [
                'postulante_documento' => '1', 'convocatoria_id' => $this->convId,
                'materia_sigla' => 'MAT', 'fecha' => '2026-03-01', 'estado' => 'TARDE',
            ])
            ->assertStatus(422);
    }

    public function test_carga_masiva_por_grupo_materia(): void
    {
        $this->inscribir('2');

        $this->actingAs($this->evaluador(), 'api')
            ->postJson("/api/evaluation/grupos/{$this->grupoId}/materias/MAT/asistencias", [
                'fecha' => '2026-03-01',
                'asistencias' => [
                    ['postulante_documento' => '1', 'estado' => 'PRESENTE'],
                    ['postulante_documento' => '2', 'estado' => 'AUSENTE'],
                    ['postulante_documento' => '99', 'estado' => 'PRESENTE'], // no inscrito
                ],
            ])
            ->assertOk()
            ->assertJson(['guardadas' => 2, 'omitidas' => 1]);

        $this->assertDatabaseHas('asistencias', ['postulante_documento' => '2', 'materia_sigla' => 'MAT', 'estado' => 'AUSENTE']);
        $this->assertDatabaseMissing('asistencias', ['postulante_documento' => '99']);
    }

    public function test_reporte_justificado_cuenta_y_habilitado(): void
    {
        // MAT: PRESENTE×3, JUSTIFICADO×1, AUSENTE×1 → 4/5 = 80% → HABILITADO
        $this->marcar('MAT', '2026-03-01', 'PRESENTE');
        $this->marcar('MAT', '2026-03-02', 'PRESENTE');
        $this->marcar('MAT', '2026-03-03', 'PRESENTE');
        $this->marcar('MAT', '2026-03-04', 'JUSTIFICADO');
        $this->marcar('MAT', '2026-03-05', 'AUSENTE');

        $this->actingAs($this->evaluador(), 'api')
            ->getJson("/api/evaluation/postulantes/1/convocatorias/{$this->convId}/asistencia")
            ->assertOk()
            ->assertJsonPath('porcentaje_global', 80)
            ->assertJsonPath('estado', 'HABILITADO');
    }

    public function test_reporte_inhabilitado_bajo_umbral(): void
    {
        // MAT: PRESENTE×3, AUSENTE×2 → 60% → INHABILITADO
        $this->marcar('MAT', '2026-03-01', 'PRESENTE');
        $this->marcar('MAT', '2026-03-02', 'PRESENTE');
        $this->marcar('MAT', '2026-03-03', 'PRESENTE');
        $this->marcar('MAT', '2026-03-04', 'AUSENTE');
        $this->marcar('MAT', '2026-03-05', 'AUSENTE');

        $this->actingAs($this->evaluador(), 'api')
            ->getJson("/api/evaluation/postulantes/1/convocatorias/{$this->convId}/asistencia")
            ->assertOk()
            ->assertJsonPath('porcentaje_global', 60)
            ->assertJsonPath('estado', 'INHABILITADO');
    }

    public function test_reporte_usa_calendario_lv_menos_feriados(): void
    {
        // Periodo 02–06 mar 2026 (Lun–Vie = 5 días). Feriado mié 04 → 4 sesiones esperadas.
        $periodo = Periodo::create([
            'codigo' => '1/2026', 'gestion' => '2026',
            'fecha_inicio_clases' => '2026-03-02', 'fecha_fin_clases' => '2026-03-06',
        ]);
        Feriado::create(['fecha' => '2026-03-04', 'descripcion' => 'Feriado', 'gestion' => '2026']);
        Grupo::where('id', $this->grupoId)->update(['periodo_id' => $periodo->id]);

        // MAT: 2 presentes sobre 4 esperadas. FIS: 0. Global = 2/8 = 25% → INHABILITADO.
        $this->marcar('MAT', '2026-03-02', 'PRESENTE');
        $this->marcar('MAT', '2026-03-03', 'PRESENTE');

        $this->actingAs($this->evaluador(), 'api')
            ->getJson("/api/evaluation/postulantes/1/convocatorias/{$this->convId}/asistencia")
            ->assertOk()
            ->assertJsonPath('base_calculo', 'calendario')
            ->assertJsonPath('sesiones_esperadas', 4)
            ->assertJsonPath('porcentaje_global', 25)
            ->assertJsonPath('estado', 'INHABILITADO');
    }

    public function test_sin_permiso_no_registra(): void
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole(RoleName::POSTULANTE);

        $this->actingAs($u, 'api')
            ->postJson('/api/evaluation/asistencias', [
                'postulante_documento' => '1', 'convocatoria_id' => $this->convId,
                'materia_sigla' => 'MAT', 'fecha' => '2026-03-01', 'estado' => 'PRESENTE',
            ])
            ->assertForbidden();
    }
}
