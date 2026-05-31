<?php

namespace App\Modules\AcademicManagement\DTOs;

// Datos para editar una facultad (campos opcionales).
final readonly class UpdateFacultadDTO
{
    public function __construct(
        public ?string $codigo = null,
        public ?string $nombre = null,
        public ?string $abreviatura = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            codigo: $data['codigo'] ?? null,
            nombre: $data['nombre'] ?? null,
            abreviatura: $data['abreviatura'] ?? null,
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
            'abreviatura' => $this->abreviatura,
        ], fn ($v) => $v !== null);
    }
}
