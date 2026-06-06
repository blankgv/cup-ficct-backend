<?php

namespace App\Modules\Payments\Requests;

use App\Modules\Payments\Enums\MetodoPago;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida el registro de un pago (debe existir la postulación).
class StorePagoRequest extends FormRequest
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
            'postulante_documento' => ['required', 'string'],
            'convocatoria_id' => [
                'required', 'integer',
                Rule::exists('postulaciones', 'convocatoria_id')->where('postulante_documento', $this->input('postulante_documento')),
            ],
            'monto' => ['required', 'numeric', 'min:0'],
            'concepto' => ['required', 'string', 'max:255'],
            'metodo' => ['required', Rule::enum(MetodoPago::class)],
            'fecha_pago' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'convocatoria_id.exists' => 'No existe una postulación de ese postulante en esa convocatoria.',
        ];
    }
}
