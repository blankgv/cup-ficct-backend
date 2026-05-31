<?php

namespace App\Modules\AcademicManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Valida la edición de una materia. La sigla (PK) no se cambia.
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
            'peso' => ['sometimes', 'numeric', 'between:0,1'],
        ];
    }
}
