<?php

namespace App\Modules\Payments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Comprobante de un pago (archivo en R2).
class Comprobante extends Model
{
    protected $table = 'comprobantes';

    protected $fillable = [
        'pago_id', 'path', 'nombre_original', 'mime', 'tamano',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pago_id' => 'integer',
            'tamano' => 'integer',
        ];
    }

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'pago_id');
    }
}
