<?php

namespace App\Modules\AcademicManagement\DTOs;

// Datos para crear una facultad.
final readonly class CreateFacultadDTO
{
    public function __construct(
        public string $codigo,
        public string $nombre,
        public string $abreviatura,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            codigo: (string) $data['codigo'],
            nombre: (string) $data['nombre'],
            abreviatura: (string) $data['abreviatura'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['codigo' => $this->codigo, 'nombre' => $this->nombre, 'abreviatura' => $this->abreviatura];
    }
}
