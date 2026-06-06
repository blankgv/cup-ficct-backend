<?php

namespace App\Modules\AcademicManagement\DTOs;

// Datos para editar un periodo (campos opcionales).
final readonly class UpdatePeriodoDTO
{
    public function __construct(
        public ?string $codigo = null,
        public ?string $gestion = null,
        public ?string $fechaInicioClases = null,
        public ?string $fechaFinClases = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            codigo: $data['codigo'] ?? null,
            gestion: $data['gestion'] ?? null,
            fechaInicioClases: $data['fecha_inicio_clases'] ?? null,
            fechaFinClases: $data['fecha_fin_clases'] ?? null,
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
            [
                'codigo' => $this->codigo,
                'gestion' => $this->gestion,
                'fecha_inicio_clases' => $this->fechaInicioClases,
                'fecha_fin_clases' => $this->fechaFinClases,
            ],
            fn ($v) => $v !== null,
        );
    }
}
