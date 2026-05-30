<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authentication\Requests\ForgotPasswordRequest;
use App\Modules\Authentication\Requests\ResetPasswordRequest;
use App\Modules\Authentication\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

// Solicitud y confirmación de reseteo de contraseña.
class PasswordResetController extends Controller
{
    public function __construct(private readonly PasswordResetService $passwords) {}

    #[OA\Post(
        path: '/api/auth/forgot-password',
        tags: ['Password'],
        summary: 'Solicitar enlace de recuperación',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['email'],
            properties: [new OA\Property(property: 'email', type: 'string', example: 'admin@cup-ficct.local')]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Enlace enviado'),
            new OA\Response(response: 422, description: 'Correo inválido'),
        ]
    )]
    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        $status = $this->passwords->sendResetLink($request->validated('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return response()->json(['message' => 'Enlace de recuperación enviado al correo.']);
    }

    #[OA\Post(
        path: '/api/auth/reset-password',
        tags: ['Password'],
        summary: 'Restablecer contraseña con token',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['token', 'email', 'password', 'password_confirmation'],
            properties: [
                new OA\Property(property: 'token', type: 'string'),
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'password', type: 'string'),
                new OA\Property(property: 'password_confirmation', type: 'string'),
            ]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Contraseña restablecida'),
            new OA\Response(response: 422, description: 'Token inválido'),
        ]
    )]
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $status = $this->passwords->resetPassword($request->validated());

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return response()->json(['message' => 'Contraseña restablecida.']);
    }
}
