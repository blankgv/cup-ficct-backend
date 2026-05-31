<?php

namespace App\Modules\AcademicManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la creación de una facultad.
class StoreFacultadRequest extends FormRequest
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
            'codigo' => ['required', 'string', 'max:50', Rule::unique('facultades', 'codigo')],
            'nombre' => ['required', 'string', 'max:255'],
            'abreviatura' => ['required', 'string', 'max:50'],
        ];
    }
}
