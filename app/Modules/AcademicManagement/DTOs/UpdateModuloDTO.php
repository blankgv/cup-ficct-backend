<?php

namespace App\Modules\AcademicManagement\DTOs;

// Datos para editar un módulo (campos opcionales).
final readonly class UpdateModuloDTO
{
    public function __construct(
        public ?string $numero = null,
        public ?string $nombre = null,
        public ?string $ubicacion = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            numero: $data['numero'] ?? null,
            nombre: $data['nombre'] ?? null,
            ubicacion: $data['ubicacion'] ?? null,
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
            ['numero' => $this->numero, 'nombre' => $this->nombre, 'ubicacion' => $this->ubicacion],
            fn ($v) => $v !== null,
        );
    }
}
