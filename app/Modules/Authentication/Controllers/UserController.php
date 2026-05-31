<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authentication\DTOs\CreateUserDTO;
use App\Modules\Authentication\DTOs\UpdateUserDTO;
use App\Modules\Authentication\Models\User;
use App\Modules\Authentication\Requests\ChangePasswordRequest;
use App\Modules\Authentication\Requests\StoreUserRequest;
use App\Modules\Authentication\Requests\UpdateUserRequest;
use App\Modules\Authentication\Resources\UserResource;
use App\Modules\Authentication\Services\PasswordResetService;
use App\Modules\Authentication\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

// CRUD de usuarios + cambio de contraseña en primer ingreso.
class UserController extends Controller
{
    public function __construct(
        private readonly UserService $users,
        private readonly PasswordResetService $passwords,
    ) {}

    #[OA\Post(
        path: '/api/auth/change-password',
        tags: ['Password'],
        summary: 'Cambiar contraseña (primer ingreso o propia)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['current_password', 'new_password', 'new_password_confirmation'],
            properties: [
                new OA\Property(property: 'current_password', type: 'string'),
                new OA\Property(property: 'new_password', type: 'string'),
                new OA\Property(property: 'new_password_confirmation', type: 'string'),
            ]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Contraseña actualizada'),
            new OA\Response(response: 422, description: 'Contraseña actual incorrecta'),
        ]
    )]
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->passwords->changePassword(
            $request->user(),
            $request->validated('current_password'),
            $request->validated('new_password'),
        );

        return response()->json(['message' => 'Contraseña actualizada.']);
    }

    #[OA\Get(
        path: '/api/auth/users',
        tags: ['Users'],
        summary: 'Listar usuarios',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'role', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista de usuarios'),
            new OA\Response(response: 403, description: 'Sin permiso'),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $list = $this->users->list(
            search: $request->query('search'),
            role: $request->query('role'),
            perPage: (int) $request->query('per_page', 15),
        );

        return UserResource::collection($list);
    }

    #[OA\Post(
        path: '/api/auth/users',
        tags: ['Users'],
        summary: 'Crear usuario',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['email', 'password', 'role'],
            properties: [
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'password', type: 'string'),
                new OA\Property(property: 'role', type: 'string', example: 'DOCENTE'),
            ]
        )),
        responses: [new OA\Response(response: 201, description: 'Usuario creado', content: new OA\JsonContent(ref: '#/components/schemas/User'))]
    )]
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->users->create(CreateUserDTO::fromArray($request->validated()));

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/auth/users/{user}',
        tags: ['Users'],
        summary: 'Ver usuario',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Usuario', content: new OA\JsonContent(ref: '#/components/schemas/User'))]
    )]
    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    #[OA\Put(
        path: '/api/auth/users/{user}',
        tags: ['Users'],
        summary: 'Editar usuario',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'role', type: 'string'),
            ]
        )),
        responses: [new OA\Response(response: 200, description: 'Usuario actualizado', content: new OA\JsonContent(ref: '#/components/schemas/User'))]
    )]
    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        return new UserResource($this->users->update($user, UpdateUserDTO::fromArray($request->validated())));
    }

    #[OA\Delete(
        path: '/api/auth/users/{user}',
        tags: ['Users'],
        summary: 'Eliminar usuario',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Usuario eliminado')]
    )]
    public function destroy(User $user): JsonResponse
    {
        $this->users->delete($user);

        return response()->json(['message' => 'Usuario eliminado.']);
    }
}
