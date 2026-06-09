<?php

namespace App\Modules\ApplicantAdmission\Services;

use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\Authentication\Authorization\Role;
use App\Modules\Authentication\DTOs\CreateUserDTO;
use App\Modules\Authentication\Services\AuthService;
use App\Modules\Authentication\Services\UserService;
use Illuminate\Support\Facades\DB;

// Auto-registro público: crea la cuenta (rol POSTULANTE) + el postulante y autentica.
class RegistroPostulanteService
{
    public function __construct(
        private readonly UserService $users,
        private readonly AuthService $auth,
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed> token + user para auto-login
     */
    public function register(array $data): array
    {
        $user = DB::transaction(function () use ($data) {
            $user = $this->users->create(new CreateUserDTO(
                email: (string) $data['email'],
                password: (string) $data['password'],
                role: Role::POSTULANTE,
                mustChangePassword: false,
            ));

            Postulante::create([
                'documento' => $data['documento'],
                'nombres' => $data['nombres'],
                'apellidos' => $data['apellidos'],
                'email' => $data['email'],
                'telefono' => $data['telefono'] ?? null,
                'user_id' => $user->id,
            ]);

            return $user;
        });

        return [
            ...$this->auth->issueTokenFor($user),
            'user' => $user,
        ];
    }
}
