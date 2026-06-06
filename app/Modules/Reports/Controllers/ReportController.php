<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\Reports\Services\ReportExporter;
use App\Modules\Reports\Services\ReportService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

// Reportes: consulta (JSON) y exportación (Excel/PDF).
class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reports,
        private readonly ReportExporter $exporter,
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
        $gestion = $request->query('gestion');
        abort_if($gestion === null, 422, 'El parámetro gestion es obligatorio.');

        $report = $this->reports->estudiantesPorGrupo(
            (string) $gestion,
            $request->query('grupo_id') !== null ? (int) $request->query('grupo_id') : null,
            $request->query('nombre'),
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
        return $this->responder($request, $this->reports->postulantesPorConvocatoria($convocatoria));
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
        return $this->responder($request, $this->reports->recaudacion($convocatoria));
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
        return $this->responder($request, $this->reports->resultados($convocatoria));
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
        return $this->responder($request, $this->reports->asignacionCarreras($convocatoria));
    }

    /**
     * Devuelve el reporte en el formato pedido. Excel/PDF requieren permiso report.export.
     *
     * @param array{titulo:string, headers:list<string>, rows:list<list<mixed>>} $report
     */
    private function responder(Request $request, array $report): Response
    {
        $format = (string) $request->query('format', 'json');

        if ($format === 'json') {
            return response()->json($report);
        }

        abort_unless(in_array($format, ['excel', 'pdf'], true), 422, 'Formato no soportado.');
        abort_unless($request->user()->hasPermission('report.export'), 403, 'Requiere permiso de exportación.');

        return $format === 'excel' ? $this->exporter->excel($report) : $this->exporter->pdf($report);
    }
}
