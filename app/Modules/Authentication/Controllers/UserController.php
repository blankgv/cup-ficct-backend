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

// CRUD de usuarios + cambio de contraseña en primer ingreso.
class UserController extends Controller
{
    public function __construct(
        private readonly UserService $users,
        private readonly PasswordResetService $passwords,
    ) {}

    // POST /api/auth/change-password (usuario autenticado)
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->passwords->changePassword(
            $request->user(),
            $request->validated('current_password'),
            $request->validated('new_password'),
        );

        return response()->json(['message' => 'Contraseña actualizada.']);
    }

    // GET /api/auth/users
    public function index(Request $request): AnonymousResourceCollection
    {
        $list = $this->users->list(
            search: $request->query('search'),
            role: $request->query('role'),
            perPage: (int) $request->query('per_page', 15),
        );

        return UserResource::collection($list);
    }

    // POST /api/auth/users
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->users->create(CreateUserDTO::fromArray($request->validated()));

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    // GET /api/auth/users/{user}
    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    // PUT /api/auth/users/{user}
    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        return new UserResource($this->users->update($user, UpdateUserDTO::fromArray($request->validated())));
    }

    // DELETE /api/auth/users/{user}
    public function destroy(User $user): JsonResponse
    {
        $this->users->delete($user);

        return response()->json(['message' => 'Usuario eliminado.']);
    }
}
