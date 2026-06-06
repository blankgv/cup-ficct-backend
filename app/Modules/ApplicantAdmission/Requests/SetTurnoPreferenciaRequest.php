<?php

namespace App\Modules\ApplicantAdmission\Requests;

use App\Modules\AcademicManagement\Enums\Turno;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida la preferencia de turno del postulante (solo mañana o tarde).
class SetTurnoPreferenciaRequest extends FormRequest
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
            'turno' => ['required', Rule::in([Turno::MANANA->value, Turno::TARDE->value])],
        ];
    }
}
