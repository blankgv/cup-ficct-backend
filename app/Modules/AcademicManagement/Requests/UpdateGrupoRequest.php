<?php

namespace App\Modules\AcademicManagement\Requests;

use App\Modules\AcademicManagement\Enums\Turno;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la edición de un grupo.
class UpdateGrupoRequest extends FormRequest
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
        $grupo = $this->route('grupo');
        $gestion = $this->input('gestion', $grupo->gestion);

        return [
            'codigo' => [
                'sometimes', 'string', 'max:50',
                Rule::unique('grupos', 'codigo')->where('gestion', $gestion)->ignore($grupo),
            ],
            'turno' => ['sometimes', Rule::enum(Turno::class)],
            'capacidad' => ['sometimes', 'integer', 'min:1', 'max:70'],
            'gestion' => ['sometimes', 'string', 'max:50'],
        ];
    }
}
