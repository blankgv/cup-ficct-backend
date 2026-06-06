<?php

namespace App\Modules\Evaluation\Models;

use App\Modules\AcademicManagement\Models\Materia;
use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\Evaluation\Enums\EstadoAsistencia;
use App\Support\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Asistencia diaria. PK compuesta (postulante_documento, convocatoria_id, materia_sigla, fecha).
class Asistencia extends Model
{
    use HasCompositePrimaryKey;

    protected $table = 'asistencias';

    /** @var list<string> */
    protected $primaryKey = ['postulante_documento', 'convocatoria_id', 'materia_sigla', 'fecha'];

    public $incrementing = false;

    protected $fillable = [
        'postulante_documento', 'convocatoria_id', 'materia_sigla', 'fecha', 'estado',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'convocatoria_id' => 'integer',
            'fecha' => 'date:Y-m-d',
            'estado' => EstadoAsistencia::class,
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

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materia_sigla', 'sigla');
    }
}
