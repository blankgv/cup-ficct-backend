<?php

namespace App\Modules\ApplicantAdmission\DTOs;

// Datos para crear una convocatoria.
final readonly class CreateConvocatoriaDTO
{
    public function __construct(
        public string $nombre,
        public string $gestion,
        public string $fechaInicio,
        public string $fechaFin,
        public string $estado = 'ABIERTA',
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            nombre: (string) $data['nombre'],
            gestion: (string) $data['gestion'],
            fechaInicio: (string) $data['fecha_inicio'],
            fechaFin: (string) $data['fecha_fin'],
            estado: $data['estado'] ?? 'ABIERTA',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'nombre' => $this->nombre,
            'gestion' => $this->gestion,
            'fecha_inicio' => $this->fechaInicio,
            'fecha_fin' => $this->fechaFin,
            'estado' => $this->estado,
        ];
    }
}
