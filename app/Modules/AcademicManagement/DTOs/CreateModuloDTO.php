<?php

namespace App\Modules\AcademicManagement\DTOs;

// Datos para crear un módulo.
final readonly class CreateModuloDTO
{
    public function __construct(
        public string $numero,
        public string $nombre,
        public ?string $ubicacion = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            numero: (string) $data['numero'],
            nombre: (string) $data['nombre'],
            ubicacion: $data['ubicacion'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['numero' => $this->numero, 'nombre' => $this->nombre, 'ubicacion' => $this->ubicacion];
    }
}
