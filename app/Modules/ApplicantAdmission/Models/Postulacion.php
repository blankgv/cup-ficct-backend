<?php

namespace App\Modules\ApplicantAdmission\Models;

use App\Modules\AcademicManagement\Models\Carrera;
use App\Modules\ApplicantAdmission\Enums\EstadoPostulacion;
use App\Support\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Postulación. PK compuesta (postulante_documento, convocatoria_id).
class Postulacion extends Model
{
    use HasCompositePrimaryKey;

    protected $table = 'postulaciones';

    /** @var list<string> */
    protected $primaryKey = ['postulante_documento', 'convocatoria_id'];

    public $incrementing = false;

    protected $fillable = [
        'postulante_documento', 'convocatoria_id',
        'carrera_primera_codigo', 'carrera_segunda_codigo', 'estado',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'convocatoria_id' => 'integer',
            'estado' => EstadoPostulacion::class,
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

    public function carreraPrimera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'carrera_primera_codigo', 'codigo');
    }

    public function carreraSegunda(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'carrera_segunda_codigo', 'codigo');
    }
}
