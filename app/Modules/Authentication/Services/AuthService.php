<?php

namespace App\Modules\Authentication\Services;

use App\Modules\Authentication\DTOs\LoginDTO;
use App\Modules\Authentication\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

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
    public function login(LoginDTO $data): array
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
     * Emite un token para un usuario ya existente (auto-login tras registro).
     *
     * @return array<string, mixed>
     */
    public function issueTokenFor(User $user): array
    {
        return $this->buildTokenResponse((string) Auth::guard('api')->login($user));
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
