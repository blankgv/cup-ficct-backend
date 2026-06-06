<?php

namespace App\Modules\AcademicManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Valida la edición de un feriado (la fecha es la PK; se editan descripción/gestión).
class UpdateFeriadoRequest extends FormRequest
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
            'descripcion' => ['sometimes', 'string', 'max:255'],
            'gestion' => ['sometimes', 'string', 'max:20'],
        ];
    }
}
