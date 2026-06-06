<?php

namespace App\Modules\AcademicManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la creación de un feriado.
class StoreFeriadoRequest extends FormRequest
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
            'fecha' => ['required', 'date', Rule::unique('feriados', 'fecha')],
            'descripcion' => ['required', 'string', 'max:255'],
            'gestion' => ['required', 'string', 'max:20'],
        ];
    }
}
