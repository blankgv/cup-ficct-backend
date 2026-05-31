<?php

namespace App\Modules\ApplicantAdmission\DTOs;

// Datos para crear una postulación (el postulante viene de la ruta).
final readonly class CreatePostulacionDTO
{
    public function __construct(
        public string $postulanteDocumento,
        public int $convocatoriaId,
        public string $carreraPrimera,
        public string $carreraSegunda,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data, string $postulanteDocumento): self
    {
        return new self(
            postulanteDocumento: $postulanteDocumento,
            convocatoriaId: (int) $data['convocatoria_id'],
            carreraPrimera: (string) $data['carrera_primera_codigo'],
            carreraSegunda: (string) $data['carrera_segunda_codigo'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'postulante_documento' => $this->postulanteDocumento,
            'convocatoria_id' => $this->convocatoriaId,
            'carrera_primera_codigo' => $this->carreraPrimera,
            'carrera_segunda_codigo' => $this->carreraSegunda,
            'estado' => 'PENDIENTE',
        ];
    }
}
