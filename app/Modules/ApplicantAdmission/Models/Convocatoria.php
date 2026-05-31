<?php

namespace App\Modules\ApplicantAdmission\Models;

use App\Modules\AcademicManagement\Models\Carrera;
use App\Modules\ApplicantAdmission\Enums\EstadoConvocatoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// Convocatoria (periodo de admisión).
class Convocatoria extends Model
{
    protected $table = 'convocatorias';

    protected $fillable = ['nombre', 'gestion', 'fecha_inicio', 'fecha_fin', 'estado'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date:Y-m-d',
            'fecha_fin' => 'date:Y-m-d',
            'estado' => EstadoConvocatoria::class,
        ];
    }

    // Carreras ofertadas con sus cupos (muchos a muchos).
    public function carreras(): BelongsToMany
    {
        return $this->belongsToMany(Carrera::class, 'carrera_convocatoria', 'convocatoria_id', 'carrera_codigo', 'id', 'codigo')
            ->withPivot('cupos')
            ->withTimestamps();
    }
}
