<?php

namespace App\Modules\Payments\Services;

use App\Modules\Payments\Enums\EstadoPago;
use App\Modules\Payments\Models\Pago;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

// Genera el recibo PDF del pago y lo guarda en R2.
class ReciboService
{
    private const DISK = 'r2';

    // Carpeta de recibos dentro del bucket (configurable por entorno).
    private function carpeta(): string
    {
        return (string) config('storage.r2.recibos');
    }

    // Ruta del recibo dentro del bucket.
    private function path(Pago $pago): string
    {
        return "{$this->carpeta()}/{$pago->id}.pdf";
    }

    // Número de recibo legible.
    private function numero(Pago $pago): string
    {
        return 'R-'.str_pad((string) $pago->id, 6, '0', STR_PAD_LEFT);
    }

    // Genera (si falta) el recibo y devuelve una URL firmada temporal.
    public function urlDescarga(Pago $pago): string
    {
        if ($pago->estado !== EstadoPago::PAGADO) {
            throw ValidationException::withMessages([
                'estado' => 'Solo se genera recibo de un pago confirmado (PAGADO).',
            ]);
        }

        $path = $this->path($pago);

        if (! Storage::disk(self::DISK)->exists($path)) {
            $this->generar($pago, $path);
        }

        return Storage::disk(self::DISK)->temporaryUrl($path, now()->addMinutes(10));
    }

    // Renderiza el PDF y lo guarda en R2.
    private function generar(Pago $pago, string $path): void
    {
        $pago->loadMissing('postulante', 'convocatoria');

        $pdf = Pdf::loadView('recibos.pago', [
            'pago' => $pago,
            'numero' => $this->numero($pago),
            'emitido' => now()->format('d/m/Y H:i'),
        ]);

        Storage::disk(self::DISK)->put($path, $pdf->output());
    }
}
