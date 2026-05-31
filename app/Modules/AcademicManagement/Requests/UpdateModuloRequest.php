<?php

namespace App\Modules\AcademicManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la edición de un módulo (numero editable; cascada vía FK ON UPDATE).
class UpdateModuloRequest extends FormRequest
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
            'numero' => ['sometimes', 'string', 'max:50', Rule::unique('modulos', 'numero')->ignore($this->route('modulo'))],
            'nombre' => ['sometimes', 'string', 'max:255'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
        ];
    }
}
