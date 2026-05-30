<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authentication\DTOs\LoginDTO;
use App\Modules\Authentication\Requests\LoginRequest;
use App\Modules\Authentication\Resources\AuthTokenResource;
use App\Modules\Authentication\Resources\UserResource;
use App\Modules\Authentication\Services\AuthService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

// Endpoints de sesión. Valida y delega en AuthService.
class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    #[OA\Post(
        path: '/api/auth/login',
        tags: ['Auth'],
        summary: 'Iniciar sesión',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', example: 'admin@cup-ficct.local'),
                new OA\Property(property: 'password', type: 'string', example: 'password'),
            ]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Token emitido', content: new OA\JsonContent(ref: '#/components/schemas/AuthToken')),
            new OA\Response(response: 401, description: 'Credenciales inválidas'),
        ]
    )]
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

    #[OA\Get(
        path: '/api/auth/me',
        tags: ['Auth'],
        summary: 'Usuario autenticado',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Usuario actual'),
            new OA\Response(response: 401, description: 'No autenticado'),
        ]
    )]
    public function me(): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($this->authService->currentUser()),
        ]);
    }

    #[OA\Post(
        path: '/api/auth/logout',
        tags: ['Auth'],
        summary: 'Cerrar sesión (invalida el token)',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Sesión cerrada')]
    )]
    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json(['message' => 'Sesión cerrada.']);
    }

    #[OA\Post(
        path: '/api/auth/refresh',
        tags: ['Auth'],
        summary: 'Renovar el token',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Nuevo token', content: new OA\JsonContent(ref: '#/components/schemas/AuthToken'))]
    )]
    public function refresh(): JsonResponse
    {
        return response()->json($this->authService->refresh());
    }
}
