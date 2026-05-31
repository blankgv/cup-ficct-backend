<?php

namespace App\Modules\AcademicManagement\Models;

use App\Modules\AcademicManagement\Enums\Turno;
use Illuminate\Database\Eloquent\Model;

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
}
