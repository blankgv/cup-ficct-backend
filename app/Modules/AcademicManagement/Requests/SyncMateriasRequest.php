<?php

namespace App\Modules\AcademicManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Valida el reemplazo completo de materias de un grupo.
class SyncMateriasRequest extends FormRequest
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
            'siglas' => ['present', 'array'],
            'siglas.*' => ['string', Rule::exists('materias', 'sigla')],
        ];
    }
}
