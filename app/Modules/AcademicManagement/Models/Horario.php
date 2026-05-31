<?php

namespace App\Modules\AcademicManagement\Models;

use App\Modules\AcademicManagement\Enums\DiaSemana;
use App\Support\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;

// Horario. PK compuesta (grupo_id, materia_sigla, numero).
class Horario extends Model
{
    use HasCompositePrimaryKey;

    protected $table = 'horarios';

    /** @var list<string> */
    protected $primaryKey = ['grupo_id', 'materia_sigla', 'numero'];

    public $incrementing = false;

    protected $fillable = [
        'grupo_id', 'materia_sigla', 'numero',
        'dia', 'hora_inicio', 'hora_fin',
        'aula_modulo_numero', 'aula_numero',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'numero' => 'integer',
            'aula_numero' => 'integer',
            'dia' => DiaSemana::class,
        ];
    }

    // Aula donde ocurre (FK compuesta, resolución manual).
    public function aula(): ?Aula
    {
        return Aula::query()
            ->where('modulo_numero', $this->aula_modulo_numero)
            ->where('numero', $this->aula_numero)
            ->first();
    }
}
