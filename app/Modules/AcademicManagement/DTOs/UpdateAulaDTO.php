<?php

namespace App\Modules\AcademicManagement\DTOs;

// Datos para editar un aula (la PK modulo/numero no cambia).
final readonly class UpdateAulaDTO
{
    public function __construct(
        public ?string $nombre = null,
        public ?int $capacidad = null,
        public ?int $piso = null,
        public ?string $tipo = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            nombre: $data['nombre'] ?? null,
            capacidad: isset($data['capacidad']) ? (int) $data['capacidad'] : null,
            piso: isset($data['piso']) ? (int) $data['piso'] : null,
            tipo: $data['tipo'] ?? null,
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
            ['nombre' => $this->nombre, 'capacidad' => $this->capacidad, 'piso' => $this->piso, 'tipo' => $this->tipo],
            fn ($v) => $v !== null,
        );
    }
}
