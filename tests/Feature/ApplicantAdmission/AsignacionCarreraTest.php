<?php

namespace Tests\Feature\ApplicantAdmission;

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
use App\Modules\Evaluation\Models\Asistencia;
use App\Modules\Evaluation\Models\Nota;
use App\Modules\Payments\Models\Pago;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsignacionCarreraTest extends TestCase
{
    use RefreshDatabase;

    private Convocatoria $conv;

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

        $this->conv = Convocatoria::create(['nombre' => 'CUP', 'gestion' => '2026', 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-02-01', 'estado' => 'ABIERTA']);
        $grupo = Grupo::create(['codigo' => 'C1-M1', 'turno' => 'MANANA', 'capacidad' => 70, 'gestion' => '2026', 'convocatoria_id' => $this->conv->id]);
        $this->grupoId = $grupo->id;
        GrupoMateria::create(['grupo_id' => $grupo->id, 'materia_sigla' => 'MAT']);
        GrupoMateria::create(['grupo_id' => $grupo->id, 'materia_sigla' => 'FIS']);
    }

    private function cupos(int $a, int $b): void
    {
        $this->conv->carreras()->sync([
            '187-09' => ['cupos' => $a],
            '187-10' => ['cupos' => $b],
        ]);
    }

    // Crea un inscrito con nota y habilitación dadas.
    private function estudiante(string $doc, string $primera, string $segunda, float $nota, string $fechaPago, bool $habilitado = true): void
    {
        Postulante::create(['documento' => $doc, 'nombres' => 'N', 'apellidos' => 'A', 'email' => "$doc@e.com", 'fecha_nacimiento' => '2007-01-01', 'colegio' => 'X', 'ciudad' => 'SC']);
        Postulacion::create([
            'postulante_documento' => $doc, 'convocatoria_id' => $this->conv->id,
            'carrera_primera_codigo' => $primera, 'carrera_segunda_codigo' => $segunda, 'estado' => 'VERIFICADO',
        ]);
        Pago::create([
            'postulante_documento' => $doc, 'convocatoria_id' => $this->conv->id, 'monto' => 350.00,
            'concepto' => 'Inscripción', 'metodo' => 'QR', 'fecha_pago' => $fechaPago, 'estado' => 'PAGADO',
        ]);
        Inscripcion::create([
            'postulante_documento' => $doc, 'convocatoria_id' => $this->conv->id,
            'grupo_id' => $this->grupoId, 'fecha_asignacion' => '2026-02-02 09:00',
        ]);
        // Promedio final = nota (pesos 0.5 + 0.5, un examen por materia).
        Nota::create(['postulante_documento' => $doc, 'convocatoria_id' => $this->conv->id, 'materia_sigla' => 'MAT', 'numero' => 1, 'valor' => $nota]);
        Nota::create(['postulante_documento' => $doc, 'convocatoria_id' => $this->conv->id, 'materia_sigla' => 'FIS', 'numero' => 1, 'valor' => $nota]);
        // Habilitación data-driven: 1 registro PRESENTE = 100%, AUSENTE = 0%.
        Asistencia::create(['postulante_documento' => $doc, 'convocatoria_id' => $this->conv->id, 'materia_sigla' => 'MAT', 'fecha' => '2026-03-02', 'estado' => $habilitado ? 'PRESENTE' : 'AUSENTE']);
    }

    private function generar(): \Illuminate\Testing\TestResponse
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        $admin->assignRole(RoleName::ADMINISTRADOR);

        return $this->actingAs($admin, 'api')
            ->postJson("/api/applicant-admission/convocatorias/{$this->conv->id}/asignar-carreras");
    }

    public function test_asigna_por_nota_y_rebalsa_a_segunda_opcion(): void
    {
        $this->cupos(1, 1);
        $this->estudiante('1', '187-09', '187-10', 90, '2026-01-10 08:00');
        $this->estudiante('2', '187-09', '187-10', 80, '2026-01-11 08:00');
        $this->estudiante('3', '187-09', '187-10', 70, '2026-01-12 08:00');

        $this->generar()
            ->assertOk()
            ->assertJson(['elegibles' => 3, 'asignados' => 2, 'sin_cupo' => 1]);

        $this->assertDatabaseHas('inscripciones', ['postulante_documento' => '1', 'carrera_asignada_codigo' => '187-09']); // mejor nota → 1ra
        $this->assertDatabaseHas('inscripciones', ['postulante_documento' => '2', 'carrera_asignada_codigo' => '187-10']); // 1ra llena → 2da
        $this->assertDatabaseHas('inscripciones', ['postulante_documento' => '3', 'carrera_asignada_codigo' => null]);     // todo lleno
    }

    public function test_excluye_reprobado_e_inhabilitado(): void
    {
        $this->cupos(5, 5);
        $this->estudiante('1', '187-09', '187-10', 50, '2026-01-10 08:00');                 // reprobado
        $this->estudiante('2', '187-09', '187-10', 70, '2026-01-10 08:00', habilitado: false); // inhabilitado
        $this->estudiante('3', '187-09', '187-10', 70, '2026-01-10 08:00');                 // elegible

        $this->generar()->assertOk()->assertJson(['elegibles' => 1, 'asignados' => 1]);

        $this->assertDatabaseHas('inscripciones', ['postulante_documento' => '3', 'carrera_asignada_codigo' => '187-09']);
        $this->assertDatabaseHas('inscripciones', ['postulante_documento' => '1', 'carrera_asignada_codigo' => null]);
        $this->assertDatabaseHas('inscripciones', ['postulante_documento' => '2', 'carrera_asignada_codigo' => null]);
    }

    public function test_desempate_por_quien_pago_antes(): void
    {
        $this->cupos(1, 0);
        $this->estudiante('1', '187-09', '187-10', 80, '2026-01-15 08:00'); // pagó después
        $this->estudiante('2', '187-09', '187-10', 80, '2026-01-10 08:00'); // pagó antes → gana

        $this->generar()->assertOk()->assertJson(['asignados' => 1, 'sin_cupo' => 1]);

        $this->assertDatabaseHas('inscripciones', ['postulante_documento' => '2', 'carrera_asignada_codigo' => '187-09']);
        $this->assertDatabaseHas('inscripciones', ['postulante_documento' => '1', 'carrera_asignada_codigo' => null]);
    }

    public function test_regenera_sin_duplicar(): void
    {
        $this->cupos(1, 1);
        $this->estudiante('1', '187-09', '187-10', 90, '2026-01-10 08:00');

        $this->generar()->assertOk();
        $this->generar()->assertOk()->assertJson(['asignados' => 1]);

        $this->assertDatabaseHas('inscripciones', ['postulante_documento' => '1', 'carrera_asignada_codigo' => '187-09']);
    }

    public function test_sin_permiso_no_asigna(): void
    {
        $this->cupos(1, 1);
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole(RoleName::DOCENTE);

        $this->actingAs($u, 'api')
            ->postJson("/api/applicant-admission/convocatorias/{$this->conv->id}/asignar-carreras")
            ->assertForbidden();
    }
}
