<?php

namespace App\Exports;

use App\Models\AssessmentResult;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SoaSheet implements FromCollection, WithHeadings, WithStyles, WithTitle, WithEvents, WithCustomStartCell
{
    protected $sessionId;
    protected $type;
    protected $title;

    public function __construct($sessionId, string $type, string $title)
    {
        $this->sessionId = $sessionId;
        $this->type = $type;
        $this->title = $title;
    }

    public function collection()
    {
        $orderKey = fn($result) => sprintf(
            '%s|%s',
            $result->standard->parent?->code ?? '',
            $result->standard->code ?? ''
        );

        $results = AssessmentResult::with('standard.parent')
            ->where('session_id', $this->sessionId)
            ->get()
            ->filter(function ($result) {
                if (!$result->standard) {
                    return false;
                }

                if (!is_array($result->standard->questions) || count($result->standard->questions) === 0) {
                    return false;
                }

                if ($this->type === 'clausa') {
                    return $result->standard->type === 'clausa';
                }

                return $result->standard->type === 'control';
            })
            ->sortBy($orderKey, SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $grouped = $results->groupBy(fn($result) => $result->standard->parent ? $result->standard->parent->code . ' - ' . $result->standard->parent->title : 'No Parent');

        $rows = collect();

        foreach ($grouped as $parentLabel => $group) {
            $rows->push([$parentLabel]);

            foreach ($group as $result) {
                $rows->push([
                    $result->standard->code,
                    $result->standard->title,
                    $result->standard->description,
                    $result->is_applicable ? 'Yes' : 'No',
                    $result->soa_justification ?? '-',
                    $result->maturity_rating,
                    $result->status == 'completed' ? 'Implemented' : 'Not Implemented / In Progress',
                    $result->notes ?? '-',
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Control Code',
            'Control Title',
            'Control Description',
            'Applicable?',
            'Justification for Exclusion',
            'Maturity Level (0-5)',
            'Status',
            'Evidence/Notes'
        ];
    }

    public function map($result): array
    {
        return [
            $result->standard->code,
            $result->standard->title,
            $result->standard->description,
            $result->is_applicable ? 'Yes' : 'No',
            $result->soa_justification ?? '-',
            $result->maturity_rating,
            $result->status == 'completed' ? 'Implemented' : 'Not Implemented / In Progress',
            $result->notes ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            2 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'color' => ['argb' => 'FF1E293B']],
            ],
            'A' => ['font' => ['bold' => true]],
        ];
    }

    public function startCell(): string
    {
        return 'A2';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->mergeCells('A1:H1');
                $sheet->setCellValue('A1', $this->title);
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                $highestRow = $sheet->getHighestRow();
                for ($row = 3; $row <= $highestRow; $row++) {
                    $firstColumnValue = trim((string)$sheet->getCell('A' . $row)->getValue());
                    $secondColumnValue = trim((string)$sheet->getCell('B' . $row)->getValue());

                    if ($firstColumnValue !== '' && $secondColumnValue === '') {
                        $sheet->mergeCells(sprintf('A%d:H%d', $row, $row));
                        $sheet->getStyle('A' . $row)->applyFromArray([
                            'font' => ['bold' => true],
                            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
                        ]);
                    }
                }
            },
        ];
    }

    public function title(): string
    {
        return $this->title;
    }
}

class SoaExport implements WithMultipleSheets
{
    protected $sessionId;

    public function __construct($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    public function sheets(): array
    {
        return [
            new SoaSheet($this->sessionId, 'clausa', 'Clausa Controls'),
            new SoaSheet($this->sessionId, 'annex', 'Annex Controls'),
        ];
    }
}
