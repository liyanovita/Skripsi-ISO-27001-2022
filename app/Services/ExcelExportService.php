<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelExportService
{
    /**
     * Build and stream a styled .xlsx file.
     *
     * @param  string   $filename   e.g. 'Audit_Trail_2026-06-28.xlsx'
     * @param  array    $headers    Column header labels  ['ID', 'Name', ...]
     * @param  array    $rows       2-D array of data rows
     * @param  string   $sheetTitle Worksheet tab name
     * @return StreamedResponse
     */
    public static function download(string $filename, array $headers, array $rows, string $sheetTitle = 'Sheet1'): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetTitle);

        // ── Header row ────────────────────────────────────────────────────────
        $sheet->fromArray([$headers], null, 'A1');

        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $headerRange = "A1:{$lastCol}1";

        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size'  => 10,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1E3A5F'],   // navy blue
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF2E5090'],
                ],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(20);

        // ── Data rows ─────────────────────────────────────────────────────────
        if (!empty($rows)) {
            $rIndex = 2;
            foreach ($rows as $row) {
                $cIndex = 1;
                foreach ($row as $val) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex);
                    $cell = $sheet->getCell($colLetter . $rIndex);
                    
                    if (is_string($val)) {
                        // Force text format to prevent Excel from interpreting decimal-like strings (e.g., "4.1") as numbers
                        $cell->setValueExplicit($val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    } else {
                        $cell->setValue($val);
                    }
                    $cIndex++;
                }
                $rIndex++;
            }
        }

        $totalRows = count($rows) + 1;
        if ($totalRows > 1) {
            $dataRange = "A2:{$lastCol}{$totalRows}";
            $sheet->getStyle($dataRange)->applyFromArray([
                'font' => ['size' => 10],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => 'FFD1D5DB'],
                    ],
                ],
            ]);

            // Alternating row shading (light grey every other row)
            for ($r = 2; $r <= $totalRows; $r++) {
                if ($r % 2 === 0) {
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFF8FAFC');
                }
            }
        }

        // ── Auto-width columns ────────────────────────────────────────────────
        foreach (range(1, count($headers)) as $colIndex) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ── Stream response ───────────────────────────────────────────────────
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }
}
