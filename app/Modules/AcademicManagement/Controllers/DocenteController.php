<?php

namespace App\Modules\AcademicManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AcademicManagement\DTOs\CreateDocenteDTO;
use App\Modules\AcademicManagement\DTOs\UpdateDocenteDTO;
use App\Modules\AcademicManagement\Models\Docente;
use App\Modules\AcademicManagement\Requests\StoreDocenteRequest;
use App\Modules\AcademicManagement\Requests\UpdateDocenteRequest;
use App\Modules\AcademicManagement\Resources\DocenteResource;
use App\Modules\AcademicManagement\Services\DocenteCuentaService;
use App\Modules\AcademicManagement\Services\DocenteService;
use App\Modules\Authentication\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

// CRUD de docentes.
class DocenteController extends Controller
{
    public function __construct(
        private readonly DocenteService $docentes,
        private readonly DocenteCuentaService $cuentas,
    ) {}

    #[OA\Get(
        path: '/api/academic-management/docentes',
        tags: ['Docentes'],
        summary: 'Listar docentes',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Lista de docentes')]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        return DocenteResource::collection(
            $this->docentes->list($request->query('search'), (int) $request->query('per_page', 15))
        );
    }

    #[OA\Post(
        path: '/api/academic-management/docentes',
        tags: ['Docentes'],
        summary: 'Crear docente',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['ci', 'nombres', 'apellidos', 'email'],
            properties: [
                new OA\Property(property: 'ci', type: 'string', example: '1234567'),
                new OA\Property(property: 'nombres', type: 'string', example: 'Juan Carlos'),
                new OA\Property(property: 'apellidos', type: 'string', example: 'Pérez López'),
                new OA\Property(property: 'email', type: 'string', example: 'jperez@cup-ficct.local'),
                new OA\Property(property: 'telefono', type: 'string', example: '70000000'),
                new OA\Property(property: 'profesion', type: 'string', example: 'Ing. Matemático'),
            ]
        )),
        responses: [new OA\Response(response: 201, description: 'Docente creado', content: new OA\JsonContent(ref: '#/components/schemas/Docente'))]
    )]
    public function store(StoreDocenteRequest $request): JsonResponse
    {
        $docente = $this->docentes->create(CreateDocenteDTO::fromArray($request->validated()));

        return (new DocenteResource($docente))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/academic-management/docentes/{docente}',
        tags: ['Docentes'],
        summary: 'Ver docente',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'docente', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Docente', content: new OA\JsonContent(ref: '#/components/schemas/Docente'))]
    )]
    public function show(Docente $docente): DocenteResource
    {
        return new DocenteResource($docente);
    }

    #[OA\Put(
        path: '/api/academic-management/docentes/{docente}',
        tags: ['Docentes'],
        summary: 'Editar docente',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'docente', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'ci', type: 'string'),
                new OA\Property(property: 'nombres', type: 'string'),
                new OA\Property(property: 'apellidos', type: 'string'),
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'telefono', type: 'string'),
                new OA\Property(property: 'profesion', type: 'string'),
            ]
        )),
        responses: [new OA\Response(response: 200, description: 'Docente actualizado', content: new OA\JsonContent(ref: '#/components/schemas/Docente'))]
    )]
    public function update(UpdateDocenteRequest $request, Docente $docente): DocenteResource
    {
        return new DocenteResource($this->docentes->update($docente, UpdateDocenteDTO::fromArray($request->validated())));
    }

    #[OA\Delete(
        path: '/api/academic-management/docentes/{docente}',
        tags: ['Docentes'],
        summary: 'Eliminar docente',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'docente', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Docente eliminado')]
    )]
    public function destroy(Docente $docente): JsonResponse
    {
        $this->docentes->delete($docente);

        return response()->json(['message' => 'Docente eliminado.']);
    }

    #[OA\Post(
        path: '/api/academic-management/docentes/{docente}/usuario',
        tags: ['Docentes'],
        summary: 'Crear y vincular la cuenta del docente',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'docente', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 201, description: 'Cuenta creada (devuelve contraseña temporal)'),
            new OA\Response(response: 422, description: 'Ya tiene cuenta o correo en uso'),
        ]
    )]
    public function createAccount(Docente $docente): JsonResponse
    {
        $result = $this->cuentas->create($docente);

        return response()->json([
            'user' => new UserResource($result['user']),
            'temporary_password' => $result['temporary_password'],
        ], 201);
    }

    #[OA\Delete(
        path: '/api/academic-management/docentes/{docente}/usuario',
        tags: ['Docentes'],
        summary: 'Eliminar la cuenta del docente',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'docente', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Cuenta eliminada')]
    )]
    public function deleteAccount(Docente $docente): JsonResponse
    {
        $this->cuentas->delete($docente);

        return response()->json(['message' => 'Cuenta del docente eliminada.']);
    }
}
