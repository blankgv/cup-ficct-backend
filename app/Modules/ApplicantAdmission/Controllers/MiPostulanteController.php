<?php

namespace App\Modules\ApplicantAdmission\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AcademicManagement\Models\Carrera;
use App\Modules\ApplicantAdmission\DTOs\CreatePostulacionDTO;
use App\Modules\ApplicantAdmission\DTOs\UpdatePostulanteDTO;
use App\Modules\ApplicantAdmission\Enums\EstadoConvocatoria;
use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\ApplicantAdmission\Models\Inscripcion;
use App\Modules\ApplicantAdmission\Models\Postulacion;
use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\ApplicantAdmission\Requests\CompletarPerfilRequest;
use App\Modules\ApplicantAdmission\Requests\CrearMiPostulacionRequest;
use App\Modules\ApplicantAdmission\Requests\UploadTituloRequest;
use App\Modules\ApplicantAdmission\Resources\PostulanteResource;
use App\Modules\ApplicantAdmission\Services\PostulacionService;
use App\Modules\ApplicantAdmission\Services\PostulanteService;
use App\Modules\ApplicantAdmission\Services\TituloService;
use App\Modules\Evaluation\Services\AsistenciaService;
use App\Modules\Evaluation\Services\NotaService;
use App\Modules\Payments\Models\Pago;
use App\Modules\Payments\Resources\PagoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

// Autogestión del propio postulante (el dueño, sin permiso de staff).
class MiPostulanteController extends Controller
{
    public function __construct(
        private readonly PostulanteService $postulantes,
        private readonly TituloService $titulos,
        private readonly NotaService $notas,
        private readonly AsistenciaService $asistencias,
        private readonly PostulacionService $postulacionesSrv,
    ) {}

    // Convocatoria de la inscripción más reciente del postulante (o 404).
    private function convocatoriaInscrito(Postulante $postulante): int
    {
        $convId = Inscripcion::query()
            ->where('postulante_documento', $postulante->documento)
            ->latest('fecha_asignacion')
            ->value('convocatoria_id');

        abort_if($convId === null, 404, 'Todavía no estás inscrito en ningún grupo.');

        return (int) $convId;
    }

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

    #[OA\Get(
        path: '/api/applicant-admission/mi-postulante/boletin',
        tags: ['ApplicantAdmission'],
        summary: 'Mi boletín (notas, promedio y estado) de mi inscripción',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Boletín del postulante'),
            new OA\Response(response: 404, description: 'No inscrito'),
        ]
    )]
    public function boletin(): JsonResponse
    {
        $postulante = $this->postulante();

        return response()->json(
            $this->notas->boletin($postulante, $this->convocatoriaInscrito($postulante)),
        );
    }

    #[OA\Get(
        path: '/api/applicant-admission/mi-postulante/asistencia',
        tags: ['ApplicantAdmission'],
        summary: 'Mi asistencia (% por materia, global y habilitación)',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Reporte de asistencia del postulante'),
            new OA\Response(response: 404, description: 'No inscrito'),
        ]
    )]
    public function asistencia(): JsonResponse
    {
        $postulante = $this->postulante();

        return response()->json(
            $this->asistencias->reporte($postulante, $this->convocatoriaInscrito($postulante)),
        );
    }

    #[OA\Get(
        path: '/api/applicant-admission/mi-postulante/convocatorias',
        tags: ['ApplicantAdmission'],
        summary: 'Convocatorias abiertas y sus carreras (para postularme)',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Convocatorias abiertas')]
    )]
    public function convocatoriasAbiertas(): JsonResponse
    {
        $data = Convocatoria::query()
            ->where('estado', EstadoConvocatoria::ABIERTA->value)
            ->with('carreras')
            ->get()
            ->map(fn (Convocatoria $c) => [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'gestion' => $c->gestion,
                'carreras' => $c->carreras->map(fn ($ca) => [
                    'codigo' => $ca->codigo,
                    'nombre' => $ca->nombre,
                ])->values(),
            ]);

        return response()->json($data);
    }

    #[OA\Get(
        path: '/api/applicant-admission/mi-postulante/postulaciones',
        tags: ['ApplicantAdmission'],
        summary: 'Mis postulaciones (estado, carreras, turno)',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Mis postulaciones')]
    )]
    public function postulaciones(): JsonResponse
    {
        $nombres = Carrera::query()->pluck('nombre', 'codigo');

        $data = Postulacion::query()
            ->where('postulante_documento', $this->postulante()->documento)
            ->with('convocatoria')
            ->latest()
            ->get()
            ->map(fn (Postulacion $p) => [
                'id' => $p->id,
                'convocatoria_id' => $p->convocatoria_id,
                'convocatoria' => $p->convocatoria?->nombre,
                'gestion' => $p->convocatoria?->gestion,
                'carrera_primera' => $p->carrera_primera_codigo,
                'carrera_primera_nombre' => (string) ($nombres[$p->carrera_primera_codigo] ?? ''),
                'carrera_segunda' => $p->carrera_segunda_codigo,
                'carrera_segunda_nombre' => (string) ($nombres[$p->carrera_segunda_codigo] ?? ''),
                'turno_preferencia' => $p->turno_preferencia instanceof \BackedEnum
                    ? $p->turno_preferencia->value
                    : $p->turno_preferencia,
                'estado' => $p->estado instanceof \BackedEnum ? $p->estado->value : $p->estado,
                'observacion' => $p->observacion,
            ]);

        return response()->json($data);
    }

    #[OA\Post(
        path: '/api/applicant-admission/mi-postulante/postulaciones',
        tags: ['ApplicantAdmission'],
        summary: 'Crear mi postulación (convocatoria, carreras 1ª/2ª, turno) → queda PENDIENTE',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Postulación creada (pendiente de verificación)'),
            new OA\Response(response: 422, description: 'Datos inválidos o ya postulado'),
        ]
    )]
    public function crearPostulacion(CrearMiPostulacionRequest $request): JsonResponse
    {
        $postulante = $this->postulante();
        $data = $request->validated();

        $convocatoria = Convocatoria::findOrFail((int) $data['convocatoria_id']);
        $ofrecidas = $convocatoria->carreras->pluck('codigo');

        foreach (['carrera_primera_codigo', 'carrera_segunda_codigo'] as $campo) {
            if (! $ofrecidas->contains($data[$campo])) {
                throw ValidationException::withMessages([
                    $campo => 'Esa carrera no está ofertada en la convocatoria.',
                ]);
            }
        }

        // Reusa la validación (convocatoria abierta + sin duplicado) y fija el turno.
        $postulacion = $this->postulacionesSrv->create(new CreatePostulacionDTO(
            postulanteDocumento: $postulante->documento,
            convocatoriaId: (int) $data['convocatoria_id'],
            carreraPrimera: (string) $data['carrera_primera_codigo'],
            carreraSegunda: (string) $data['carrera_segunda_codigo'],
        ));
        $this->postulacionesSrv->setTurnoPreferencia($postulacion, (string) $data['turno_preferencia']);

        return response()->json(['message' => 'Postulación registrada. Queda pendiente de verificación.'], 201);
    }
}
