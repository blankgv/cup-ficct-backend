<?php

namespace App\Modules\ApplicantAdmission\Models;

use Illuminate\Database\Eloquent\Model;

// Postulante. PK = documento (CI).
class Postulante extends Model
{
    protected $table = 'postulantes';

    protected $primaryKey = 'documento';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['documento', 'nombres', 'apellidos', 'email', 'telefono', 'fecha_nacimiento', 'colegio', 'ciudad', 'titulo_bachiller_path'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date:Y-m-d',
        ];
    }
}
