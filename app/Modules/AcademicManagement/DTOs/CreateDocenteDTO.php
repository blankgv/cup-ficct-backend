<?php

namespace App\Modules\AcademicManagement\DTOs;

// Datos para crear un docente.
final readonly class CreateDocenteDTO
{
    public function __construct(
        public string $ci,
        public string $nombres,
        public string $apellidos,
        public string $email,
        public ?string $telefono = null,
        public ?string $profesion = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            ci: (string) $data['ci'],
            nombres: (string) $data['nombres'],
            apellidos: (string) $data['apellidos'],
            email: (string) $data['email'],
            telefono: $data['telefono'] ?? null,
            profesion: $data['profesion'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'ci' => $this->ci,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'profesion' => $this->profesion,
        ];
    }
}
