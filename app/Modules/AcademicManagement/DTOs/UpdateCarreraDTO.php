<?php

namespace App\Modules\AcademicManagement\DTOs;

// Datos para editar una carrera (campos opcionales).
final readonly class UpdateCarreraDTO
{
    public function __construct(
        public ?string $codigo = null,
        public ?string $nombre = null,
        public ?string $facultadCodigo = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            codigo: $data['codigo'] ?? null,
            nombre: $data['nombre'] ?? null,
            facultadCodigo: $data['facultad_codigo'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'facultad_codigo' => $this->facultadCodigo,
        ], fn ($v) => $v !== null);
    }
}
