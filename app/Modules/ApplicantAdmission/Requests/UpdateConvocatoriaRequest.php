<?php

namespace App\Modules\ApplicantAdmission\Requests;

use App\Modules\ApplicantAdmission\Enums\EstadoConvocatoria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la edición de una convocatoria.
class UpdateConvocatoriaRequest extends FormRequest
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
            'nombre' => ['sometimes', 'string', 'max:255'],
            'gestion' => ['sometimes', 'string', 'max:50'],
            'fecha_inicio' => ['sometimes', 'date'],
            'fecha_fin' => ['sometimes', 'date', 'after_or_equal:fecha_inicio'],
            'estado' => ['sometimes', Rule::enum(EstadoConvocatoria::class)],
        ];
    }
}
