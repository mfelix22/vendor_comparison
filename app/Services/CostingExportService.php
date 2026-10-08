<?php

namespace App\Services;

use App\Models\Costing;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CostingExportService
{
    public function export(Costing $costing): StreamedResponse
    {
        $costing->load(['vehicles.accessories']);
        $vehicles = $costing->vehicles;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Costing');

        // Font
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);

        // Header
        $sheet->setCellValue('A1', $costing->customer_name);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        // Subheader: Costing Number & Date
        $sheet->setCellValue('A2', "No: {$costing->costing_number} | Tanggal: " . $costing->created_at->format('d/m/Y'));
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('666666');

        // Row 4: Vehicle Brand
        $sheet->setCellValue('A4', 'Brand :');
        $sheet->setCellValue('A5', 'Type Kendaraan :');
        $sheet->setCellValue('A6', 'BBN :');
        $sheet->setCellValue('A7', 'Pemakaian :');
        $sheet->setCellValue('A8', 'Harga OTR');
        $sheet->setCellValue('A9', 'Diskon');
        $sheet->setCellValue('A10', 'Harga Nett');
        $sheet->setCellValue('A12', 'Estimasi kenaikan harga');
        $sheet->setCellValue('A14', 'Harga setelah estimasi kenaikan');
        $sheet->setCellValue('A16', 'Tambahan :');

        $sheet->getStyle('A4:A7')->getFont()->setBold(true);
        $sheet->getStyle('A8:A10')->getFont()->setBold(true);
        $sheet->getStyle('A14')->getFont()->setBold(true);
        $sheet->getStyle('A16')->getFont()->setBold(true);

        // Gather unique accessory names
        $accNames = [];
        foreach ($vehicles as $vehicle) {
            foreach ($vehicle->accessories as $acc) {
                if (!in_array($acc->accessory_name, $accNames, true)) {
                    $accNames[] = $acc->accessory_name;
                }
            }
        }

        // Fill vehicle columns
        $colIndex = 2; // Column B
        foreach ($vehicles as $vehicle) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex);

            $sheet->setCellValue("{$colLetter}4", $vehicle->brand);
            $sheet->setCellValue("{$colLetter}5", $vehicle->vehicle_type);
            $sheet->setCellValue("{$colLetter}6", $vehicle->bbn);
            $sheet->setCellValue("{$colLetter}7", $vehicle->pemakaian);

            $sheet->setCellValue("{$colLetter}8", (float) $vehicle->harga_otr);
            $sheet->setCellValue("{$colLetter}9", (float) $vehicle->diskon);
            $sheet->setCellValue("{$colLetter}10", "={$colLetter}8-{$colLetter}9"); // Nett formula

            $sheet->setCellValue("{$colLetter}12", (float) $vehicle->estimasi_kenaikan);
            $sheet->setCellValue("{$colLetter}14", "={$colLetter}10+{$colLetter}12"); // After increase

            // Style headers
            $sheet->getStyle("{$colLetter}4:{$colLetter}5")->getFont()->setBold(true);
            $sheet->getStyle("{$colLetter}10")->getFont()->setBold(true);
            $sheet->getStyle("{$colLetter}14")->getFont()->setBold(true);

            $colIndex++;
        }

        $lastColLetter = Coordinate::stringFromColumnIndex($colIndex - 1);

        // Accessories rows starting at row 17
        $currRow = 17;
        $accStartRow = $currRow;

        foreach ($accNames as $accName) {
            $sheet->setCellValue("A{$currRow}", $accName);

            $cIdx = 2;
            foreach ($vehicles as $vehicle) {
                $cLet = Coordinate::stringFromColumnIndex($cIdx);
                $foundAcc = $vehicle->accessories->firstWhere('accessory_name', $accName);
                $amt = $foundAcc ? (float) $foundAcc->amount : 0;
                $sheet->setCellValue("{$cLet}{$currRow}", $amt);
                $cIdx++;
            }
            $currRow++;
        }

        $accEndRow = max($accStartRow, $currRow - 1);
        $totalAccRow = $currRow + 1;

        // Total Acc row
        $sheet->setCellValue("A{$totalAccRow}", 'Total Acc');
        $sheet->getStyle("A{$totalAccRow}")->getFont()->setBold(true);

        $cIdx = 2;
        foreach ($vehicles as $vehicle) {
            $cLet = Coordinate::stringFromColumnIndex($cIdx);
            if (!empty($accNames)) {
                $sheet->setCellValue("{$cLet}{$totalAccRow}", "=SUM({$cLet}{$accStartRow}:{$cLet}{$accEndRow})");
            } else {
                $sheet->setCellValue("{$cLet}{$totalAccRow}", 0);
            }
            $sheet->getStyle("{$cLet}{$totalAccRow}")->getFont()->setBold(true);
            $cIdx++;
        }

        // Grand Total row
        $grandTotalRow = $totalAccRow + 2;
        $sheet->setCellValue("A{$grandTotalRow}", 'Harga Unit + Aksesoris');
        $sheet->getStyle("A{$grandTotalRow}")->getFont()->setBold(true)->setSize(11);

        $cIdx = 2;
        foreach ($vehicles as $vehicle) {
            $cLet = Coordinate::stringFromColumnIndex($cIdx);
            $sheet->setCellValue("{$cLet}{$grandTotalRow}", "={$cLet}14+{$cLet}{$totalAccRow}");
            $sheet->getStyle("{$cLet}{$grandTotalRow}")->getFont()->setBold(true);
            $cIdx++;
        }

        // Number Formatting (Currency)
        $numFormat = '#,##0';
        $sheet->getStyle("B8:{$lastColLetter}10")->getNumberFormat()->setFormatCode($numFormat);
        $sheet->getStyle("B12:{$lastColLetter}14")->getNumberFormat()->setFormatCode($numFormat);
        if (!empty($accNames)) {
            $sheet->getStyle("B{$accStartRow}:{$lastColLetter}{$accEndRow}")->getNumberFormat()->setFormatCode($numFormat);
        }
        $sheet->getStyle("B{$totalAccRow}:{$lastColLetter}{$totalAccRow}")->getNumberFormat()->setFormatCode($numFormat);
        $sheet->getStyle("B{$grandTotalRow}:{$lastColLetter}{$grandTotalRow}")->getNumberFormat()->setFormatCode($numFormat);

        // Styling highlights
        $sheet->getStyle("A10:{$lastColLetter}10")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0FDF4');
        $sheet->getStyle("A14:{$lastColLetter}14")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEF9C3');
        $sheet->getStyle("A{$grandTotalRow}:{$lastColLetter}{$grandTotalRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EDE9FE');

        // Thin borders for table sections
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ];
        $sheet->getStyle("A4:{$lastColLetter}10")->applyFromArray($borderStyle);
        $sheet->getStyle("A12:{$lastColLetter}14")->applyFromArray($borderStyle);
        if (!empty($accNames)) {
            $sheet->getStyle("A{$accStartRow}:{$lastColLetter}{$totalAccRow}")->applyFromArray($borderStyle);
        }
        $sheet->getStyle("A{$grandTotalRow}:{$lastColLetter}{$grandTotalRow}")->applyFromArray($borderStyle);

        // Auto column widths
        foreach (range(1, $colIndex - 1) as $colNum) {
            $sheet->getColumnDimensionByColumn($colNum)->setAutoSize(true);
        }

        // Response stream
        $fileName = 'Costing_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $costing->costing_number) . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
