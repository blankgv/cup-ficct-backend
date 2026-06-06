<?php

namespace App\Modules\ApplicantAdmission\Models;

use App\Modules\AcademicManagement\Models\Carrera;
use App\Modules\AcademicManagement\Models\Grupo;
use App\Support\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Inscripción del postulante a un grupo. PK compuesta (postulante_documento, convocatoria_id).
class Inscripcion extends Model
{
    use HasCompositePrimaryKey;

    protected $table = 'inscripciones';

    /** @var list<string> */
    protected $primaryKey = ['postulante_documento', 'convocatoria_id'];

    public $incrementing = false;

    protected $fillable = [
        'postulante_documento', 'convocatoria_id', 'grupo_id', 'fecha_asignacion', 'carrera_asignada_codigo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'convocatoria_id' => 'integer',
            'grupo_id' => 'integer',
            'fecha_asignacion' => 'datetime',
        ];
    }

    public function postulante(): BelongsTo
    {
        return $this->belongsTo(Postulante::class, 'postulante_documento', 'documento');
    }

    public function convocatoria(): BelongsTo
    {
        return $this->belongsTo(Convocatoria::class, 'convocatoria_id');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function carreraAsignada(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'carrera_asignada_codigo', 'codigo');
    }
}
