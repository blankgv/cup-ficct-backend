<?php

namespace App\Modules\AcademicManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la edición de un docente (ci editable; cascada vía FK ON UPDATE).
class UpdateDocenteRequest extends FormRequest
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
        $ci = $this->route('docente');

        return [
            'ci' => ['sometimes', 'string', 'max:50', Rule::unique('docentes', 'ci')->ignore($ci, 'ci')],
            'nombres' => ['sometimes', 'string', 'max:255'],
            'apellidos' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('docentes', 'email')->ignore($ci, 'ci')],
            'telefono' => ['nullable', 'string', 'max:50'],
            'profesion' => ['nullable', 'string', 'max:255'],
        ];
    }
}
