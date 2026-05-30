<?php

namespace App\Modules\Authentication\Services;

use App\Models\User;
use App\Modules\Authentication\DTOs\LoginData;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

// Lógica de autenticación JWT.
class AuthService
{
    /**
     * Login. Devuelve el token o lanza error si las credenciales fallan.
     *
     * @return array<string, mixed>
     *
     * @throws AuthenticationException
     */
    public function login(LoginData $data): array
    {
        $token = Auth::guard('api')->attempt($data->toCredentials());

        if ($token === false) {
            throw new AuthenticationException('Credenciales inválidas.');
        }

        return $this->buildTokenResponse((string) $token);
    }

    // Invalida el token actual.
    public function logout(): void
    {
        Auth::guard('api')->logout();
    }

    /**
     * Renueva el token.
     *
     * @return array<string, mixed>
     */
    public function refresh(): array
    {
        return $this->buildTokenResponse((string) Auth::guard('api')->refresh());
    }

    // Usuario autenticado.
    public function currentUser(): mixed
    {
        return Auth::guard('api')->user();
    }

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

    /**
     * Arma la respuesta del token.
     *
     * @return array<string, mixed>
     */
    private function buildTokenResponse(string $token): array
    {
        // ttl (minutos) -> expires_in (segundos).
        $ttlMinutes = (int) Auth::guard('api')->factory()->getTTL();

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $ttlMinutes * 60,
        ];
    }
}
