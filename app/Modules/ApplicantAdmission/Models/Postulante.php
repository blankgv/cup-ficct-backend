<?php

namespace App\Modules\ApplicantAdmission\Models;

use App\Modules\Authentication\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Postulante. PK = documento (CI).
class Postulante extends Model
{
    protected $table = 'postulantes';

    protected $primaryKey = 'documento';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['documento', 'nombres', 'apellidos', 'email', 'telefono', 'fecha_nacimiento', 'colegio', 'ciudad', 'titulo_bachiller_path', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date:Y-m-d',
        ];
    }
}
