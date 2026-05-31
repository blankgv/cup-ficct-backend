<?php

namespace App\Modules\AcademicManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Carrera. PK = codigo. Pertenece a una facultad.
class Carrera extends Model
{
    protected $table = 'carreras';

    protected $primaryKey = 'codigo';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['codigo', 'nombre', 'facultad_codigo'];

    public function facultad(): BelongsTo
    {
        return $this->belongsTo(Facultad::class, 'facultad_codigo', 'codigo');
    }
}
