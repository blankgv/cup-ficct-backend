<?php

namespace App\Modules\Authentication\Services;

use App\Modules\Authentication\DTOs\CreateUserDTO;
use App\Modules\Authentication\DTOs\UpdateUserDTO;
use App\Modules\Authentication\Models\User;
use App\Modules\Authentication\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

// Lógica de negocio de usuarios (CRUD + asignación de rol).
class UserService
{
    public function __construct(private readonly UserRepository $users) {}

    public function list(?string $search, ?string $role, int $perPage = 15): LengthAwarePaginator
    {
        return $this->users->paginate($search, $role, $perPage);
    }

    // Crea un usuario, le asigna el rol y lo marca para cambiar contraseña.
    public function create(CreateUserDTO $data): User
    {
        $user = $this->users->create([
            'email' => $data->email,
            'username' => $data->username,
            'password' => Hash::make($data->password),
            'must_change_password' => true,
        ]);

        $user->assignRole($data->role);

        return $user;
    }

    // Actualiza datos del usuario y sincroniza su rol si viene.
    public function update(User $user, UpdateUserDTO $data): User
    {
        $user->fill(array_filter(
            ['email' => $data->email, 'username' => $data->username],
            fn ($v) => $v !== null,
        ))->save();

        if ($data->role !== null) {
            $user->assignRole($data->role);
        }

        return $user;
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
