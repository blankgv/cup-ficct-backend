<?php

namespace App\Modules\AcademicManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AcademicManagement\Models\Grupo;
use App\Modules\AcademicManagement\Requests\AssignDocenteRequest;
use App\Modules\AcademicManagement\Requests\AttachMateriaRequest;
use App\Modules\AcademicManagement\Requests\SyncMateriasRequest;
use App\Modules\AcademicManagement\Resources\GrupoMateriaResource;
use App\Modules\AcademicManagement\Resources\MateriaResource;
use App\Modules\AcademicManagement\Services\GrupoMateriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

// Materias de un grupo (muchos a muchos).
class GrupoMateriaController extends Controller
{
    public function __construct(private readonly GrupoMateriaService $service) {}

    #[OA\Get(
        path: '/api/academic-management/grupos/{grupo}/materias',
        tags: ['Grupos'],
        summary: 'Listar materias del grupo',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'grupo', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Materias del grupo')]
    )]
    public function index(Grupo $grupo): AnonymousResourceCollection
    {
        return MateriaResource::collection($this->service->list($grupo));
    }

    #[OA\Put(
        path: '/api/academic-management/grupos/{grupo}/materias',
        tags: ['Grupos'],
        summary: 'Reemplazar materias del grupo',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'grupo', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['siglas'],
            properties: [new OA\Property(property: 'siglas', type: 'array', items: new OA\Items(type: 'string'), example: ['MAT', 'FIS', 'ING', 'COM'])]
        )),
        responses: [new OA\Response(response: 200, description: 'Materias sincronizadas')]
    )]
    public function sync(SyncMateriasRequest $request, Grupo $grupo): AnonymousResourceCollection
    {
        return MateriaResource::collection($this->service->sync($grupo, $request->validated('siglas')));
    }

    #[OA\Post(
        path: '/api/academic-management/grupos/{grupo}/materias',
        tags: ['Grupos'],
        summary: 'Agregar una materia al grupo',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'grupo', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['sigla'],
            properties: [new OA\Property(property: 'sigla', type: 'string', example: 'MAT')]
        )),
        responses: [new OA\Response(response: 200, description: 'Materia agregada')]
    )]
    public function attach(AttachMateriaRequest $request, Grupo $grupo): AnonymousResourceCollection
    {
        return MateriaResource::collection($this->service->attach($grupo, $request->validated('sigla')));
    }

    #[OA\Delete(
        path: '/api/academic-management/grupos/{grupo}/materias/{sigla}',
        tags: ['Grupos'],
        summary: 'Quitar una materia del grupo',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'grupo', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'sigla', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [new OA\Response(response: 200, description: 'Materia quitada')]
    )]
    public function detach(Grupo $grupo, string $sigla): JsonResponse
    {
        $this->service->detach($grupo, $sigla);

        return response()->json(['message' => 'Materia quitada del grupo.']);
    }

    #[OA\Put(
        path: '/api/academic-management/grupos/{grupo}/materias/{sigla}/docente',
        tags: ['Grupos'],
        summary: 'Asignar docente al grupo-materia',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'grupo', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'sigla', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['ci'],
            properties: [new OA\Property(property: 'ci', type: 'string', example: '1234567')]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Docente asignado'),
            new OA\Response(response: 404, description: 'El grupo no cursa esa materia'),
        ]
    )]
    public function assignDocente(AssignDocenteRequest $request, Grupo $grupo, string $sigla): GrupoMateriaResource
    {
        return new GrupoMateriaResource($this->service->assignDocente($grupo, $sigla, $request->validated('ci')));
    }

    #[OA\Delete(
        path: '/api/academic-management/grupos/{grupo}/materias/{sigla}/docente',
        tags: ['Grupos'],
        summary: 'Quitar el docente del grupo-materia',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'grupo', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'sigla', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [new OA\Response(response: 200, description: 'Docente quitado')]
    )]
    public function removeDocente(Grupo $grupo, string $sigla): GrupoMateriaResource
    {
        return new GrupoMateriaResource($this->service->removeDocente($grupo, $sigla));
    }
}
