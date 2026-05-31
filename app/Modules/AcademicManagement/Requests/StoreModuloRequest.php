<?php

namespace App\Modules\AcademicManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la creación de un módulo.
class StoreModuloRequest extends FormRequest
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
            'numero' => ['required', 'string', 'max:50', Rule::unique('modulos', 'numero')],
            'nombre' => ['required', 'string', 'max:255'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
        ];
    }
}
