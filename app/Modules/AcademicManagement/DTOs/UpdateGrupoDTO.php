<?php

namespace App\Modules\AcademicManagement\DTOs;

// Datos para editar un grupo (campos opcionales).
final readonly class UpdateGrupoDTO
{
    public function __construct(
        public ?string $codigo = null,
        public ?string $turno = null,
        public ?int $capacidad = null,
        public ?string $gestion = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            codigo: $data['codigo'] ?? null,
            turno: $data['turno'] ?? null,
            capacidad: isset($data['capacidad']) ? (int) $data['capacidad'] : null,
            gestion: $data['gestion'] ?? null,
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
            ['codigo' => $this->codigo, 'turno' => $this->turno, 'capacidad' => $this->capacidad, 'gestion' => $this->gestion],
            fn ($v) => $v !== null,
        );
    }
}
