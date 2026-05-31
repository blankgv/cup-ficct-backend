<?php

namespace App\Modules\AcademicManagement\Requests;

use App\Modules\AcademicManagement\Enums\Turno;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la creación de un grupo.
class StoreGrupoRequest extends FormRequest
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
            'codigo' => [
                'required', 'string', 'max:50',
                Rule::unique('grupos', 'codigo')->where('gestion', $this->input('gestion')),
            ],
            'turno' => ['required', Rule::enum(Turno::class)],
            'capacidad' => ['required', 'integer', 'min:1', 'max:70'],
            'gestion' => ['required', 'string', 'max:50'],
        ];
    }
}
