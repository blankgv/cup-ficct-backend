<?php

namespace App\Modules\Payments\Models;

use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\Authentication\Models\User;
use App\Modules\Payments\Enums\EstadoPago;
use App\Modules\Payments\Enums\MetodoPago;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Pago de la postulación.
class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'postulante_documento', 'convocatoria_id', 'monto', 'concepto', 'metodo', 'fecha_pago', 'estado', 'gateway', 'referencia', 'confirmado_por', 'confirmado_at', 'motivo_rechazo',
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
            'confirmado_at' => 'datetime',
            'confirmado_por' => 'integer',
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

    public function comprobantes(): HasMany
    {
        return $this->hasMany(Comprobante::class, 'pago_id');
    }

    public function confirmadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmado_por');
    }
}
