<?php

namespace App\Modules\AcademicManagement\Models;

use Illuminate\Database\Eloquent\Model;

// Materia del curso. PK = sigla.
class Materia extends Model
{
    protected $primaryKey = 'sigla';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['sigla', 'nombre', 'peso'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'peso' => 'decimal:4',
        ];
    }
}
