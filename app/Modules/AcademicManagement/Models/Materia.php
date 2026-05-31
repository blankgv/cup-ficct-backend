<?php

namespace App\Modules\AcademicManagement\Models;

use Illuminate\Database\Eloquent\Model;

// Materia del curso.
class Materia extends Model
{
    protected $fillable = ['nombre', 'sigla', 'peso'];

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
