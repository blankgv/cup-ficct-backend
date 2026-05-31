<?php

namespace App\Modules\ApplicantAdmission\DTOs;

// Datos para editar una convocatoria (campos opcionales).
final readonly class UpdateConvocatoriaDTO
{
    public function __construct(
        public ?string $nombre = null,
        public ?string $gestion = null,
        public ?string $fechaInicio = null,
        public ?string $fechaFin = null,
        public ?string $estado = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            nombre: $data['nombre'] ?? null,
            gestion: $data['gestion'] ?? null,
            fechaInicio: $data['fecha_inicio'] ?? null,
            fechaFin: $data['fecha_fin'] ?? null,
            estado: $data['estado'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'nombre' => $this->nombre,
            'gestion' => $this->gestion,
            'fecha_inicio' => $this->fechaInicio,
            'fecha_fin' => $this->fechaFin,
            'estado' => $this->estado,
        ], fn ($v) => $v !== null);
    }
}
