<?php

namespace App\Modules\AcademicManagement\Models;

use Illuminate\Database\Eloquent\Model;

// Feriado. PK = fecha (no se castea para evitar problemas de binding en la clave).
class Feriado extends Model
{
    protected $table = 'feriados';

    protected $primaryKey = 'fecha';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['fecha', 'descripcion', 'gestion'];
}
