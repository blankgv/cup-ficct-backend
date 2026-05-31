<?php

namespace App\Modules\AcademicManagement\DTOs;

// Datos para crear una materia.
final readonly class CreateMateriaDTO
{
    public function __construct(
        public string $nombre,
        public string $sigla,
        public float $peso,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            nombre: (string) $data['nombre'],
            sigla: (string) $data['sigla'],
            peso: (float) $data['peso'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['nombre' => $this->nombre, 'sigla' => $this->sigla, 'peso' => $this->peso];
    }
}
