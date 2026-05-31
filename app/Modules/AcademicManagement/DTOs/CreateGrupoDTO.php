<?php

namespace App\Modules\AcademicManagement\DTOs;

// Datos para crear un grupo.
final readonly class CreateGrupoDTO
{
    public function __construct(
        public string $codigo,
        public string $turno,
        public int $capacidad,
        public string $gestion,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            codigo: (string) $data['codigo'],
            turno: (string) $data['turno'],
            capacidad: (int) $data['capacidad'],
            gestion: (string) $data['gestion'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'codigo' => $this->codigo,
            'turno' => $this->turno,
            'capacidad' => $this->capacidad,
            'gestion' => $this->gestion,
        ];
    }
}
