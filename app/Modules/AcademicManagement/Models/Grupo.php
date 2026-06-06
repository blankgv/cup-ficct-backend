<?php

namespace App\Modules\AcademicManagement\Models;

use App\Modules\AcademicManagement\Enums\Turno;
use App\Modules\ApplicantAdmission\Models\Inscripcion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Grupo (paralelo/cohorte). Cursa todas las materias.
class Grupo extends Model
{
    protected $table = 'grupos';

    protected $fillable = ['codigo', 'turno', 'capacidad', 'gestion', 'convocatoria_id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacidad' => 'integer',
            'convocatoria_id' => 'integer',
            'turno' => Turno::class,
        ];
    }

    // Inscripciones (postulantes asignados) del grupo.
    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'grupo_id');
    }

    // Materias que cursa el grupo (muchos a muchos).
    public function materias(): BelongsToMany
    {
        return $this->belongsToMany(Materia::class, 'grupo_materia', 'grupo_id', 'materia_sigla', 'id', 'sigla')
            ->withTimestamps();
    }
}
