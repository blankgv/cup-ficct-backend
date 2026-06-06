<?php

namespace App\Modules\AcademicManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la creación de un periodo de clases.
class StorePeriodoRequest extends FormRequest
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
            'codigo' => ['required', 'string', 'max:50', Rule::unique('periodos', 'codigo')],
            'gestion' => ['required', 'string', 'max:20'],
            'fecha_inicio_clases' => ['required', 'date'],
            'fecha_fin_clases' => ['required', 'date', 'after_or_equal:fecha_inicio_clases'],
        ];
    }
}
