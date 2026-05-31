<?php

namespace App\Modules\AcademicManagement\DTOs;

// Datos para editar un horario (la PK no cambia).
final readonly class UpdateHorarioDTO
{
    public function __construct(
        public ?string $dia = null,
        public ?string $horaInicio = null,
        public ?string $horaFin = null,
        public ?string $aulaModuloNumero = null,
        public ?int $aulaNumero = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            dia: $data['dia'] ?? null,
            horaInicio: $data['hora_inicio'] ?? null,
            horaFin: $data['hora_fin'] ?? null,
            aulaModuloNumero: $data['aula_modulo_numero'] ?? null,
            aulaNumero: isset($data['aula_numero']) ? (int) $data['aula_numero'] : null,
        );
    }

    /**
     * Solo los campos presentes (claves de columna).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'dia' => $this->dia,
            'hora_inicio' => $this->horaInicio,
            'hora_fin' => $this->horaFin,
            'aula_modulo_numero' => $this->aulaModuloNumero,
            'aula_numero' => $this->aulaNumero,
        ], fn ($v) => $v !== null);
    }
}
