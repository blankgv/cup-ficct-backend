<?php

namespace App\Modules\AcademicManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Valida la importación de feriados desde la API para una gestión.
class ImportFeriadosRequest extends FormRequest
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
            'gestion' => ['required', 'string', 'max:20'],
        ];
    }
}
