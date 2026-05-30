<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authentication\DTOs\LoginDTO;
use App\Modules\Authentication\Requests\LoginRequest;
use App\Modules\Authentication\Resources\AuthTokenResource;
use App\Modules\Authentication\Resources\UserResource;
use App\Modules\Authentication\Services\AuthService;
use Illuminate\Http\JsonResponse;

// Endpoints de sesión. Valida y delega en AuthService.
class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    // POST /api/auth/login
    public function login(LoginRequest $request): AuthTokenResource
    {
        $token = $this->authService->login(
            LoginDTO::fromArray($request->validated())
        );

        return new AuthTokenResource([
            ...$token,
            'user' => $this->authService->currentUser(),
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
}
