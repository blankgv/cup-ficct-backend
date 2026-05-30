<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authentication\DTOs\LoginData;
use App\Modules\Authentication\Requests\ChangePasswordRequest;
use App\Modules\Authentication\Requests\ForgotPasswordRequest;
use App\Modules\Authentication\Requests\LoginRequest;
use App\Modules\Authentication\Requests\ResetPasswordRequest;
use App\Modules\Authentication\Resources\UserResource;
use App\Modules\Authentication\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

// Endpoints de autenticación. Valida y delega en AuthService.
class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    // POST /api/auth/login
    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->authService->login(
            LoginData::fromArray($request->validated())
        );

        return response()->json([
            ...$token,
            'user' => new UserResource($this->authService->currentUser()),
        ]);
    }

    // GET /api/auth/me
    public function me(): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($this->authService->currentUser()),
        ]);
    }

    // POST /api/auth/logout
    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json(['message' => 'Sesión cerrada.']);
    }

    // POST /api/auth/refresh
    public function refresh(): JsonResponse
    {
        return response()->json($this->authService->refresh());
    }

    // POST /api/auth/change-password
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword(
            $this->authService->currentUser(),
            $request->validated('current_password'),
            $request->validated('new_password'),
        );

        return response()->json(['message' => 'Contraseña actualizada.']);
    }

    // POST /api/auth/forgot-password
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = $this->authService->sendResetLink($request->validated('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return response()->json(['message' => 'Enlace de recuperación enviado al correo.']);
    }

    // POST /api/auth/reset-password
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = $this->authService->resetPassword($request->validated());

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return response()->json(['message' => 'Contraseña restablecida.']);
    }
}
