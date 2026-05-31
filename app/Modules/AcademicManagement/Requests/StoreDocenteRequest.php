<?php

namespace App\Modules\AcademicManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la creación de un docente.
class StoreDocenteRequest extends FormRequest
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
            'ci' => ['required', 'string', 'max:50', Rule::unique('docentes', 'ci')],
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('docentes', 'email')],
            'telefono' => ['nullable', 'string', 'max:50'],
            'profesion' => ['nullable', 'string', 'max:255'],
        ];
    }
}
