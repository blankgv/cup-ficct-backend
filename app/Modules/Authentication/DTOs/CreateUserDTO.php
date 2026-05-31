<?php

namespace App\Modules\Authentication\DTOs;

// Datos para crear un usuario.
final readonly class CreateUserDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public string $role,
        public ?string $username = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: (string) $data['email'],
            password: (string) $data['password'],
            role: (string) $data['role'],
            username: $data['username'] ?? null,
        );
    }
}
