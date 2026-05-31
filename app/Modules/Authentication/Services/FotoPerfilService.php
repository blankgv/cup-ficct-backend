<?php

namespace App\Modules\Authentication\Services;

use App\Modules\Authentication\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// Maneja la foto de perfil del usuario en R2.
class FotoPerfilService
{
    private const DISK = 'r2';

    // Carpeta de fotos dentro del bucket (configurable por entorno).
    private function carpeta(): string
    {
        return (string) config('storage.r2.fotos');
    }

    // Sube (o reemplaza) la foto y guarda su ruta.
    public function upload(User $user, UploadedFile $archivo): User
    {
        $this->borrarAnterior($user);

        $extension = $archivo->getClientOriginalExtension();
        $path = $archivo->storeAs($this->carpeta(), "{$user->id}.{$extension}", self::DISK);

        $user->update(['foto_perfil_path' => $path]);

        return $user;
    }

    // URL temporal firmada para mostrar la foto.
    public function downloadUrl(User $user): ?string
    {
        if ($user->foto_perfil_path === null) {
            return null;
        }

        return Storage::disk(self::DISK)->temporaryUrl($user->foto_perfil_path, now()->addMinutes(10));
    }

    private function borrarAnterior(User $user): void
    {
        if ($user->foto_perfil_path !== null) {
            Storage::disk(self::DISK)->delete($user->foto_perfil_path);
        }
    }
}
