<?php

namespace App\Modules\AcademicManagement\DTOs;

// Datos para crear un horario (grupo y materia vienen de la ruta).
final readonly class CreateHorarioDTO
{
    public function __construct(
        public int $grupoId,
        public string $materiaSigla,
        public string $dia,
        public string $horaInicio,
        public string $horaFin,
        public string $aulaModuloNumero,
        public int $aulaNumero,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data, int $grupoId, string $materiaSigla): self
    {
        return new self(
            grupoId: $grupoId,
            materiaSigla: $materiaSigla,
            dia: (string) $data['dia'],
            horaInicio: (string) $data['hora_inicio'],
            horaFin: (string) $data['hora_fin'],
            aulaModuloNumero: (string) $data['aula_modulo_numero'],
            aulaNumero: (int) $data['aula_numero'],
        );
    }
}
