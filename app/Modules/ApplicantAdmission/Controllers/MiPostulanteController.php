<?php

namespace App\Modules\ApplicantAdmission\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ApplicantAdmission\DTOs\UpdatePostulanteDTO;
use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\ApplicantAdmission\Requests\CompletarPerfilRequest;
use App\Modules\ApplicantAdmission\Requests\UploadTituloRequest;
use App\Modules\ApplicantAdmission\Resources\PostulanteResource;
use App\Modules\ApplicantAdmission\Services\PostulanteService;
use App\Modules\ApplicantAdmission\Services\TituloService;
use App\Modules\Payments\Models\Pago;
use App\Modules\Payments\Resources\PagoResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

// Autogestión del propio postulante (el dueño, sin permiso de staff).
class MiPostulanteController extends Controller
{
    public function __construct(
        private readonly PostulanteService $postulantes,
        private readonly TituloService $titulos,
    ) {}

    // Resuelve el postulante del usuario autenticado.
    private function postulante(): Postulante
    {
        return Postulante::query()
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    #[OA\Get(
        path: '/api/applicant-admission/mi-postulante',
        tags: ['ApplicantAdmission'],
        summary: 'Mis datos de postulante',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Postulante', content: new OA\JsonContent(ref: '#/components/schemas/Postulante')),
            new OA\Response(response: 404, description: 'El usuario no es postulante'),
        ]
    )]
    public function show(): PostulanteResource
    {
        return new PostulanteResource($this->postulante());
    }

    #[OA\Put(
        path: '/api/applicant-admission/mi-postulante',
        tags: ['ApplicantAdmission'],
        summary: 'Completar/editar mis datos de postulante',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Actualizado', content: new OA\JsonContent(ref: '#/components/schemas/Postulante'))]
    )]
    public function update(CompletarPerfilRequest $request): PostulanteResource
    {
        $postulante = $this->postulantes->update(
            $this->postulante(),
            UpdatePostulanteDTO::fromArray($request->validated()),
        );

        return new PostulanteResource($postulante);
    }

    #[OA\Post(
        path: '/api/applicant-admission/mi-postulante/titulo',
        tags: ['ApplicantAdmission'],
        summary: 'Subir mi título de bachiller',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(required: ['titulo'], properties: [new OA\Property(property: 'titulo', type: 'string', format: 'binary')])
        )),
        responses: [new OA\Response(response: 200, description: 'Título subido', content: new OA\JsonContent(ref: '#/components/schemas/Postulante'))]
    )]
    public function uploadTitulo(UploadTituloRequest $request): PostulanteResource
    {
        return new PostulanteResource(
            $this->titulos->upload($this->postulante(), $request->file('titulo')),
        );
    }

    #[OA\Get(
        path: '/api/applicant-admission/mi-postulante/titulo',
        tags: ['ApplicantAdmission'],
        summary: 'Descargar mi título (302 a URL firmada)',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 302, description: 'Redirige al archivo'),
            new OA\Response(response: 404, description: 'Sin título'),
        ]
    )]
    public function downloadTitulo(): RedirectResponse
    {
        $url = $this->titulos->downloadUrl($this->postulante());

        abort_if($url === null, 404, 'No tenés título cargado.');

        return redirect()->away($url);
    }

    #[OA\Get(
        path: '/api/applicant-admission/mi-postulante/pagos',
        tags: ['ApplicantAdmission'],
        summary: 'Mis pagos (cobros de inscripción y otros)',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Lista de pagos del postulante')]
    )]
    public function pagos(): AnonymousResourceCollection
    {
        return PagoResource::collection(
            Pago::query()
                ->where('postulante_documento', $this->postulante()->documento)
                ->latest()
                ->get(),
        );
    }
}
