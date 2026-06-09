<?php

namespace App\Modules\ApplicantAdmission\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Valida la carga masiva de postulantes (CSV o Excel).
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
            'archivo' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'],
            // ZIP opcional con los títulos, cada archivo nombrado por documento (9876543.pdf).
            'titulos' => ['nullable', 'file', 'mimes:zip', 'max:51200'],
        ];
    }
}
