<?php

namespace Tests\Feature\ApplicantAdmission;

use App\Modules\AcademicManagement\Models\Carrera;
use App\Modules\AcademicManagement\Models\Facultad;
use App\Modules\AcademicManagement\Models\Grupo;
use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\ApplicantAdmission\Models\Inscripcion;
use App\Modules\ApplicantAdmission\Models\Postulacion;
use App\Modules\ApplicantAdmission\Models\Postulante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InscripcionTest extends TestCase
{
    use RefreshDatabase;

    private int $convId;

    private int $grupoId;

    protected function setUp(): void
    {
        parent::setUp();

        Facultad::create(['codigo' => '187', 'nombre' => 'FICCT', 'abreviatura' => 'FICCT']);
        Carrera::create(['codigo' => '187-09', 'nombre' => 'Sis', 'facultad_codigo' => '187']);
        Carrera::create(['codigo' => '187-10', 'nombre' => 'Inf', 'facultad_codigo' => '187']);
        Postulante::create(['documento' => '9876543', 'nombres' => 'M', 'apellidos' => 'Q', 'email' => 'm@e.com', 'fecha_nacimiento' => '2007-01-01', 'colegio' => 'X', 'ciudad' => 'SC']);
        $conv = Convocatoria::create(['nombre' => 'CUP', 'gestion' => '2026', 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-02-01', 'estado' => 'ABIERTA']);
        $this->convId = $conv->id;
        Postulacion::create([
            'postulante_documento' => '9876543', 'convocatoria_id' => $conv->id,
            'carrera_primera_codigo' => '187-09', 'carrera_segunda_codigo' => '187-10', 'estado' => 'VERIFICADO',
        ]);
        $grupo = Grupo::create(['codigo' => 'A1', 'turno' => 'MANANA', 'capacidad' => 30, 'gestion' => '2026']);
        $this->grupoId = $grupo->id;
    }

    public function test_crea_inscripcion_con_pk_compuesta(): void
    {
        $inscripcion = Inscripcion::create([
            'postulante_documento' => '9876543',
            'convocatoria_id' => $this->convId,
            'grupo_id' => $this->grupoId,
            'fecha_asignacion' => '2026-02-02 09:00',
        ]);

        $this->assertDatabaseHas('inscripciones', [
            'postulante_documento' => '9876543',
            'convocatoria_id' => $this->convId,
            'grupo_id' => $this->grupoId,
        ]);

        $this->assertSame('9876543', $inscripcion->postulante->documento);
        $this->assertSame('A1', $inscripcion->grupo->codigo);
        $this->assertSame($this->convId, $inscripcion->convocatoria->id);
    }
}
