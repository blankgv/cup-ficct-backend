<?php

namespace App\Modules\AcademicManagement\Requests;

use App\Modules\AcademicManagement\Enums\AulaTipo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la creación de un aula dentro de un módulo.
class StoreAulaRequest extends FormRequest
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
        $moduloNumero = $this->route('modulo')->numero;

        return [
            'numero' => [
                'required', 'integer', 'min:1',
                Rule::unique('aulas', 'numero')->where('modulo_numero', $moduloNumero),
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'capacidad' => ['required', 'integer', 'min:1'],
            'piso' => ['required', 'integer', 'min:0'],
            'tipo' => ['required', Rule::enum(AulaTipo::class)],
        ];
    }
}
