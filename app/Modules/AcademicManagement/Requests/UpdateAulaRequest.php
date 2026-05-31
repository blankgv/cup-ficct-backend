<?php

namespace App\Modules\AcademicManagement\Requests;

use App\Modules\AcademicManagement\Enums\AulaTipo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la edición de un aula (no cambia su PK: modulo/numero).
class UpdateAulaRequest extends FormRequest
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
            'capacidad' => ['sometimes', 'integer', 'min:1'],
            'piso' => ['sometimes', 'integer', 'min:0'],
            'tipo' => ['sometimes', Rule::enum(AulaTipo::class)],
        ];
    }
}
