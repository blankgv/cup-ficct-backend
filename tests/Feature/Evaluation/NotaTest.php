<?php

namespace Tests\Feature\Evaluation;

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
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotaTest extends TestCase
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

    public function test_carga_nota_individual(): void
    {
        $this->actingAs($this->evaluador(), 'api')
            ->postJson('/api/evaluation/notas', [
                'postulante_documento' => '1', 'convocatoria_id' => $this->convId,
                'materia_sigla' => 'MAT', 'numero' => 1, 'valor' => 80.5,
            ])
            ->assertOk()
            ->assertJsonPath('data.valor', 80.5);

        $this->assertDatabaseHas('notas', ['postulante_documento' => '1', 'materia_sigla' => 'MAT', 'numero' => 1, 'valor' => 80.50]);
    }

    public function test_upsert_reemplaza_nota_existente(): void
    {
        $eval = $this->evaluador();
        $payload = ['postulante_documento' => '1', 'convocatoria_id' => $this->convId, 'materia_sigla' => 'MAT', 'numero' => 1, 'valor' => 50];

        $this->actingAs($eval, 'api')->postJson('/api/evaluation/notas', $payload)->assertOk();
        $this->actingAs($eval, 'api')->postJson('/api/evaluation/notas', array_merge($payload, ['valor' => 90]))->assertOk();

        $this->assertSame(1, \App\Modules\Evaluation\Models\Nota::where('postulante_documento', '1')->where('materia_sigla', 'MAT')->where('numero', 1)->count());
        $this->assertDatabaseHas('notas', ['postulante_documento' => '1', 'materia_sigla' => 'MAT', 'numero' => 1, 'valor' => 90.00]);
    }

    public function test_rechaza_nota_si_no_inscrito(): void
    {
        Postulante::create(['documento' => '99', 'nombres' => 'N', 'apellidos' => 'A', 'email' => '99@e.com', 'fecha_nacimiento' => '2007-01-01', 'colegio' => 'X', 'ciudad' => 'SC']);

        $this->actingAs($this->evaluador(), 'api')
            ->postJson('/api/evaluation/notas', [
                'postulante_documento' => '99', 'convocatoria_id' => $this->convId,
                'materia_sigla' => 'MAT', 'numero' => 1, 'valor' => 80,
            ])
            ->assertStatus(422);
    }

    public function test_rechaza_materia_ajena_al_grupo(): void
    {
        $this->actingAs($this->evaluador(), 'api')
            ->postJson('/api/evaluation/notas', [
                'postulante_documento' => '1', 'convocatoria_id' => $this->convId,
                'materia_sigla' => 'ING', 'numero' => 1, 'valor' => 80,
            ])
            ->assertStatus(422);
    }

    public function test_carga_masiva_por_grupo_materia(): void
    {
        $this->inscribir('2');

        $this->actingAs($this->evaluador(), 'api')
            ->postJson("/api/evaluation/grupos/{$this->grupoId}/materias/MAT/notas", [
                'numero' => 1,
                'notas' => [
                    ['postulante_documento' => '1', 'valor' => 80],
                    ['postulante_documento' => '2', 'valor' => 90],
                    ['postulante_documento' => '99', 'valor' => 70], // no inscrito
                ],
            ])
            ->assertOk()
            ->assertJson(['guardadas' => 2, 'omitidas' => 1]);

        $this->assertDatabaseHas('notas', ['postulante_documento' => '2', 'materia_sigla' => 'MAT', 'numero' => 1, 'valor' => 90.00]);
        $this->assertDatabaseMissing('notas', ['postulante_documento' => '99']);
    }

    public function test_boletin_calcula_promedio_ponderado_y_aprobado(): void
    {
        $eval = $this->evaluador();
        // MAT: (80+90)/2 = 85 ; FIS: 70 ; final = 85*0.5 + 70*0.5 = 77.5 → APROBADO
        $this->cargar($eval, 'MAT', 1, 80);
        $this->cargar($eval, 'MAT', 2, 90);
        $this->cargar($eval, 'FIS', 1, 70);

        $this->actingAs($eval, 'api')
            ->getJson("/api/evaluation/postulantes/1/convocatorias/{$this->convId}/boletin")
            ->assertOk()
            ->assertJsonPath('promedio_final', 77.5)
            ->assertJsonPath('estado', 'APROBADO');
    }

    public function test_boletin_reprobado_cuando_bajo_60(): void
    {
        $eval = $this->evaluador();
        $this->cargar($eval, 'MAT', 1, 50);
        $this->cargar($eval, 'FIS', 1, 40);

        $this->actingAs($eval, 'api')
            ->getJson("/api/evaluation/postulantes/1/convocatorias/{$this->convId}/boletin")
            ->assertOk()
            ->assertJsonPath('promedio_final', 45)
            ->assertJsonPath('estado', 'REPROBADO');
    }

    public function test_sin_permiso_no_carga(): void
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole(RoleName::POSTULANTE);

        $this->actingAs($u, 'api')
            ->postJson('/api/evaluation/notas', [
                'postulante_documento' => '1', 'convocatoria_id' => $this->convId,
                'materia_sigla' => 'MAT', 'numero' => 1, 'valor' => 80,
            ])
            ->assertForbidden();
    }

    private function cargar(User $eval, string $materia, int $numero, float $valor): void
    {
        $this->actingAs($eval, 'api')->postJson('/api/evaluation/notas', [
            'postulante_documento' => '1', 'convocatoria_id' => $this->convId,
            'materia_sigla' => $materia, 'numero' => $numero, 'valor' => $valor,
        ])->assertOk();
    }
}
