<?php

namespace App\Modules\Reports\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

// Interpreta el texto (transcrito por el front) y decide reporte + filtros con OpenAI.
class OpenAiReportInterpreter
{
    /**
     * @return array{reporte:string, filtros:array<string, mixed>}
     */
    public function interpretar(string $texto): array
    {
        $key = (string) config('services.openai.api_key');
        $model = (string) config('services.openai.model');
        $base = (string) config('services.openai.base_url');

        if ($key === '' || $model === '' || $base === '') {
            throw ValidationException::withMessages([
                'openai' => 'OpenAI no está configurado (OPENAI_API_KEY / OPENAI_MODEL / OPENAI_BASE_URL).',
            ]);
        }

        $respuesta = Http::withToken($key)
            ->post(rtrim($base, '/').'/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $this->instruccion()],
                    ['role' => 'user', 'content' => $texto],
                ],
                'tools' => [$this->herramienta()],
                'tool_choice' => ['type' => 'function', 'function' => ['name' => 'generar_reporte']],
            ])
            ->throw();

        $argumentos = data_get($respuesta->json(), 'choices.0.message.tool_calls.0.function.arguments');

        if ($argumentos === null) {
            throw ValidationException::withMessages(['texto' => 'No se pudo interpretar la solicitud de reporte.']);
        }

        $datos = json_decode((string) $argumentos, true) ?: [];

        return [
            'reporte' => (string) ($datos['reporte'] ?? ''),
            'filtros' => array_filter(
                (array) ($datos['filtros'] ?? []),
                fn ($v) => $v !== null && $v !== '',
            ),
        ];
    }

    // Instrucción del sistema: qué reportes existen y cómo mapear filtros.
    private function instruccion(): string
    {
        return <<<'TXT'
        Eres un asistente que convierte pedidos en lenguaje natural (en español) a un reporte del sistema CUP-FICCT.
        Siempre debes llamar a la función generar_reporte eligiendo UN reporte y rellenando solo los filtros que el usuario mencione.
        Reportes disponibles:
        - estudiantes_por_grupo: estudiantes inscritos por grupo. Requiere gestion (año). Filtros: grupo_id, turno, nombre, carrera.
        - postulantes: postulantes de una convocatoria. Requiere convocatoria (id o nombre/gestion). Filtros: estado, carrera, turno_preferencia, nombre.
        - recaudacion: pagos de una convocatoria. Requiere convocatoria. Filtros: estado, metodo, desde, hasta (fechas YYYY-MM-DD).
        - resultados: notas/promedios de una convocatoria. Requiere convocatoria. Filtros: estado (APROBADO/REPROBADO), nota_min, nota_max, grupo_id.
        - asignacion_carreras: cupos vs asignados de una convocatoria. Requiere convocatoria. Filtros: carrera.
        Valores: turno y turno_preferencia = MANANA, TARDE o NOCHE. metodo = EFECTIVO, TRANSFERENCIA, QR o TARJETA.
        No inventes filtros que el usuario no haya pedido.
        TXT;
    }

    /**
     * @return array<string, mixed>
     */
    private function herramienta(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'generar_reporte',
                'description' => 'Genera un reporte del sistema con los filtros indicados.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'reporte' => [
                            'type' => 'string',
                            'enum' => ['estudiantes_por_grupo', 'postulantes', 'recaudacion', 'resultados', 'asignacion_carreras'],
                            'description' => 'El reporte a generar.',
                        ],
                        'filtros' => [
                            'type' => 'object',
                            'properties' => [
                                'gestion' => ['type' => 'string', 'description' => 'Año/gestión, ej. 2026.'],
                                'convocatoria' => ['type' => 'string', 'description' => 'Nombre o gestión de la convocatoria.'],
                                'convocatoria_id' => ['type' => 'integer', 'description' => 'Id de la convocatoria si se conoce.'],
                                'grupo_id' => ['type' => 'integer'],
                                'turno' => ['type' => 'string', 'enum' => ['MANANA', 'TARDE', 'NOCHE']],
                                'turno_preferencia' => ['type' => 'string', 'enum' => ['MANANA', 'TARDE', 'NOCHE']],
                                'nombre' => ['type' => 'string', 'description' => 'Nombre o apellido del estudiante.'],
                                'carrera' => ['type' => 'string', 'description' => 'Código de carrera, ej. 187-09.'],
                                'estado' => ['type' => 'string'],
                                'metodo' => ['type' => 'string', 'enum' => ['EFECTIVO', 'TRANSFERENCIA', 'QR', 'TARJETA']],
                                'desde' => ['type' => 'string', 'description' => 'Fecha desde YYYY-MM-DD.'],
                                'hasta' => ['type' => 'string', 'description' => 'Fecha hasta YYYY-MM-DD.'],
                                'nota_min' => ['type' => 'number'],
                                'nota_max' => ['type' => 'number'],
                            ],
                        ],
                    ],
                    'required' => ['reporte'],
                ],
            ],
        ];
    }
}
