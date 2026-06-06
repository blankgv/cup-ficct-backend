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
            $sheet->fromArray($report['rows'], null, 'A2');
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

    private function nombreArchivo(string $titulo, string $ext): string
    {
        return Str::slug($titulo).'-'.now()->format('Ymd-His').'.'.$ext;
    }
}
