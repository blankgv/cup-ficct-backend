<?php

namespace App\Modules\Authentication\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Respuesta del login: token + usuario.
 *
 * Espera un array: ['access_token', 'token_type', 'expires_in', 'user' => User].
 */
class AuthTokenResource extends JsonResource
{
    // Sin envoltura "data".
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'access_token' => $this->resource['access_token'],
            'token_type' => $this->resource['token_type'],
            'expires_in' => $this->resource['expires_in'],
            'user' => new UserResource($this->resource['user']),
        ];
    }
}
