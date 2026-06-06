<?php

namespace App\Modules\Payments\DTOs;

// Datos para registrar un pago.
final readonly class CreatePagoDTO
{
    public function __construct(
        public string $postulanteDocumento,
        public int $convocatoriaId,
        public float $monto,
        public string $concepto,
        public string $metodo,
        public string $fechaPago,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            postulanteDocumento: (string) $data['postulante_documento'],
            convocatoriaId: (int) $data['convocatoria_id'],
            monto: (float) $data['monto'],
            concepto: (string) $data['concepto'],
            metodo: (string) $data['metodo'],
            fechaPago: (string) $data['fecha_pago'],
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
            'monto' => $this->monto,
            'concepto' => $this->concepto,
            'metodo' => $this->metodo,
            'fecha_pago' => $this->fechaPago,
            'estado' => 'PENDIENTE',
        ];
    }
}
