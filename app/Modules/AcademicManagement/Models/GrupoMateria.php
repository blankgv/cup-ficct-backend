<?php

namespace App\Modules\AcademicManagement\Models;

use App\Support\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Asociación grupo-materia. PK compuesta (grupo_id, materia_sigla).
// Horario referencia esta asociación.
class GrupoMateria extends Model
{
    use HasCompositePrimaryKey;

    protected $table = 'grupo_materia';

    /** @var list<string> */
    protected $primaryKey = ['grupo_id', 'materia_sigla'];

    public $incrementing = false;

    protected $fillable = ['grupo_id', 'materia_sigla', 'docente_ci'];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materia_sigla', 'sigla');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'docente_ci', 'ci');
    }
}
