<?php

namespace App\Modules\Authentication\DTOs;

// Datos para editar un usuario (campos opcionales).
final readonly class UpdateUserDTO
{
    public function __construct(
        public ?string $email = null,
        public ?string $role = null,
        public ?string $username = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'] ?? null,
            role: $data['role'] ?? null,
            username: $data['username'] ?? null,
        );
    }
}
