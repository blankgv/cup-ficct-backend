<?php

namespace App\Modules\ApplicantAdmission\Requests;

use App\Modules\ApplicantAdmission\Enums\EstadoConvocatoria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la creación de una convocatoria.
class StoreConvocatoriaRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:255'],
            'gestion' => ['required', 'string', 'max:50'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'estado' => ['sometimes', Rule::enum(EstadoConvocatoria::class)],
        ];
    }
}
