<?php

namespace App\Modules\Authentication\DTOs;

// Datos para crear/editar un rol.
final readonly class CreateRoleDTO
{
    /**
     * @param list<string> $permissions
     */
    public function __construct(
        public ?string $name = null,
        public array $permissions = [],
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            permissions: $data['permissions'] ?? [],
        );
    }
}
