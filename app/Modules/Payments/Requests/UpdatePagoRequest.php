<?php

namespace App\Modules\Payments\Requests;

use App\Modules\Payments\Enums\MetodoPago;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la edición de un pago (no cambia la postulación asociada).
class UpdatePagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'monto' => ['sometimes', 'numeric', 'min:0'],
            'concepto' => ['sometimes', 'string', 'max:255'],
            'metodo' => ['sometimes', Rule::enum(MetodoPago::class)],
            'fecha_pago' => ['sometimes', 'date'],
        ];
    }
}
