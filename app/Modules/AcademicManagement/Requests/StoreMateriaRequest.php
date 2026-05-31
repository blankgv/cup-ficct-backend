<?php

namespace App\Modules\AcademicManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la creación de una materia.
class StoreMateriaRequest extends FormRequest
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
            'sigla' => ['required', 'string', 'max:50', Rule::unique('materias', 'sigla')],
            'peso' => ['required', 'numeric', 'between:0,1'],
        ];
    }
}
