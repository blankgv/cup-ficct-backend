<?php

namespace App\Modules\ApplicantAdmission\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ApplicantAdmission\DTOs\CreatePostulanteDTO;
use App\Modules\ApplicantAdmission\DTOs\UpdatePostulanteDTO;
use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\ApplicantAdmission\Requests\BatchPostulantesRequest;
use App\Modules\ApplicantAdmission\Requests\StorePostulanteRequest;
use App\Modules\ApplicantAdmission\Requests\UpdatePostulanteRequest;
use App\Modules\ApplicantAdmission\Requests\UploadTituloRequest;
use App\Modules\ApplicantAdmission\Resources\PostulanteResource;
use App\Modules\ApplicantAdmission\Services\BatchPostulanteService;
use App\Modules\ApplicantAdmission\Services\PostulanteService;
use App\Modules\ApplicantAdmission\Services\TituloService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

// CRUD de postulantes + título de bachiller.
class PostulanteController extends Controller
{
    public function __construct(
        private readonly PostulanteService $postulantes,
        private readonly TituloService $titulos,
        private readonly BatchPostulanteService $batch,
    ) {}

    #[OA\Post(
        path: '/api/applicant-admission/postulantes/lote',
        tags: ['Postulantes'],
        summary: 'Carga masiva de postulantes (CSV). Crea su usuario (rol POSTULANTE)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['archivo'],
                properties: [new OA\Property(property: 'archivo', type: 'string', format: 'binary', description: 'CSV con encabezados: documento,nombres,apellidos,email,fecha_nacimiento,colegio,ciudad,telefono')]
            )
        )),
        responses: [new OA\Response(response: 200, description: 'Resumen {creados, omitidos, errores}')]
    )]
    public function importLote(BatchPostulantesRequest $request): JsonResponse
    {
        return response()->json($this->batch->import(
            $request->file('archivo'),
            $request->file('titulos'),
        ));
    }

    #[OA\Get(
        path: '/api/applicant-admission/postulantes',
        tags: ['Postulantes'],
        summary: 'Listar postulantes',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Lista de postulantes')]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        return PostulanteResource::collection(
            $this->postulantes->list($request->query('search'), (int) $request->query('per_page', 15))
        );
    }

    #[OA\Post(
        path: '/api/applicant-admission/postulantes',
        tags: ['Postulantes'],
        summary: 'Registrar postulante',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['documento', 'nombres', 'apellidos', 'email', 'fecha_nacimiento', 'colegio'],
            properties: [
                new OA\Property(property: 'documento', type: 'string', example: '9876543'),
                new OA\Property(property: 'nombres', type: 'string', example: 'María José'),
                new OA\Property(property: 'apellidos', type: 'string', example: 'Quispe Vargas'),
                new OA\Property(property: 'email', type: 'string', example: 'mquispe@example.com'),
                new OA\Property(property: 'telefono', type: 'string', example: '70000000'),
                new OA\Property(property: 'fecha_nacimiento', type: 'string', format: 'date', example: '2007-03-15'),
                new OA\Property(property: 'colegio', type: 'string', example: 'Colegio Nacional'),
            ]
        )),
        responses: [new OA\Response(response: 201, description: 'Postulante creado', content: new OA\JsonContent(ref: '#/components/schemas/Postulante'))]
    )]
    public function store(StorePostulanteRequest $request): JsonResponse
    {
        $postulante = $this->postulantes->create(CreatePostulanteDTO::fromArray($request->validated()));

        return (new PostulanteResource($postulante))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/applicant-admission/postulantes/{postulante}',
        tags: ['Postulantes'],
        summary: 'Ver postulante',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'postulante', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Postulante', content: new OA\JsonContent(ref: '#/components/schemas/Postulante'))]
    )]
    public function show(Postulante $postulante): PostulanteResource
    {
        return new PostulanteResource($postulante);
    }

    #[OA\Put(
        path: '/api/applicant-admission/postulantes/{postulante}',
        tags: ['Postulantes'],
        summary: 'Editar postulante',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'postulante', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'documento', type: 'string'),
                new OA\Property(property: 'nombres', type: 'string'),
                new OA\Property(property: 'apellidos', type: 'string'),
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'telefono', type: 'string'),
                new OA\Property(property: 'fecha_nacimiento', type: 'string', format: 'date'),
                new OA\Property(property: 'colegio', type: 'string'),
            ]
        )),
        responses: [new OA\Response(response: 200, description: 'Postulante actualizado', content: new OA\JsonContent(ref: '#/components/schemas/Postulante'))]
    )]
    public function update(UpdatePostulanteRequest $request, Postulante $postulante): PostulanteResource
    {
        return new PostulanteResource($this->postulantes->update($postulante, UpdatePostulanteDTO::fromArray($request->validated())));
    }

    #[OA\Delete(
        path: '/api/applicant-admission/postulantes/{postulante}',
        tags: ['Postulantes'],
        summary: 'Eliminar postulante',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'postulante', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Postulante eliminado')]
    )]
    public function destroy(Postulante $postulante): JsonResponse
    {
        $this->postulantes->delete($postulante);

        return response()->json(['message' => 'Postulante eliminado.']);
    }

    #[OA\Post(
        path: '/api/applicant-admission/postulantes/{postulante}/titulo',
        tags: ['Postulantes'],
        summary: 'Subir título de bachiller (PDF/imagen)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'postulante', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['titulo'],
                properties: [new OA\Property(property: 'titulo', type: 'string', format: 'binary')]
            )
        )),
        responses: [new OA\Response(response: 200, description: 'Título subido', content: new OA\JsonContent(ref: '#/components/schemas/Postulante'))]
    )]
    public function uploadTitulo(UploadTituloRequest $request, Postulante $postulante): PostulanteResource
    {
        return new PostulanteResource($this->titulos->upload($postulante, $request->file('titulo')));
    }

    #[OA\Get(
        path: '/api/applicant-admission/postulantes/{postulante}/titulo',
        tags: ['Postulantes'],
        summary: 'Descargar título (redirige a URL firmada)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'postulante', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 302, description: 'Redirige al archivo'),
            new OA\Response(response: 404, description: 'Sin título'),
        ]
    )]
    public function downloadTitulo(Postulante $postulante): RedirectResponse
    {
        $url = $this->titulos->downloadUrl($postulante);

        abort_if($url === null, 404, 'El postulante no tiene título cargado.');

        return redirect()->away($url);
    }
}
