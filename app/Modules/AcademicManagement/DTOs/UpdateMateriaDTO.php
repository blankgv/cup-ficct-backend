<?php

namespace App\Modules\AcademicManagement\DTOs;

// Datos para editar una materia (campos opcionales).
final readonly class UpdateMateriaDTO
{
    public function __construct(
        public ?string $nombre = null,
        public ?string $sigla = null,
        public ?float $peso = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            nombre: $data['nombre'] ?? null,
            sigla: $data['sigla'] ?? null,
            peso: isset($data['peso']) ? (float) $data['peso'] : null,
        );
    }

    /**
     * Solo los campos presentes.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter(
            ['nombre' => $this->nombre, 'sigla' => $this->sigla, 'peso' => $this->peso],
            fn ($v) => $v !== null,
        );
    }
}
