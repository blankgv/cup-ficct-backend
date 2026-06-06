<?php

namespace App\Modules\Payments\Models;

use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\Payments\Enums\EstadoPago;
use App\Modules\Payments\Enums\MetodoPago;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Pago de la postulación.
class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'postulante_documento', 'convocatoria_id', 'monto', 'concepto', 'metodo', 'fecha_pago', 'estado', 'gateway', 'referencia',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'convocatoria_id' => 'integer',
            'fecha_pago' => 'datetime',
            'metodo' => MetodoPago::class,
            'estado' => EstadoPago::class,
        ];
    }

    public function postulante(): BelongsTo
    {
        return $this->belongsTo(Postulante::class, 'postulante_documento', 'documento');
    }

    public function convocatoria(): BelongsTo
    {
        return $this->belongsTo(Convocatoria::class, 'convocatoria_id');
    }
}
