<?php

namespace App\Modules\Payments\Services;

use App\Modules\Payments\Models\Comprobante;
use App\Modules\Payments\Models\Pago;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Maneja los comprobantes de pago en R2.
class ComprobanteService
{
    private const DISK = 'r2';

    // Carpeta de comprobantes dentro del bucket (configurable por entorno).
    private function carpeta(): string
    {
        return (string) config('storage.r2.comprobantes');
    }

    // Sube un comprobante del pago y lo registra.
    public function upload(Pago $pago, UploadedFile $archivo): Comprobante
    {
        $extension = $archivo->getClientOriginalExtension();
        $nombre = Str::uuid()->toString().'.'.$extension;
        $path = $archivo->storeAs("{$this->carpeta()}/{$pago->id}", $nombre, self::DISK);

        return $pago->comprobantes()->create([
            'path' => $path,
            'nombre_original' => $archivo->getClientOriginalName(),
            'mime' => $archivo->getClientMimeType(),
            'tamano' => $archivo->getSize(),
        ]);
    }

    // URL temporal firmada para descargar el comprobante.
    public function downloadUrl(Comprobante $comprobante): string
    {
        return Storage::disk(self::DISK)->temporaryUrl($comprobante->path, now()->addMinutes(10));
    }
}
