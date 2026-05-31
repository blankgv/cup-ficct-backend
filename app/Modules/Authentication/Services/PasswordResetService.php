<?php

namespace App\Modules\Authentication\Services;

use App\Modules\Authentication\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

// Lógica de contraseñas: cambio en primer ingreso, recuperación y reseteo.
class PasswordResetService
{
    /**
     * Cambia la contraseña del usuario y limpia la bandera de primer ingreso.
     *
     * @throws ValidationException si la contraseña actual no coincide
     */
    public function changePassword(User $user, string $current, string $new): void
    {
        if (! Hash::check($current, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'La contraseña actual no es correcta.',
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($new),
            'must_change_password' => false,
        ])->save();
    }

    // Envía el enlace de recuperación al correo. Devuelve el status del broker.
    public function sendResetLink(string $email): string
    {
        return Password::broker()->sendResetLink(['email' => $email]);
    }

    /**
     * Resetea la contraseña usando el token. Devuelve el status del broker.
     *
     * @param array{token:string,email:string,password:string,password_confirmation:string} $data
     */
    public function resetPassword(array $data): string
    {
        return Password::broker()->reset($data, function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'must_change_password' => false,
            ])->save();
        });
    }
}
