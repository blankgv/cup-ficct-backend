<?php

namespace App\Modules\AcademicManagement\Models;

use App\Modules\AcademicManagement\Enums\Turno;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// Grupo (paralelo/cohorte). Cursa todas las materias.
class Grupo extends Model
{
    protected $table = 'grupos';

    protected $fillable = ['codigo', 'turno', 'capacidad', 'gestion'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacidad' => 'integer',
            'turno' => Turno::class,
        ];
    }

    // Materias que cursa el grupo (muchos a muchos).
    public function materias(): BelongsToMany
    {
        return $this->belongsToMany(Materia::class, 'grupo_materia', 'grupo_id', 'materia_sigla', 'id', 'sigla')
            ->withTimestamps();
    }
}
