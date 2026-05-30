<?php

namespace App\Modules\Authentication\Services;

use App\Models\User;
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

    /**
     * Crea un usuario, le asigna el rol y lo marca para cambiar contraseña.
     *
     * @param array{name:string,email:string,password:string,role:string} $data
     */
    public function create(array $data): User
    {
        $user = $this->users->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'must_change_password' => true,
        ]);

        $user->assignRole($data['role']);

        return $user;
    }

    /**
     * Actualiza datos del usuario y sincroniza su rol si viene.
     *
     * @param array<string, mixed> $data
     */
    public function update(User $user, array $data): User
    {
        $user->fill(array_filter(
            ['name' => $data['name'] ?? null, 'email' => $data['email'] ?? null],
            fn ($v) => $v !== null,
        ))->save();

        if (! empty($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        return $user;
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
