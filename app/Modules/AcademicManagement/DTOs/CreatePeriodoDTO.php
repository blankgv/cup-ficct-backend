<?php

namespace App\Modules\AcademicManagement\DTOs;

// Datos para crear un periodo de clases.
final readonly class CreatePeriodoDTO
{
    public function __construct(
        public string $codigo,
        public string $gestion,
        public string $fechaInicioClases,
        public string $fechaFinClases,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            codigo: (string) $data['codigo'],
            gestion: (string) $data['gestion'],
            fechaInicioClases: (string) $data['fecha_inicio_clases'],
            fechaFinClases: (string) $data['fecha_fin_clases'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'codigo' => $this->codigo,
            'gestion' => $this->gestion,
            'fecha_inicio_clases' => $this->fechaInicioClases,
            'fecha_fin_clases' => $this->fechaFinClases,
        ];
    }
}
