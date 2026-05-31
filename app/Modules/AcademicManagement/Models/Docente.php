<?php

namespace App\Modules\AcademicManagement\Models;

use App\Modules\Authentication\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Docente. PK = ci.
class Docente extends Model
{
    protected $table = 'docentes';

    protected $primaryKey = 'ci';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['ci', 'nombres', 'apellidos', 'email', 'telefono', 'profesion', 'user_id'];

    // Cuenta de usuario vinculada.
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
