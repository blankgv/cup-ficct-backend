<?php

namespace App\Modules\AcademicManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Facultad. PK = codigo.
class Facultad extends Model
{
    protected $table = 'facultades';

    protected $primaryKey = 'codigo';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['codigo', 'nombre', 'abreviatura'];

    public function carreras(): HasMany
    {
        return $this->hasMany(Carrera::class, 'facultad_codigo', 'codigo');
    }
}
