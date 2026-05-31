<?php

namespace App\Modules\AcademicManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la edición de una materia.
class UpdateMateriaRequest extends FormRequest
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
            'sigla' => ['sometimes', 'string', 'max:50', Rule::unique('materias', 'sigla')->ignore($this->route('materia'))],
            'peso' => ['sometimes', 'numeric', 'between:0,1'],
        ];
    }
}
