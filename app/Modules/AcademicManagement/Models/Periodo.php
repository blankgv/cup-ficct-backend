<?php

namespace App\Modules\AcademicManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Periodo de clases (temporada). Ej: codigo '1/2025'.
class Periodo extends Model
{
    protected $table = 'periodos';

    protected $fillable = ['codigo', 'gestion', 'fecha_inicio_clases', 'fecha_fin_clases'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_inicio_clases' => 'date:Y-m-d',
            'fecha_fin_clases' => 'date:Y-m-d',
        ];
    }

    public function grupos(): HasMany
    {
        return $this->hasMany(Grupo::class, 'periodo_id');
    }
}
