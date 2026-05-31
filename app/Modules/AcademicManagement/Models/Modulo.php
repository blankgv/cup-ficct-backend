<?php

namespace App\Modules\AcademicManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Módulo (edificio). PK = numero.
class Modulo extends Model
{
    protected $table = 'modulos';

    protected $primaryKey = 'numero';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['numero', 'nombre', 'ubicacion'];

    public function aulas(): HasMany
    {
        return $this->hasMany(Aula::class, 'modulo_numero', 'numero');
    }
}
