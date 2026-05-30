<?php

namespace App\Modules\Authentication\Repositories;

use App\Modules\Authentication\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

// Acceso a datos de usuarios.
class UserRepository
{
    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    public function findById(int|string $id): ?User
    {
        return User::query()->find($id);
    }

    /**
     * Lista paginada con búsqueda por nombre/email y filtro por rol.
     */
    public function paginate(?string $search, ?string $role, int $perPage): LengthAwarePaginator
    {
        return User::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->whereLike('name', "%{$search}%")
                        ->orWhereLike('email', "%{$search}%");
                });
            })
            ->when($role, fn ($q) => $q->role($role))
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function create(array $attributes): User
    {
        return User::create($attributes);
    }
}
