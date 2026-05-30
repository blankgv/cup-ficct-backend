<?php

namespace App\Modules\Authentication\DTOs;

// Lista de permisos a asignar a un rol.
final readonly class AssignPermissionsDTO
{
    /**
     * @param list<string> $permissions
     */
    public function __construct(
        public array $permissions = [],
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            permissions: $data['permissions'] ?? [],
        );
    }
}
