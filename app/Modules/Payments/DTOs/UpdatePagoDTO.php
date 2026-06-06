<?php

namespace App\Modules\Payments\DTOs;

// Datos para editar un pago (campos opcionales).
final readonly class UpdatePagoDTO
{
    public function __construct(
        public ?float $monto = null,
        public ?string $concepto = null,
        public ?string $metodo = null,
        public ?string $fechaPago = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            monto: isset($data['monto']) ? (float) $data['monto'] : null,
            concepto: $data['concepto'] ?? null,
            metodo: $data['metodo'] ?? null,
            fechaPago: $data['fecha_pago'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'monto' => $this->monto,
            'concepto' => $this->concepto,
            'metodo' => $this->metodo,
            'fecha_pago' => $this->fechaPago,
        ], fn ($v) => $v !== null);
    }
}
