<?php

namespace App\Modules\AcademicManagement\DTOs;

// Datos para editar un docente (campos opcionales).
final readonly class UpdateDocenteDTO
{
    public function __construct(
        public ?string $ci = null,
        public ?string $nombres = null,
        public ?string $apellidos = null,
        public ?string $email = null,
        public ?string $telefono = null,
        public ?string $profesion = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            ci: $data['ci'] ?? null,
            nombres: $data['nombres'] ?? null,
            apellidos: $data['apellidos'] ?? null,
            email: $data['email'] ?? null,
            telefono: $data['telefono'] ?? null,
            profesion: $data['profesion'] ?? null,
        );
    }

    /**
     * Solo los campos presentes.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'ci' => $this->ci,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'profesion' => $this->profesion,
        ], fn ($v) => $v !== null);
    }
}
