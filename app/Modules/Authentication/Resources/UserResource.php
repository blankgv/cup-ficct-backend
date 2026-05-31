<?php

namespace App\Modules\Authentication\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formatea el usuario para la respuesta API.
 *
 * @mixin \App\Modules\Authentication\Models\User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'username' => $this->username,
            'foto_perfil_path' => $this->foto_perfil_path,
            'must_change_password' => $this->must_change_password,
            'role' => $this->role?->name,
            'permissions' => $this->permissionNames(),
            'created_at' => $this->created_at,
        ];
    }
}
