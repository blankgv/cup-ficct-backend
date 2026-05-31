<?php

namespace App\Modules\AcademicManagement\Services;

use App\Modules\AcademicManagement\Models\Docente;
use App\Modules\Authentication\Authorization\Role;
use App\Modules\Authentication\DTOs\CreateUserDTO;
use App\Modules\Authentication\Models\User;
use App\Modules\Authentication\Services\UserService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

// Crea/elimina la cuenta de usuario del docente (rol DOCENTE).
class DocenteCuentaService
{
    public function __construct(private readonly UserService $users) {}

    /**
     * Crea la cuenta del docente y la vincula. Devuelve el usuario y la
     * contraseña temporal (el docente la cambia al primer ingreso).
     *
     * @return array{user: User, temporary_password: string}
     */
    public function create(Docente $docente): array
    {
        if ($docente->user_id !== null) {
            throw ValidationException::withMessages(['user' => 'El docente ya tiene cuenta.']);
        }

        if (User::query()->where('email', $docente->email)->exists()) {
            throw ValidationException::withMessages(['email' => 'Ya existe un usuario con ese correo.']);
        }

        $password = Str::password(12);

        $user = $this->users->create(new CreateUserDTO(
            email: $docente->email,
            password: $password,
            role: Role::DOCENTE,
        ));

        $docente->update(['user_id' => $user->id]);

        return ['user' => $user, 'temporary_password' => $password];
    }

    // Elimina la cuenta vinculada (la FK deja user_id en null).
    public function delete(Docente $docente): void
    {
        if ($docente->user_id === null) {
            throw ValidationException::withMessages(['user' => 'El docente no tiene cuenta.']);
        }

        User::query()->whereKey($docente->user_id)->delete();
    }
}
