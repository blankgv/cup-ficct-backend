<?php

namespace Tests\Feature\Reports;

use App\Modules\AcademicManagement\Models\Carrera;
use App\Modules\AcademicManagement\Models\Facultad;
use App\Modules\AcademicManagement\Models\Grupo;
use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\ApplicantAdmission\Models\Inscripcion;
use App\Modules\ApplicantAdmission\Models\Postulacion;
use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\User;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VozReportTest extends TestCase
{
    use RefreshDatabase;

    private Convocatoria $conv;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthenticationSeeder::class);

        config([
            'services.openai.api_key' => 'sk-test',
            'services.openai.model' => 'gpt-test',
            'services.openai.base_url' => 'https://api.openai.com',
        ]);

        Facultad::create(['codigo' => '187', 'nombre' => 'FICCT', 'abreviatura' => 'FICCT']);
        Carrera::create(['codigo' => '187-09', 'nombre' => 'Sistemas', 'facultad_codigo' => '187']);
        Carrera::create(['codigo' => '187-10', 'nombre' => 'Informatica', 'facultad_codigo' => '187']);

        $this->conv = Convocatoria::create(['nombre' => 'CUP 2026', 'gestion' => '2026', 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-02-01', 'estado' => 'ABIERTA']);
        $grupo = Grupo::create(['codigo' => 'C1-M1', 'turno' => 'MANANA', 'capacidad' => 70, 'gestion' => '2026', 'convocatoria_id' => $this->conv->id]);

        Postulante::create(['documento' => '1', 'nombres' => 'Ana', 'apellidos' => 'Lopez', 'email' => '1@e.com', 'fecha_nacimiento' => '2007-01-01', 'colegio' => 'X', 'ciudad' => 'SC']);
        Postulacion::create(['postulante_documento' => '1', 'convocatoria_id' => $this->conv->id, 'carrera_primera_codigo' => '187-09', 'carrera_segunda_codigo' => '187-10', 'estado' => 'VERIFICADO', 'turno_preferencia' => 'MANANA']);
        Inscripcion::create(['postulante_documento' => '1', 'convocatoria_id' => $this->conv->id, 'grupo_id' => $grupo->id, 'fecha_asignacion' => '2026-02-02 09:00']);
    }

    private function admin(): User
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole(RoleName::ADMINISTRADOR);

        return $u;
    }

    private function fakeOpenAi(?array $argumentos): void
    {
        $message = $argumentos === null
            ? ['role' => 'assistant', 'content' => 'no entiendo']
            : ['role' => 'assistant', 'tool_calls' => [[
                'id' => 'call_1', 'type' => 'function',
                'function' => ['name' => 'generar_reporte', 'arguments' => json_encode($argumentos)],
            ]]];

        Http::fake(['*/v1/chat/completions' => Http::response(['choices' => [['message' => $message]]])]);
    }

    public function test_voz_genera_estudiantes_por_grupo(): void
    {
        $this->fakeOpenAi(['reporte' => 'estudiantes_por_grupo', 'filtros' => ['gestion' => '2026', 'turno' => 'MANANA']]);

        $this->actingAs($this->admin(), 'api')
            ->postJson('/api/reports/voz', ['texto' => 'estudiantes del turno mañana gestión 2026'])
            ->assertOk()
            ->assertJsonPath('interpretacion.reporte', 'estudiantes_por_grupo')
            ->assertJsonCount(1, 'rows');
    }

    public function test_voz_no_configurado_da_422(): void
    {
        config(['services.openai.api_key' => '', 'services.openai.model' => '', 'services.openai.base_url' => '']);

        $this->actingAs($this->admin(), 'api')
            ->postJson('/api/reports/voz', ['texto' => 'algo'])
            ->assertStatus(422);
    }

    public function test_voz_sin_interpretacion_da_422(): void
    {
        $this->fakeOpenAi(null);

        $this->actingAs($this->admin(), 'api')
            ->postJson('/api/reports/voz', ['texto' => 'bla bla'])
            ->assertStatus(422);
    }

    public function test_voz_convocatoria_no_identificada_da_422(): void
    {
        $this->fakeOpenAi(['reporte' => 'postulantes', 'filtros' => []]);

        $this->actingAs($this->admin(), 'api')
            ->postJson('/api/reports/voz', ['texto' => 'postulantes'])
            ->assertStatus(422);
    }

    public function test_voz_resuelve_convocatoria_por_gestion(): void
    {
        $this->fakeOpenAi(['reporte' => 'postulantes', 'filtros' => ['convocatoria' => '2026']]);

        $this->actingAs($this->admin(), 'api')
            ->postJson('/api/reports/voz', ['texto' => 'postulantes de la convocatoria 2026'])
            ->assertOk()
            ->assertJsonPath('interpretacion.reporte', 'postulantes')
            ->assertJsonCount(1, 'rows');
    }

    public function test_voz_export_excel(): void
    {
        $this->fakeOpenAi(['reporte' => 'estudiantes_por_grupo', 'filtros' => ['gestion' => '2026']]);

        $this->actingAs($this->admin(), 'api')
            ->post('/api/reports/voz', ['texto' => 'estudiantes 2026', 'format' => 'excel'])
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
