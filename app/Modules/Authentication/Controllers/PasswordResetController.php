<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authentication\Requests\ForgotPasswordRequest;
use App\Modules\Authentication\Requests\ResetPasswordRequest;
use App\Modules\Authentication\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

// Solicitud y confirmación de reseteo de contraseña.
class PasswordResetController extends Controller
{
    public function __construct(private readonly PasswordResetService $passwords) {}

    // POST /api/auth/forgot-password
    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        $status = $this->passwords->sendResetLink($request->validated('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return response()->json(['message' => 'Enlace de recuperación enviado al correo.']);
    }

    // POST /api/auth/reset-password
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $status = $this->passwords->resetPassword($request->validated());

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return response()->json(['message' => 'Contraseña restablecida.']);
    }
}
