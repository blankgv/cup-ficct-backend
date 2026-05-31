<?php

namespace App\Modules\AcademicManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    // Grupos que cursan esta materia (muchos a muchos).
    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(Grupo::class, 'grupo_materia', 'materia_sigla', 'grupo_id', 'sigla', 'id')
            ->withTimestamps();
    }
}
