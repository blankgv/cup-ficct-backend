<?php

namespace App\Modules\ApplicantAdmission\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida fijar cupos de una carrera en la convocatoria.
class SetCuposRequest extends FormRequest
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
            'carrera_codigo' => ['required', 'string', Rule::exists('carreras', 'codigo')],
            'cupos' => ['required', 'integer', 'min:0'],
        ];
    }
}
