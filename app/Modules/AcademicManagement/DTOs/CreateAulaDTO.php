<?php

namespace App\Modules\AcademicManagement\DTOs;

// Datos para crear un aula (el módulo viene de la ruta).
final readonly class CreateAulaDTO
{
    public function __construct(
        public string $moduloNumero,
        public int $numero,
        public string $nombre,
        public int $capacidad,
        public int $piso,
        public string $tipo,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data, string $moduloNumero): self
    {
        return new self(
            moduloNumero: $moduloNumero,
            numero: (int) $data['numero'],
            nombre: (string) $data['nombre'],
            capacidad: (int) $data['capacidad'],
            piso: (int) $data['piso'],
            tipo: (string) $data['tipo'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'modulo_numero' => $this->moduloNumero,
            'numero' => $this->numero,
            'nombre' => $this->nombre,
            'capacidad' => $this->capacidad,
            'piso' => $this->piso,
            'tipo' => $this->tipo,
        ];
    }
}
