<?php

namespace App\Modules\ApplicantAdmission\Services;

use App\Modules\ApplicantAdmission\Models\Postulante;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// Maneja el título de bachiller del postulante en R2.
class TituloService
{
    private const DISK = 'r2';

    // Carpeta del título dentro del bucket (configurable por entorno).
    private function carpeta(): string
    {
        return (string) config('storage.r2.titulos');
    }

    // Sube (o reemplaza) el título y guarda su ruta.
    public function upload(Postulante $postulante, UploadedFile $archivo): Postulante
    {
        $this->borrarAnterior($postulante);

        $extension = $archivo->getClientOriginalExtension();
        $path = $archivo->storeAs($this->carpeta(), "{$postulante->documento}.{$extension}", self::DISK);

        $postulante->update(['titulo_bachiller_path' => $path]);

        return $postulante;
    }

    // URL temporal firmada para descargar el título.
    public function downloadUrl(Postulante $postulante): ?string
    {
        if ($postulante->titulo_bachiller_path === null) {
            return null;
        }

        return Storage::disk(self::DISK)->temporaryUrl($postulante->titulo_bachiller_path, now()->addMinutes(10));
    }

    private function borrarAnterior(Postulante $postulante): void
    {
        if ($postulante->titulo_bachiller_path !== null) {
            Storage::disk(self::DISK)->delete($postulante->titulo_bachiller_path);
        }
    }
}
