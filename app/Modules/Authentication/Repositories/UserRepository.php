<?php

namespace App\Modules\Authentication\Repositories;

use App\Models\User;

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
}
