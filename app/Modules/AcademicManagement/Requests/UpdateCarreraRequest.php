<?php

namespace App\Modules\AcademicManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la edición de una carrera (código debe empezar con el de su facultad).
class UpdateCarreraRequest extends FormRequest
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
        $carrera = $this->route('carrera');

        return [
            'codigo' => [
                'sometimes', 'string', 'max:50',
                Rule::unique('carreras', 'codigo')->ignore($carrera, 'codigo'),
                function (string $attribute, mixed $value, \Closure $fail) use ($carrera) {
                    $facultad = (string) $this->input('facultad_codigo', $carrera->facultad_codigo);
                    if ($facultad !== '' && ! str_starts_with((string) $value, $facultad)) {
                        $fail("El código de carrera debe empezar con el código de la facultad ({$facultad}).");
                    }
                },
            ],
            'nombre' => ['sometimes', 'string', 'max:255'],
            'facultad_codigo' => ['sometimes', 'string', Rule::exists('facultades', 'codigo')],
        ];
    }
}
