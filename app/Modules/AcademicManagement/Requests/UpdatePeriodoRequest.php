<?php

namespace App\Modules\AcademicManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la edición de un periodo (campos opcionales).
class UpdatePeriodoRequest extends FormRequest
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
            'codigo' => ['sometimes', 'string', 'max:50', Rule::unique('periodos', 'codigo')->ignore($this->route('periodo'))],
            'gestion' => ['sometimes', 'string', 'max:20'],
            'fecha_inicio_clases' => ['sometimes', 'date'],
            'fecha_fin_clases' => ['sometimes', 'date', 'after_or_equal:fecha_inicio_clases'],
        ];
    }
}
