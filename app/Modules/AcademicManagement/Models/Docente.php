<?php

namespace App\Modules\AcademicManagement\Models;

use Illuminate\Database\Eloquent\Model;

// Docente. PK = ci.
class Docente extends Model
{
    protected $table = 'docentes';

    protected $primaryKey = 'ci';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['ci', 'nombres', 'apellidos', 'email', 'telefono', 'profesion'];
}
