<?php

namespace App\Modules\Authentication\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Valida la subida de la foto de perfil (imagen, máx 2 MB).
class UploadFotoRequest extends FormRequest
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
            'foto' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
