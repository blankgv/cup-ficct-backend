<?php

namespace App\Modules\Reports\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Exporta un reporte (titulo, headers, rows) a Excel o PDF.
class ReportExporter
{
    /**
     * @param array{titulo:string, headers:list<string>, rows:list<list<mixed>>} $report
     */
    public function excel(array $report): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([$report['headers']], null, 'A1');
        if ($report['rows'] !== []) {
            $rows = array_map(
                fn (array $row) => array_map([$this, 'normalizar'], $row),
                $report['rows'],
            );
            $sheet->fromArray($rows, null, 'A2');
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            fn () => $writer->save('php://output'),
            $this->nombreArchivo($report['titulo'], 'xlsx'),
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }

    /**
     * @param array{titulo:string, headers:list<string>, rows:list<list<mixed>>} $report
     */
    public function pdf(array $report): \Illuminate\Http\Response
    {
        return Pdf::loadView('reports.tabla', $report)
            ->download($this->nombreArchivo($report['titulo'], 'pdf'));
    }

    // Convierte cualquier valor a un escalar que PhpSpreadsheet pueda escribir.
    private function normalizar(mixed $valor): mixed
    {
        if ($valor === null || is_scalar($valor)) {
            return $valor;
        }
        if ($valor instanceof \BackedEnum) {
            return $valor->value;
        }
        if ($valor instanceof \Stringable || (is_object($valor) && method_exists($valor, '__toString'))) {
            return (string) $valor;
        }

        return json_encode($valor);
    }

    private function nombreArchivo(string $titulo, string $ext): string
    {
        return Str::slug($titulo).'-'.now()->format('Ymd-His').'.'.$ext;
    }
}
