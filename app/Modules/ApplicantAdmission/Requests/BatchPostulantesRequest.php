<?php

namespace App\Modules\ApplicantAdmission\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Valida la carga masiva de postulantes (CSV).
class BatchPostulantesRequest extends FormRequest
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
            'archivo' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ];
    }
}
