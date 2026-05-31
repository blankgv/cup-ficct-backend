<?php

namespace App\Modules\AcademicManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la creación de una carrera. El código debe empezar con el de su facultad.
class StoreCarreraRequest extends FormRequest
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
                Rule::unique('carreras', 'codigo'),
                function (string $attribute, mixed $value, \Closure $fail) {
                    $facultad = (string) $this->input('facultad_codigo');
                    if ($facultad !== '' && ! str_starts_with((string) $value, $facultad)) {
                        $fail("El código de carrera debe empezar con el código de la facultad ({$facultad}).");
                    }
                },
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'facultad_codigo' => ['required', 'string', Rule::exists('facultades', 'codigo')],
        ];
    }
}
