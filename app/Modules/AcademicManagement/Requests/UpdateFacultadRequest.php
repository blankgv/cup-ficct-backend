<?php

namespace App\Modules\AcademicManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la edición de una facultad (codigo editable; cascada vía FK ON UPDATE).
class UpdateFacultadRequest extends FormRequest
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
        $codigo = $this->route('facultad');

        return [
            'codigo' => ['sometimes', 'string', 'max:50', Rule::unique('facultades', 'codigo')->ignore($codigo, 'codigo')],
            'nombre' => ['sometimes', 'string', 'max:255'],
            'abreviatura' => ['sometimes', 'string', 'max:50'],
        ];
    }
}
