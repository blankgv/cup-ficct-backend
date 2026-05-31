<?php

namespace App\Modules\AcademicManagement\DTOs;

// Datos para editar una materia (la sigla/PK no cambia).
final readonly class UpdateMateriaDTO
{
    public function __construct(
        public ?string $nombre = null,
        public ?float $peso = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            nombre: $data['nombre'] ?? null,
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
            ['nombre' => $this->nombre, 'peso' => $this->peso],
            fn ($v) => $v !== null,
        );
    }
}
