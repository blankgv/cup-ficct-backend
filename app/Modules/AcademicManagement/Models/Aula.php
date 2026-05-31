<?php

namespace App\Modules\AcademicManagement\Models;

use App\Modules\AcademicManagement\Enums\AulaTipo;
use App\Support\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Aula. PK compuesta (modulo_numero, numero).
class Aula extends Model
{
    use HasCompositePrimaryKey;

    /** @var list<string> */
    protected $primaryKey = ['modulo_numero', 'numero'];

    public $incrementing = false;

    protected $fillable = ['modulo_numero', 'numero', 'nombre', 'capacidad', 'piso', 'tipo'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'numero' => 'integer',
            'capacidad' => 'integer',
            'piso' => 'integer',
            'tipo' => AulaTipo::class,
        ];
    }

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class, 'modulo_numero', 'numero');
    }
}
