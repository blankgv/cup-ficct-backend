<?php

namespace App\Modules\ApplicantAdmission\DTOs;

// Datos para crear un postulante.
final readonly class CreatePostulanteDTO
{
    public function __construct(
        public string $documento,
        public string $nombres,
        public string $apellidos,
        public string $email,
        public string $fechaNacimiento,
        public string $colegio,
        public string $ciudad,
        public ?string $telefono = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            documento: (string) $data['documento'],
            nombres: (string) $data['nombres'],
            apellidos: (string) $data['apellidos'],
            email: (string) $data['email'],
            fechaNacimiento: (string) $data['fecha_nacimiento'],
            colegio: (string) $data['colegio'],
            ciudad: (string) $data['ciudad'],
            telefono: $data['telefono'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'documento' => $this->documento,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'fecha_nacimiento' => $this->fechaNacimiento,
            'colegio' => $this->colegio,
            'ciudad' => $this->ciudad,
        ];
    }
}
