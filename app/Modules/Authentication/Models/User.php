<?php

namespace App\Modules\Authentication\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

#[Fillable(['email', 'username', 'password', 'must_change_password', 'role_id', 'foto_perfil_path'])]
#[Hidden(['password'])]
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // Rol del usuario.
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    // Asigna un rol por nombre.
    public function assignRole(string $name): self
    {
        $role = Role::query()->where('name', $name)->firstOrFail();
        $this->role()->associate($role)->save();

        return $this;
    }

    public function hasRole(string $name): bool
    {
        return $this->role?->name === $name;
    }

    // ¿El rol del usuario tiene el permiso?
    public function hasPermission(string $permission): bool
    {
        return $this->permissionNames()->contains($permission);
    }

    /**
     * Nombres de los permisos del usuario (vía su rol).
     *
     * @return Collection<int, string>
     */
    public function permissionNames(): Collection
    {
        return $this->role
            ? $this->role->permissions->pluck('name')
            : collect();
    }

    // Claim "sub" del JWT.
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * @return array<string, mixed>
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
