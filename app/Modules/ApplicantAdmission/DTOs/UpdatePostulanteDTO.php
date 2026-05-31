<?php

namespace App\Modules\ApplicantAdmission\DTOs;

// Datos para editar un postulante (campos opcionales).
final readonly class UpdatePostulanteDTO
{
    public function __construct(
        public ?string $documento = null,
        public ?string $nombres = null,
        public ?string $apellidos = null,
        public ?string $email = null,
        public ?string $telefono = null,
        public ?string $fechaNacimiento = null,
        public ?string $colegio = null,
        public ?string $ciudad = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            documento: $data['documento'] ?? null,
            nombres: $data['nombres'] ?? null,
            apellidos: $data['apellidos'] ?? null,
            email: $data['email'] ?? null,
            telefono: $data['telefono'] ?? null,
            fechaNacimiento: $data['fecha_nacimiento'] ?? null,
            colegio: $data['colegio'] ?? null,
            ciudad: $data['ciudad'] ?? null,
        );
    }

    /**
     * Solo los campos presentes.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'documento' => $this->documento,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'fecha_nacimiento' => $this->fechaNacimiento,
            'colegio' => $this->colegio,
            'ciudad' => $this->ciudad,
        ], fn ($v) => $v !== null);
    }
}
