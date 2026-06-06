<?php

namespace Tests\Feature\ApplicantAdmission;

use App\Modules\AcademicManagement\Models\Carrera;
use App\Modules\AcademicManagement\Models\Facultad;
use App\Modules\AcademicManagement\Models\Grupo;
use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\ApplicantAdmission\Models\Inscripcion;
use App\Modules\ApplicantAdmission\Models\Postulacion;
use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\Authentication\Authorization\Permission as Perm;
use App\Modules\Authentication\Authorization\Role as RoleName;
use App\Modules\Authentication\Models\Permission;
use App\Modules\Authentication\Models\Role;
use App\Modules\Authentication\Models\User;
use App\Modules\Payments\Models\Pago;
use Database\Seeders\AuthenticationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsignacionGrupoTest extends TestCase
{
    use RefreshDatabase;

    private int $convId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthenticationSeeder::class);

        Facultad::create(['codigo' => '187', 'nombre' => 'FICCT', 'abreviatura' => 'FICCT']);
        Carrera::create(['codigo' => '187-09', 'nombre' => 'Sis', 'facultad_codigo' => '187']);
        Carrera::create(['codigo' => '187-10', 'nombre' => 'Inf', 'facultad_codigo' => '187']);
        $conv = Convocatoria::create(['nombre' => 'CUP', 'gestion' => '2026', 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-02-01', 'estado' => 'ABIERTA']);
        $this->convId = $conv->id;
    }

    private function admin(): User
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole(RoleName::ADMINISTRADOR);

        return $u;
    }

    // Usuario con un rol que solo tiene applicant.assign (no applicant.manage).
    private function soloAsignar(): User
    {
        $role = Role::create(['name' => 'asignador', 'description' => 'Solo asignación de grupos']);
        $role->permissions()->sync(Permission::where('name', Perm::APPLICANT_ASSIGN)->pluck('id'));

        $u = User::factory()->create(['must_change_password' => false]);
        $u->role()->associate($role)->save();

        return $u;
    }

    // Crea postulante + postulación + pago para un escenario.
    private function postulante(string $doc, string $turno, string $fechaPago, string $estadoPost = 'VERIFICADO', string $estadoPago = 'PAGADO'): void
    {
        Postulante::create(['documento' => $doc, 'nombres' => 'N', 'apellidos' => 'A', 'email' => "$doc@e.com", 'fecha_nacimiento' => '2007-01-01', 'colegio' => 'X', 'ciudad' => 'SC']);
        Postulacion::create([
            'postulante_documento' => $doc, 'convocatoria_id' => $this->convId,
            'carrera_primera_codigo' => '187-09', 'carrera_segunda_codigo' => '187-10',
            'estado' => $estadoPost, 'turno_preferencia' => $turno,
        ]);
        Pago::create([
            'postulante_documento' => $doc, 'convocatoria_id' => $this->convId, 'monto' => 350.00,
            'concepto' => 'Inscripción', 'metodo' => 'QR', 'fecha_pago' => $fechaPago, 'estado' => $estadoPago,
        ]);
    }

    private function generar(): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->admin(), 'api')
            ->postJson("/api/applicant-admission/convocatorias/{$this->convId}/generar-grupos");
    }

    public function test_genera_grupos_y_asigna_por_preferencia(): void
    {
        $this->postulante('1', 'MANANA', '2026-01-10 08:00');
        $this->postulante('2', 'MANANA', '2026-01-11 08:00');
        $this->postulante('3', 'TARDE', '2026-01-12 08:00');

        $this->generar()
            ->assertOk()
            ->assertJson(['grupos_creados' => 2, 'inscritos' => 3, 'manana' => 2, 'tarde' => 1]);

        $this->assertSame(2, Grupo::where('convocatoria_id', $this->convId)->count());
        $this->assertSame(3, Inscripcion::where('convocatoria_id', $this->convId)->count());
    }

    public function test_solo_asigna_verificado_y_pagado(): void
    {
        $this->postulante('1', 'MANANA', '2026-01-10 08:00');                          // elegible
        $this->postulante('2', 'MANANA', '2026-01-10 08:00', 'VERIFICADO', 'PENDIENTE'); // no pagó
        $this->postulante('3', 'MANANA', '2026-01-10 08:00', 'PENDIENTE', 'PAGADO');     // no verificado

        $this->generar()->assertOk()->assertJson(['inscritos' => 1]);

        $this->assertDatabaseHas('inscripciones', ['postulante_documento' => '1']);
        $this->assertDatabaseMissing('inscripciones', ['postulante_documento' => '2']);
        $this->assertDatabaseMissing('inscripciones', ['postulante_documento' => '3']);
    }

    public function test_regenera_sin_duplicar(): void
    {
        $this->postulante('1', 'MANANA', '2026-01-10 08:00');
        $this->postulante('2', 'TARDE', '2026-01-11 08:00');

        $this->generar()->assertOk();
        $this->generar()->assertOk();

        $this->assertSame(2, Grupo::where('convocatoria_id', $this->convId)->count());
        $this->assertSame(2, Inscripcion::where('convocatoria_id', $this->convId)->count());
    }

    public function test_rebalsa_al_otro_turno_y_respeta_orden_de_pago(): void
    {
        // 71 prefieren mañana; capacidad mañana = 70 → el último en pagar rebalsa a tarde.
        $base = \Illuminate\Support\Carbon::parse('2026-01-10 08:00');
        for ($i = 1; $i <= 71; $i++) {
            $this->postulante((string) $i, 'MANANA', $base->copy()->addMinutes($i)->format('Y-m-d H:i:s'));
        }

        $this->generar()->assertOk()->assertJson(['grupos_creados' => 2, 'inscritos' => 71, 'manana' => 70, 'tarde' => 1]);

        // El grupo de tarde tiene exactamente 1: el de mayor fecha de pago (doc 71).
        $grupoTarde = Grupo::where('convocatoria_id', $this->convId)->where('turno', 'TARDE')->first();
        $this->assertSame(1, $grupoTarde->inscripciones()->count());
        $this->assertDatabaseHas('inscripciones', ['postulante_documento' => '71', 'grupo_id' => $grupoTarde->id]);
    }

    public function test_sin_permiso_no_genera(): void
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $u->assignRole(RoleName::DOCENTE);

        $this->actingAs($u, 'api')
            ->postJson("/api/applicant-admission/convocatorias/{$this->convId}/generar-grupos")
            ->assertForbidden();
    }

    public function test_con_permiso_assign_genera(): void
    {
        $this->postulante('1', 'MANANA', '2026-01-10 08:00');

        $this->actingAs($this->soloAsignar(), 'api')
            ->postJson("/api/applicant-admission/convocatorias/{$this->convId}/generar-grupos")
            ->assertOk()
            ->assertJson(['inscritos' => 1]);
    }

    public function test_fija_preferencia_de_turno(): void
    {
        $this->postulante('1', 'MANANA', '2026-01-10 08:00');

        $this->actingAs($this->admin(), 'api')
            ->putJson("/api/applicant-admission/postulantes/1/postulaciones/{$this->convId}/turno", ['turno' => 'TARDE'])
            ->assertOk()
            ->assertJsonPath('data.turno_preferencia', 'TARDE');

        $this->assertDatabaseHas('postulaciones', ['postulante_documento' => '1', 'turno_preferencia' => 'TARDE']);
    }

    public function test_turno_invalido_es_rechazado(): void
    {
        $this->postulante('1', 'MANANA', '2026-01-10 08:00');

        $this->actingAs($this->admin(), 'api')
            ->putJson("/api/applicant-admission/postulantes/1/postulaciones/{$this->convId}/turno", ['turno' => 'NOCHE'])
            ->assertStatus(422);
    }
}
