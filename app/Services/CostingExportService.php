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

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);

        // Sheet 1: BU AB
        $sheetBuAb = $spreadsheet->getActiveSheet();
        $this->buildSheet($sheetBuAb, $costing, false);

        // Sheet 2: SMD
        $sheetSmd = $spreadsheet->createSheet();
        $this->buildSheet($sheetSmd, $costing, true);

        // Make BU AB the default active sheet
        $spreadsheet->setActiveSheetIndex(0);

        $fileName = 'Costing_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $costing->costing_number) . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Build an individual sheet (BU AB or SMD).
     */
    private function buildSheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, Costing $costing, bool $isSmd): void
    {
        // Auto-sort vehicles by Pulau -> Provinsi -> Pemakaian -> sort_order so grouping & horizontal cell merging are cohesive
        $vehicles = $costing->vehicles->sortBy([
            ['pulau', 'asc'],
            ['provinsi', 'asc'],
            ['pemakaian', 'asc'],
            ['sort_order', 'asc'],
        ])->values();
        $sheet->setTitle($isSmd ? 'SMD' : 'BU AB');

        $thinBorder = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        // Gather unique accessory names in encounter order
        $accNames = [];
        foreach ($vehicles as $v) {
            foreach ($v->accessories as $a) {
                if (!in_array($a->accessory_name, $accNames, true)) {
                    $accNames[] = $a->accessory_name;
                }
            }
        }

        $numFormat = '_(* #,##0_);_(* (#,##0);_(* "-"??_);_(@_)';
        $colCount = count($vehicles);
        $lastColLetter = Coordinate::stringFromColumnIndex(max(2, 1 + $colCount));

        // Regional theme with per-province color inheritance
        $regionalTheme = [
            'JAWA' => [
                'main' => 'C5E0B4', // Soft Forest Mint Green
                'provinces' => [
                    'DKI JAKARTA'                => 'E2EFDA', // Light Mint
                    'JAWA BARAT'                 => 'D5E8D4', // Pale Celadon
                    'JAWA TENGAH'                => 'E2F0CB', // Lime Pasture
                    'JAWA TIMUR'                 => 'D0EAD4', // Pale Mint Sage
                    'BANTEN'                     => 'CDECCD', // Light Meadow
                    'DAERAH ISTIMEWA YOGYAKARTA' => 'E5EED0', // Light Tea Green
                ],
            ],
            'SUMATERA' => [
                'main' => 'F8CBAD', // Soft Amber Peach
                'provinces' => [
                    'ACEH'             => 'FCE4D6', // Pale Peach
                    'SUMATERA UTARA'   => 'FCE8D5', // Soft Apricot
                    'SUMATERA BARAT'   => 'FBE5C8', // Warm Bisque
                    'RIAU'             => 'FDEBD0', // Creamy Sand
                    'KEPULAUAN RIAU'   => 'FCE1C9', // Light Coral Peach
                    'JAMBI'            => 'F8E0C8', // Muted Apricot
                    'SUMATERA SELATAN' => 'FADBD8', // Soft Blush Peach
                    'BANGKA BELITUNG'  => 'FDEBD2', // Warm Gold Cream
                    'BENGKULU'         => 'FBE8DA', // Light Bisque
                    'LAMPUNG'          => 'F5D8C3', // Light Terracotta
                ],
            ],
            'KALIMANTAN' => [
                'main' => 'BDD7EE', // Soft Sky Blue
                'provinces' => [
                    'KALIMANTAN BARAT'   => 'DDEBF7', // Light Ice Blue
                    'KALIMANTAN TENGAH'  => 'D2E5F5', // Soft Sky
                    'KALIMANTAN SELATAN' => 'E1EDF8', // Pale Azure
                    'KALIMANTAN TIMUR'   => 'C9E0F2', // Powder Blue
                    'KALIMANTAN UTARA'   => 'D6E8F7', // Breeze Blue
                ],
            ],
            'SULAWESI' => [
                'main' => 'CCC0DA', // Soft Lavender Violet
                'provinces' => [
                    'SULAWESI UTARA'    => 'E6DFEC', // Pale Lavender
                    'GORONTALO'         => 'E1D5EA', // Lilac Mist
                    'SULAWESI TENGAH'   => 'EDE5F3', // Soft Violet
                    'SULAWESI BARAT'    => 'DFD3EB', // Heather
                    'SULAWESI SELATAN'  => 'E4DAEE', // Light Orchid
                    'SULAWESI TENGGARA' => 'EBE2F5', // Pastel Lilac
                ],
            ],
            'BALI & NUSA TENGGARA' => [
                'main' => 'F4B084', // Soft Coral Rose
                'provinces' => [
                    'BALI'                => 'FCE4D6', // Soft Coral
                    'NUSA TENGGARA BARAT' => 'FADBD8', // Pale Rose
                    'NUSA TENGGARA TIMUR' => 'F8D7DA', // Pastel Salmon
                ],
            ],
            'PAPUA & MALUKU' => [
                'main' => 'A2D9CE', // Soft Seafoam Teal
                'provinces' => [
                    'MALUKU'            => 'D1F2EB', // Light Mint Aqua
                    'MALUKU UTARA'      => 'E8F8F5', // Pale Aqua
                    'PAPUA'             => 'D4EFDF', // Soft Seafoam
                    'PAPUA BARAT'       => 'E5F9E0', // Pale Sage Aqua
                    'PAPUA BARAT DAYA'  => 'D0ECE7', // Light Aqua Breeze
                    'PAPUA PEGUNUNGAN'  => 'E1F5FE', // Crisp Aqua
                    'PAPUA SELATAN'     => 'E0F2F1', // Pale Teal
                    'PAPUA TENGAH'      => 'DCECC9', // Pastel Aqua Grass
                ],
            ],
        ];

        $getPulauRgb = function ($pulau) use ($regionalTheme) {
            $p = strtoupper(trim($pulau ?? ''));
            return $regionalTheme[$p]['main'] ?? 'EFEFEF';
        };

        $getProvRgb = function ($pulau, $provinsi) use ($regionalTheme) {
            $p = strtoupper(trim($pulau ?? ''));
            $pr = strtoupper(trim($provinsi ?? ''));
            return $regionalTheme[$p]['provinces'][$pr] ?? ($regionalTheme[$p]['main'] ?? 'F8F8F8');
        };

        // Row 1: Customer Name (Merged Across Full Table Width)
        $sheet->setCellValue('A1', $costing->customer_name);
        if ($colCount > 0) {
            $sheet->mergeCells("A1:{$lastColLetter}1");
        }
        $sheet->getStyle('A1')->getFont()->setName('Calibri')->setBold(true)->setSize(11);

        // Row 2: SMD, Masa Sewa, Delivery Date (Merged Across Full Table Width)
        $metaParts = [];
        if (!empty($costing->smd)) {
            $metaParts[] = 'SMD: ' . $costing->smd;
        }
        if (!empty($costing->masa_sewa)) {
            $metaParts[] = 'Masa Sewa: ' . $costing->masa_sewa;
        }
        if (!empty($costing->request_delivery_date)) {
            $metaParts[] = 'Delivery: ' . $costing->request_delivery_date->format('d/m/Y');
        }
        if (!empty($metaParts)) {
            $sheet->setCellValue('A2', implode(' | ', $metaParts));
            if ($colCount > 0) {
                $sheet->mergeCells("A2:{$lastColLetter}2");
            }
            $sheet->getStyle('A2')->getFont()->setName('Calibri')->setItalic(true)->setSize(10)->getColor()->setRGB('555555');
        }

        // Row 3: PULAU (Color-Coded by Regional Island Family)
        $sheet->setCellValue('A3', 'PULAU');
        $sheet->getStyle('A3')->getFont()->setName('Calibri')->setBold(true)->setSize(11);
        $sheet->getStyle('A3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A3:{$lastColLetter}3")->applyFromArray($thinBorder);

        $pulauGroups = [];
        $currPulau = null;
        $startColP = null;
        foreach ($vehicles as $idx => $v) {
            $cNum = $idx + 2;
            $val = strtoupper(trim($v->pulau ?: ''));
            if ($val === $currPulau) {
                // extend group
            } else {
                if ($currPulau !== null) {
                    $pulauGroups[] = ['val' => $currPulau, 'start' => $startColP, 'end' => $cNum - 1];
                }
                $currPulau = $val;
                $startColP = $cNum;
            }
        }
        if ($currPulau !== null) {
            $pulauGroups[] = ['val' => $currPulau, 'start' => $startColP, 'end' => count($vehicles) + 1];
        }

        foreach ($pulauGroups as $grp) {
            $sLet = Coordinate::stringFromColumnIndex($grp['start']);
            $eLet = Coordinate::stringFromColumnIndex($grp['end']);
            if ($grp['start'] < $grp['end']) {
                $sheet->mergeCells("{$sLet}3:{$eLet}3");
            }
            $pRgb = $getPulauRgb($grp['val']);

            $sheet->setCellValue("{$sLet}3", $grp['val']);
            $sheet->getStyle("{$sLet}3:{$eLet}3")->getFont()->setName('Calibri')->setBold(true)->setSize(11);
            $sheet->getStyle("{$sLet}3:{$eLet}3")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($pRgb);
            $sheet->getStyle("{$sLet}3:{$eLet}3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        }

        // Row 4: PROVINSI (Coordinating Lighter Pastel of Island)
        $sheet->setCellValue('A4', 'PROVINSI');
        $sheet->getStyle('A4')->getFont()->setName('Calibri')->setBold(true)->setSize(11);
        $sheet->getStyle('A4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A4:{$lastColLetter}4")->applyFromArray($thinBorder);

        $provGroups = [];
        $currProv = null;
        $currProvPulau = null;
        $startColPr = null;
        foreach ($vehicles as $idx => $v) {
            $cNum = $idx + 2;
            $val = strtoupper(trim($v->provinsi ?: ''));
            $pVal = strtoupper(trim($v->pulau ?: ''));
            if ($val === $currProv) {
                // extend group
            } else {
                if ($currProv !== null) {
                    $provGroups[] = ['val' => $currProv, 'pulau' => $currProvPulau, 'start' => $startColPr, 'end' => $cNum - 1];
                }
                $currProv = $val;
                $currProvPulau = $pVal;
                $startColPr = $cNum;
            }
        }
        if ($currProv !== null) {
            $provGroups[] = ['val' => $currProv, 'pulau' => $currProvPulau, 'start' => $startColPr, 'end' => count($vehicles) + 1];
        }

        foreach ($provGroups as $grp) {
            $sLet = Coordinate::stringFromColumnIndex($grp['start']);
            $eLet = Coordinate::stringFromColumnIndex($grp['end']);
            if ($grp['start'] < $grp['end']) {
                $sheet->mergeCells("{$sLet}4:{$eLet}4");
            }
            $prRgb = $getProvRgb($grp['pulau'] ?? '', $grp['val'] ?? '');

            $sheet->setCellValue("{$sLet}4", $grp['val']);
            $sheet->getStyle("{$sLet}4:{$eLet}4")->getFont()->setName('Calibri')->setBold(true)->setSize(11);
            $sheet->getStyle("{$sLet}4:{$eLet}4")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($prRgb);
            $sheet->getStyle("{$sLet}4:{$eLet}4")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        }

        // Row 5: Brand
        $sheet->setCellValue('A5', 'Brand :');
        $sheet->getStyle("A5:{$lastColLetter}5")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
        $sheet->getStyle("A5:{$lastColLetter}5")->getFont()->setName('Times New Roman')->setBold(true);
        $sheet->getStyle("A5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A5:{$lastColLetter}5")->applyFromArray($thinBorder);

        // Row 6: Type Kendaraan
        $sheet->setCellValue('A6', 'Type Kendaraan :');
        $sheet->getStyle("A6:{$lastColLetter}6")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
        $sheet->getStyle("A6:{$lastColLetter}6")->getFont()->setName('Times New Roman')->setBold(true);
        $sheet->getStyle("A6")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A6:{$lastColLetter}6")->getAlignment()->setWrapText(true);
        $sheet->getStyle("A6:{$lastColLetter}6")->applyFromArray($thinBorder);

        // Adjust row 6 height dynamically based on longest vehicle name
        $maxVehicleTypeLen = 0;
        foreach ($vehicles as $v) {
            $maxVehicleTypeLen = max($maxVehicleTypeLen, mb_strlen($v->vehicle_type ?? ''));
        }
        $sheet->getRowDimension(6)->setRowHeight($maxVehicleTypeLen > 24 ? 36 : 28);

        // Row 7: BBN
        $sheet->setCellValue('A7', 'BBN :');
        $sheet->getStyle("A7:{$lastColLetter}7")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('66FFFF');
        $sheet->getStyle("A7:{$lastColLetter}7")->getFont()->setName('Times New Roman')->setBold(true);
        $sheet->getStyle("A7")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A7:{$lastColLetter}7")->applyFromArray($thinBorder);

        // Row 8: Pemakaian
        $sheet->setCellValue('A8', 'Pemakaian :');
        $sheet->getStyle("A8:{$lastColLetter}8")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('66FFFF');
        $sheet->getStyle("A8:{$lastColLetter}8")->getFont()->setName('Times New Roman')->setBold(true);
        $sheet->getStyle("A8")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A8:{$lastColLetter}8")->applyFromArray($thinBorder);

        // Vehicle metadata columns (rows 5 to 8)
        $cIdx = 2;
        foreach ($vehicles as $v) {
            $cLet = Coordinate::stringFromColumnIndex($cIdx);
            $sheet->setCellValue("{$cLet}5", $v->brand);
            $sheet->setCellValue("{$cLet}6", $v->vehicle_type);
            $sheet->setCellValue("{$cLet}7", $v->bbn);
            $sheet->setCellValue("{$cLet}8", $v->pemakaian);
            $sheet->getStyle("{$cLet}5:{$cLet}8")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("{$cLet}6")->getAlignment()->setWrapText(true);
            $cIdx++;
        }

        // Row 9: Harga OTR
        $sheet->setCellValue('A9', 'Harga OTR');
        $sheet->getStyle('A9')->getFont()->setName('Times New Roman')->setBold(true);
        $sheet->getStyle("A9:{$lastColLetter}9")->applyFromArray($thinBorder);
        $cIdx = 2;
        foreach ($vehicles as $v) {
            $cLet = Coordinate::stringFromColumnIndex($cIdx);
            $sheet->setCellValue("{$cLet}9", (float) $v->harga_otr);
            $sheet->getStyle("{$cLet}9")->getFont()->setName('Times New Roman')->setBold(true);
            $sheet->getStyle("{$cLet}9")->getNumberFormat()->setFormatCode($numFormat);
            $cIdx++;
        }

        if (!$isSmd) {
            // ==========================================
            // BU AB SPECIFIC ROWS (Includes Diskon & Nett)
            // ==========================================
            // Row 10: Diskon
            $sheet->setCellValue('A10', 'Diskon');
            $sheet->getStyle('A10')->getFont()->setName('Times New Roman');
            $sheet->getStyle("A10:{$lastColLetter}10")->applyFromArray($thinBorder);
            $cIdx = 2;
            foreach ($vehicles as $v) {
                $cLet = Coordinate::stringFromColumnIndex($cIdx);
                $sheet->setCellValue("{$cLet}10", (float) $v->diskon);
                $sheet->getStyle("{$cLet}10")->getFont()->setName('Calibri')->setBold(true);
                $sheet->getStyle("{$cLet}10")->getNumberFormat()->setFormatCode($numFormat);
                $cIdx++;
            }

            // Row 11: Harga Nett (= OTR - Diskon)
            $sheet->setCellValue('A11', 'Harga Nett');
            $sheet->getStyle('A11')->getFont()->setName('Times New Roman')->setBold(true);
            $sheet->getStyle("A11:{$lastColLetter}11")->applyFromArray($thinBorder);
            $cIdx = 2;
            foreach ($vehicles as $v) {
                $cLet = Coordinate::stringFromColumnIndex($cIdx);
                $sheet->setCellValue("{$cLet}11", "={$cLet}9-{$cLet}10");
                $sheet->getStyle("{$cLet}11")->getFont()->setName('Calibri')->setBold(true);
                $sheet->getStyle("{$cLet}11")->getNumberFormat()->setFormatCode($numFormat);
                $cIdx++;
            }

            // Row 12: Blank
            $sheet->getStyle("A12:{$lastColLetter}12")->applyFromArray($thinBorder);

            // Row 13: Estimasi kenaikan harga
            $sheet->setCellValue('A13', 'Estimasi kenaikan harga');
            $sheet->getStyle('A13')->getFont()->setName('Times New Roman')->setBold(true);
            $sheet->getStyle("A13:{$lastColLetter}13")->applyFromArray($thinBorder);
            $cIdx = 2;
            foreach ($vehicles as $v) {
                $cLet = Coordinate::stringFromColumnIndex($cIdx);
                $sheet->setCellValue("{$cLet}13", (float) $v->estimasi_kenaikan);
                $sheet->getStyle("{$cLet}13")->getFont()->setName('Calibri')->setBold(true);
                $sheet->getStyle("{$cLet}13")->getNumberFormat()->setFormatCode($numFormat);
                $cIdx++;
            }

            // Row 14: Blank
            $sheet->getStyle("A14:{$lastColLetter}14")->applyFromArray($thinBorder);

            // Row 15: Harga setelah estimasi kenaikan (= Nett + Estimasi)
            $sheet->setCellValue('A15', 'Harga setelah estimasi kenaikan');
            $sheet->getStyle('A15')->getFont()->setName('Times New Roman')->setBold(true);
            $sheet->getStyle("A15:{$lastColLetter}15")->applyFromArray($thinBorder);
            $cIdx = 2;
            foreach ($vehicles as $v) {
                $cLet = Coordinate::stringFromColumnIndex($cIdx);
                $sheet->setCellValue("{$cLet}15", "={$cLet}11+{$cLet}13");
                $sheet->getStyle("{$cLet}15")->getFont()->setName('Calibri')->setBold(true);
                $sheet->getStyle("{$cLet}15")->getNumberFormat()->setFormatCode($numFormat);
                $cIdx++;
            }

            // Row 16: Blank
            $sheet->getStyle("A16:{$lastColLetter}16")->applyFromArray($thinBorder);

            // Row 17: Tambahan :
            $sheet->setCellValue('A17', 'Tambahan : ');
            $sheet->getStyle('A17')->getFont()->setName('Calibri');
            $sheet->getStyle("A17:{$lastColLetter}17")->applyFromArray($thinBorder);

            $currRow = 18;
            $accStartRow = 18;
            $hargaSetelahRow = 15;
        } else {
            // ==========================================
            // SMD SPECIFIC ROWS (NO Diskon, NO Nett)
            // ==========================================
            // Row 10: Blank
            $sheet->getStyle("A10:{$lastColLetter}10")->applyFromArray($thinBorder);

            // Row 11: Estimasi kenaikan harga
            $sheet->setCellValue('A11', 'Estimasi kenaikan harga');
            $sheet->getStyle('A11')->getFont()->setName('Times New Roman')->setBold(true);
            $sheet->getStyle("A11:{$lastColLetter}11")->applyFromArray($thinBorder);
            $cIdx = 2;
            foreach ($vehicles as $v) {
                $cLet = Coordinate::stringFromColumnIndex($cIdx);
                $sheet->setCellValue("{$cLet}11", (float) $v->estimasi_kenaikan);
                $sheet->getStyle("{$cLet}11")->getFont()->setName('Calibri')->setBold(true);
                $sheet->getStyle("{$cLet}11")->getNumberFormat()->setFormatCode($numFormat);
                $cIdx++;
            }

            // Row 12: Blank
            $sheet->getStyle("A12:{$lastColLetter}12")->applyFromArray($thinBorder);

            // Row 13: Harga setelah estimasi kenaikan (= OTR + Estimasi, NO DISKON!)
            $sheet->setCellValue('A13', 'Harga setelah estimasi kenaikan');
            $sheet->getStyle('A13')->getFont()->setName('Times New Roman')->setBold(true);
            $sheet->getStyle("A13:{$lastColLetter}13")->applyFromArray($thinBorder);
            $cIdx = 2;
            foreach ($vehicles as $v) {
                $cLet = Coordinate::stringFromColumnIndex($cIdx);
                $sheet->setCellValue("{$cLet}13", "={$cLet}9+{$cLet}11");
                $sheet->getStyle("{$cLet}13")->getFont()->setName('Calibri')->setBold(true);
                $sheet->getStyle("{$cLet}13")->getNumberFormat()->setFormatCode($numFormat);
                $cIdx++;
            }

            // Row 14: Blank
            $sheet->getStyle("A14:{$lastColLetter}14")->applyFromArray($thinBorder);

            // Row 15: Tambahan :
            $sheet->setCellValue('A15', 'Tambahan : ');
            $sheet->getStyle('A15')->getFont()->setName('Calibri');
            $sheet->getStyle("A15:{$lastColLetter}15")->applyFromArray($thinBorder);

            $currRow = 16;
            $accStartRow = 16;
            $hargaSetelahRow = 13;
        }

        // Accessories rows
        foreach ($accNames as $accName) {
            $sheet->setCellValue("A{$currRow}", $accName);
            $sheet->getStyle("A{$currRow}")->getFont()->setName('Calibri');
            $sheet->getStyle("A{$currRow}:{$lastColLetter}{$currRow}")->applyFromArray($thinBorder);

            $cIdx = 2;
            foreach ($vehicles as $v) {
                $cLet = Coordinate::stringFromColumnIndex($cIdx);
                $foundAcc = $v->accessories->firstWhere('accessory_name', $accName);
                $amt = $foundAcc ? (float) $foundAcc->amount : 0;
                $sheet->setCellValue("{$cLet}{$currRow}", $amt);
                $sheet->getStyle("{$cLet}{$currRow}")->getFont()->setName('Calibri');
                $sheet->getStyle("{$cLet}{$currRow}")->getNumberFormat()->setFormatCode($numFormat);
                $cIdx++;
            }
            $currRow++;
        }

        $accEndRow = max($accStartRow, $currRow - 1);

        // Blank row after accessories
        $sheet->getStyle("A{$currRow}:{$lastColLetter}{$currRow}")->applyFromArray($thinBorder);
        $currRow++;

        // Total Acc row
        $totalAccRow = $currRow;
        $sheet->setCellValue("A{$totalAccRow}", 'Total Acc');
        $sheet->getStyle("A{$totalAccRow}")->getFont()->setName('Calibri');
        $sheet->getStyle("A{$totalAccRow}:{$lastColLetter}{$totalAccRow}")->applyFromArray($thinBorder);
        $cIdx = 2;
        foreach ($vehicles as $v) {
            $cLet = Coordinate::stringFromColumnIndex($cIdx);
            if (!empty($accNames)) {
                $sheet->setCellValue("{$cLet}{$totalAccRow}", "=SUM({$cLet}{$accStartRow}:{$cLet}{$accEndRow})");
            } else {
                $sheet->setCellValue("{$cLet}{$totalAccRow}", 0);
            }
            $sheet->getStyle("{$cLet}{$totalAccRow}")->getFont()->setName('Calibri');
            $sheet->getStyle("{$cLet}{$totalAccRow}")->getNumberFormat()->setFormatCode($numFormat);
            $cIdx++;
        }
        $currRow++;

        // Blank row after Total Acc
        $sheet->getStyle("A{$currRow}:{$lastColLetter}{$currRow}")->applyFromArray($thinBorder);
        $currRow++;

        // Harga Unit + Aksesoris row
        $grandTotalRow = $currRow;
        $sheet->setCellValue("A{$grandTotalRow}", 'Harga Unit + Aksesoris');
        $sheet->getStyle("A{$grandTotalRow}")->getFont()->setName('Calibri')->setBold(true);
        $sheet->getStyle("A{$grandTotalRow}:{$lastColLetter}{$grandTotalRow}")->applyFromArray($thinBorder);
        $cIdx = 2;
        foreach ($vehicles as $v) {
            $cLet = Coordinate::stringFromColumnIndex($cIdx);
            $sheet->setCellValue("{$cLet}{$grandTotalRow}", "={$cLet}{$hargaSetelahRow}+{$cLet}{$totalAccRow}");
            $sheet->getStyle("{$cLet}{$grandTotalRow}")->getFont()->setName('Calibri')->setBold(true);
            $sheet->getStyle("{$cLet}{$grandTotalRow}")->getNumberFormat()->setFormatCode($numFormat);
            $cIdx++;
        }
        $currRow++;

        // Row: Note per vehicle column
        $vehicleNoteRow = $currRow;
        $sheet->setCellValue("A{$vehicleNoteRow}", 'Note :');
        $sheet->getStyle("A{$vehicleNoteRow}")->getFont()->setName('Calibri')->setBold(true);
        $sheet->getStyle("A{$vehicleNoteRow}:{$lastColLetter}{$vehicleNoteRow}")->applyFromArray($thinBorder);
        $sheet->getStyle("A{$vehicleNoteRow}:{$lastColLetter}{$vehicleNoteRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

        $maxNoteLines = 1;
        $cIdx = 2;
        foreach ($vehicles as $v) {
            $cLet = Coordinate::stringFromColumnIndex($cIdx);
            $noteContent = $v->note ?? '';
            $sheet->setCellValue("{$cLet}{$vehicleNoteRow}", $noteContent);
            $sheet->getStyle("{$cLet}{$vehicleNoteRow}")->getFont()->setName('Calibri');
            $sheet->getStyle("{$cLet}{$vehicleNoteRow}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
            
            if (!empty($noteContent)) {
                $lines = count(explode("\n", str_replace("\r", "", $noteContent)));
                $maxNoteLines = max($maxNoteLines, $lines);
            }
            $cIdx++;
        }
        // Set dynamic row height for vehicle notes row
        $sheet->getRowDimension($vehicleNoteRow)->setRowHeight(max(24, $maxNoteLines * 18));
        $currRow++;

        // 2 blank rows without borders
        $currRow += 2;

        // Master Notes row
        $notesText = 'Notes :' . ($costing->notes ? "\n" . $costing->notes : '');
        $sheet->setCellValue("A{$currRow}", $notesText);
        $sheet->getStyle("A{$currRow}")->getFont()->setName('Calibri')->setBold(true);
        $sheet->getStyle("A{$currRow}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        if (!empty($costing->notes)) {
            $masterLines = count(explode("\n", str_replace("\r", "", $costing->notes))) + 1;
            $sheet->getRowDimension($currRow)->setRowHeight(max(24, $masterLines * 18));
        }

        // Column widths - dynamically fit the longest vehicle names and descriptions
        $sheet->getColumnDimension('A')->setWidth(32);
        $cIdx = 2;
        foreach ($vehicles as $v) {
            $vTypeLen = mb_strlen($v->vehicle_type ?? '');
            $brandLen = mb_strlen($v->brand ?? '');
            $longest = max($vTypeLen, $brandLen, 24);
            // Allow column to expand comfortably (minimum 32, up to 44)
            $colWidth = max(32, min(44, $longest + 3));
            $sheet->getColumnDimensionByColumn($cIdx)->setWidth($colWidth);
            $cIdx++;
        }
        for ($col = $cIdx; $col <= max(2, $colCount + 1); $col++) {
            $sheet->getColumnDimensionByColumn($col)->setWidth(30);
        }

        // Page setup for printing in Excel (Landscape, fit to page width)
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
    }
}
