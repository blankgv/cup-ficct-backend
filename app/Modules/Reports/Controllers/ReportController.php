<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\Reports\Services\OpenAiReportInterpreter;
use App\Modules\Reports\Services\ReportExporter;
use App\Modules\Reports\Services\ReportService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

// Reportes: consulta (JSON), exportación (Excel/PDF) y consulta por voz (texto + IA).
class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reports,
        private readonly ReportExporter $exporter,
        private readonly OpenAiReportInterpreter $interpreter,
    ) {}

    #[OA\Get(
        path: '/api/reports/estudiantes-por-grupo',
        tags: ['Reportes'],
        summary: 'Estudiantes por grupo de una gestión (filtro por nombre). format=json|excel|pdf',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'gestion', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'grupo_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'nombre', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'format', in: 'query', schema: new OA\Schema(type: 'string', enum: ['json', 'excel', 'pdf'])),
        ],
        responses: [new OA\Response(response: 200, description: 'Reporte')]
    )]
    public function estudiantesPorGrupo(Request $request): Response
    {
        abort_if($request->query('gestion') === null, 422, 'El parámetro gestion es obligatorio.');

        $report = $this->reports->estudiantesPorGrupo(
            $request->only(['gestion', 'grupo_id', 'turno', 'nombre', 'carrera'])
        );

        return $this->responder($request, $report);
    }

    #[OA\Get(
        path: '/api/reports/convocatorias/{convocatoria}/postulantes',
        tags: ['Reportes'],
        summary: 'Postulantes por convocatoria. format=json|excel|pdf',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'convocatoria', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'format', in: 'query', schema: new OA\Schema(type: 'string', enum: ['json', 'excel', 'pdf'])),
        ],
        responses: [new OA\Response(response: 200, description: 'Reporte')]
    )]
    public function postulantes(Request $request, Convocatoria $convocatoria): Response
    {
        return $this->responder($request, $this->reports->postulantesPorConvocatoria(
            $convocatoria, $request->only(['estado', 'carrera', 'turno_preferencia', 'nombre'])
        ));
    }

    #[OA\Get(
        path: '/api/reports/convocatorias/{convocatoria}/recaudacion',
        tags: ['Reportes'],
        summary: 'Recaudación de pagos por convocatoria. format=json|excel|pdf',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'convocatoria', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'format', in: 'query', schema: new OA\Schema(type: 'string', enum: ['json', 'excel', 'pdf'])),
        ],
        responses: [new OA\Response(response: 200, description: 'Reporte')]
    )]
    public function recaudacion(Request $request, Convocatoria $convocatoria): Response
    {
        return $this->responder($request, $this->reports->recaudacion(
            $convocatoria, $request->only(['estado', 'metodo', 'desde', 'hasta'])
        ));
    }

    #[OA\Get(
        path: '/api/reports/convocatorias/{convocatoria}/resultados',
        tags: ['Reportes'],
        summary: 'Resultados (promedio + estado) por convocatoria. format=json|excel|pdf',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'convocatoria', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'format', in: 'query', schema: new OA\Schema(type: 'string', enum: ['json', 'excel', 'pdf'])),
        ],
        responses: [new OA\Response(response: 200, description: 'Reporte')]
    )]
    public function resultados(Request $request, Convocatoria $convocatoria): Response
    {
        return $this->responder($request, $this->reports->resultados(
            $convocatoria, $request->only(['estado', 'nota_min', 'nota_max', 'grupo_id'])
        ));
    }

    #[OA\Get(
        path: '/api/reports/convocatorias/{convocatoria}/asignacion-carreras',
        tags: ['Reportes'],
        summary: 'Asignación de carreras vs cupos. format=json|excel|pdf',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'convocatoria', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'format', in: 'query', schema: new OA\Schema(type: 'string', enum: ['json', 'excel', 'pdf'])),
        ],
        responses: [new OA\Response(response: 200, description: 'Reporte')]
    )]
    public function asignacionCarreras(Request $request, Convocatoria $convocatoria): Response
    {
        return $this->responder($request, $this->reports->asignacionCarreras(
            $convocatoria, $request->only(['carrera'])
        ));
    }

    #[OA\Post(
        path: '/api/reports/voz',
        tags: ['Reportes'],
        summary: 'Reporte por voz: el front manda el texto transcrito; la IA elige reporte + filtros. format=json|excel|pdf',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['texto'],
            properties: [
                new OA\Property(property: 'texto', type: 'string', example: 'Dame los estudiantes del turno mañana de la gestión 2026'),
                new OA\Property(property: 'format', type: 'string', enum: ['json', 'excel', 'pdf'], example: 'json'),
            ]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Reporte (con la interpretación de la IA en JSON)'),
            new OA\Response(response: 422, description: 'No se pudo interpretar o faltan datos'),
        ]
    )]
    public function voz(Request $request): Response
    {
        $request->validate(['texto' => ['required', 'string']]);

        $interpretacion = $this->interpreter->interpretar($request->input('texto'));
        $report = $this->reports->porClave($interpretacion['reporte'], $interpretacion['filtros']);

        return $this->responder($request, $report, $interpretacion);
    }

    /**
     * Devuelve el reporte en el formato pedido. Excel/PDF requieren permiso report.export.
     *
     * @param array{titulo:string, headers:list<string>, rows:list<list<mixed>>} $report
     * @param array<string, mixed>|null $interpretacion
     */
    private function responder(Request $request, array $report, ?array $interpretacion = null): Response
    {
        $format = (string) $request->input('format', 'json');

        if ($format === 'json') {
            return response()->json($interpretacion === null ? $report : ['interpretacion' => $interpretacion] + $report);
        }

        abort_unless(in_array($format, ['excel', 'pdf'], true), 422, 'Formato no soportado.');
        abort_unless($request->user()->hasPermission('report.export'), 403, 'Requiere permiso de exportación.');

        return $format === 'excel' ? $this->exporter->excel($report) : $this->exporter->pdf($report);
    }
}
