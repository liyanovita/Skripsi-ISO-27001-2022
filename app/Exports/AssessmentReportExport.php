<?php

namespace App\Exports;

use App\Models\AssessmentResult;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssessmentReportExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithCustomValueBinder, WithStyles
{
    protected $sessionId;
    protected int $rowIndex = 0;

    public function __construct($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * Mengambil data dengan filter IDENTIK dengan Gap Report Table (getFindings):
     * - Hanya kontrol dengan pertanyaan (assessable)
     * - is_applicable = true
     * - status = completed (sudah dinilai)
     * - maturity_rating 0-3 (non-compliant, termasuk 0 = Non-existent)
     * - Diurutkan dari yang paling kritis (maturity_rating terendah)
     */
    public function collection()
    {
        $results = AssessmentResult::with(['standard', 'session'])
            ->where('session_id', $this->sessionId)
            ->where('is_applicable', true)
            ->where('status', 'completed')
            ->where('maturity_rating', '>=', 0)
            ->where('maturity_rating', '<', 4)
            ->whereHas('standard', fn($q) => $q->whereNotNull('questions'))
            ->get()
            ->filter(fn($r) => is_array($r->standard?->questions) && count($r->standard->questions) > 0);

        return $results->sortBy('maturity_rating')->values();
    }

    /**
     * Header kolom IDENTIK dengan tabel Priority Roadmap di PDF ditambah data AI:
     */
    public function headings(): array
    {
        return [
            'Priority',
            'ISO Code',
            'Control Name',
            'Maturity Level',
            'Status Compliance',
            'Risk',
            'Target Days',
            'Audit Notes',
            'AI Strategic Recommendation',
            'AI Audit Insight (Gap)',
            'Corrective Action Plan (CAP)',
        ];
    }

    /**
     * Mapping data IDENTIK dengan kolom yang ditampilkan di PDF
     */
    public function map($result): array
    {
        // Hitung nomor prioritas berdasarkan posisi di collection (sama seperti $index+1 di PDF)
        $priority = ++$this->rowIndex;

        // Target Days: logika identik dengan pdf_template.blade.php
        $targetDays = match(true) {
            $result->maturity_rating <= 1 => '30 Days',
            $result->maturity_rating == 2 => '60 Days',
            $result->maturity_rating == 3 => '90 Days',
            default                       => '180 Days',
        };

        $insight = is_array($result->control_insight) ? ($result->control_insight['gap'] ?? '') : ($result->control_insight ?? '');
        $insight = trim($insight);
        if (empty($insight)) {
            $insight = 'AI insight not yet generated.';
        }
        


        $cap = is_array($result->corrective_action_plan) ? implode("\n", $result->corrective_action_plan) : ($result->corrective_action_plan ?? '');
        $cap = trim($cap);
        if (empty($cap)) {
            $cap = 'AI corrective action plan not yet generated.';
        }
        
        $notes = trim($result->notes ?? '');
        if (empty($notes)) {
            $notes = 'No audit notes provided.';
        }

        $recommendation = trim($result->ai_recommendation ?? '');
        if (empty($recommendation)) {
            $recommendation = 'AI recommendation not yet generated.';
        }

        return [
            $priority,
            $result->standard->code,
            $result->standard->title,
            $result->maturity_rating,
            $result->compliance_status,
            $result->risk_level,
            $targetDays,
            $notes,
            $recommendation,
            $insight,
            $cap,
        ];
    }

    /**
     * Memaksa kolom ISO Code (B) menjadi format teks agar tidak terkonversi menjadi format pecahan desimal oleh Excel.
     */
    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getColumn() === 'B' && is_scalar($value)) {
            $cell->setValueExplicit((string)$value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    /**
     * Style the worksheet.
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size'  => 10,
                ],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E3A5F'], // Navy Blue
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
