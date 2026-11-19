<?php

namespace App\Services;

use App\Models\CostingVehicleType;
use App\Models\CostingVehicleTypePriceHistory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CostingVehicleTypePriceService
{
    /**
     * Download Excel template pre-filled with all existing active vehicle types.
     */
    public function downloadTemplate(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Harga OTR & Diskon');

        // Title Block
        $sheet->setCellValue('A1', 'MASTER HARGA OTR & DISKON KENDARAAN (COSTING)');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('1E3A8A');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Subtitle / Instructions
        $sheet->setCellValue('A2', 'Petunjuk: Isi kolom Harga OTR dan Diskon dengan angka murni tanpa titik/koma (contoh: 350000000). Jangan mengubah kolom ID atau Model Kendaraan.');
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(9)->getColor()->setRGB('4B5563');

        // Headers (Row 4)
        $headers = [
            'A4' => 'ID System',
            'B4' => 'Brand',
            'C4' => 'Type / Model Kendaraan',
            'D4' => 'Harga OTR (Rp)',
            'E4' => 'Diskon (Rp)',
            'F4' => 'Catatan / Notes Perubahan',
        ];

        foreach ($headers as $cell => $label) {
            $sheet->setCellValue($cell, $label);
        }

        // Header Style (Primary Navy)
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E40AF'],
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            ],
        ];
        $sheet->getStyle('A4:F4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(28);

        // Populate Active Vehicle Types
        $types = CostingVehicleType::where('is_active', true)
            ->orderBy('brand_name')
            ->orderBy('name')
            ->get();

        $rowIdx = 5;
        foreach ($types as $t) {
            $sheet->setCellValue("A{$rowIdx}", $t->id);
            $sheet->setCellValue("B{$rowIdx}", $t->brand_name ?: ($t->brand->name ?? '—'));
            $sheet->setCellValue("C{$rowIdx}", $t->name);
            $sheet->setCellValue("D{$rowIdx}", (float)$t->harga_otr);
            $sheet->setCellValue("E{$rowIdx}", (float)$t->diskon);
            $sheet->setCellValue("F{$rowIdx}", '');

            // Formatting
            $sheet->getStyle("A{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("D{$rowIdx}:E{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("D{$rowIdx}:E{$rowIdx}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("F{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Zebra striping
            if ($rowIdx % 2 === 0) {
                $sheet->getStyle("A{$rowIdx}:F{$rowIdx}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8FAFC');
            }

            $rowIdx++;
        }

        $lastRow = max(5, $rowIdx - 1);
        $sheet->getStyle("A5:F{$lastRow}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setRGB('CBD5E1');

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(22);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(30);

        $filename = 'Template_Master_Harga_OTR_Diskon_' . date('Ymd_His') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * Preview changes from an Excel spreadsheet without saving anything to the database.
     */
    public function previewFromExcel(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $preview = [
            'total_rows'      => 0,
            'changed_count'   => 0,
            'unchanged_count' => 0,
            'not_found_count' => 0,
            'items'           => [],
        ];

        // Find header row (either row 4 or first row with "Harga OTR")
        $startRow = 5;
        for ($r = 1; $r <= min(10, $highestRow); $r++) {
            $val = strtolower((string)$sheet->getCell("D{$r}")->getValue());
            if (str_contains($val, 'harga otr') || str_contains($val, 'otr')) {
                $startRow = $r + 1;
                break;
            }
        }

        for ($row = $startRow; $row <= $highestRow; $row++) {
            $idVal        = trim((string)$sheet->getCell("A{$row}")->getValue());
            $brandVal     = trim((string)$sheet->getCell("B{$row}")->getValue());
            $nameVal      = trim((string)$sheet->getCell("C{$row}")->getValue());
            $rawOtrVal    = $sheet->getCell("D{$row}")->getValue();
            $rawDiskonVal = $sheet->getCell("E{$row}")->getValue();
            $notesVal     = trim((string)$sheet->getCell("F{$row}")->getValue());

            // Skip completely empty rows
            if (!$idVal && !$nameVal) {
                continue;
            }

            $preview['total_rows']++;

            // Lookup vehicle type: first by ID, fallback by exact name and brand
            $vehicleType = null;
            if ($idVal && is_numeric($idVal)) {
                $vehicleType = CostingVehicleType::find((int)$idVal);
            }
            if (!$vehicleType && $nameVal) {
                $query = CostingVehicleType::where('name', $nameVal);
                if ($brandVal) {
                    $query->where('brand_name', $brandVal);
                }
                $vehicleType = $query->first();
            }

            $newOtr = $this->cleanNumeric($rawOtrVal);
            $newDiskon = $this->cleanNumeric($rawDiskonVal);

            if (!$vehicleType) {
                $preview['not_found_count']++;
                $preview['items'][] = [
                    'row_num'        => $row,
                    'id'             => null,
                    'brand_name'     => $brandVal ?: '—',
                    'name'           => $nameVal,
                    'old_otr'        => 0,
                    'new_otr'        => $newOtr,
                    'otr_changed'    => true,
                    'old_diskon'     => 0,
                    'new_diskon'     => $newDiskon,
                    'diskon_changed' => true,
                    'old_netto'      => 0,
                    'new_netto'      => max(0, $newOtr - $newDiskon),
                    'notes'          => $notesVal,
                    'status'         => 'not_found',
                ];
                continue;
            }

            $oldOtr = (float)$vehicleType->harga_otr;
            $oldDiskon = (float)$vehicleType->diskon;

            $otrChanged = abs($newOtr - $oldOtr) >= 0.01;
            $diskonChanged = abs($newDiskon - $oldDiskon) >= 0.01;
            $hasChanged = $otrChanged || $diskonChanged;

            $oldNetto = max(0, $oldOtr - $oldDiskon);
            $newNetto = max(0, $newOtr - $newDiskon);

            if ($hasChanged) {
                $preview['changed_count']++;
                $status = 'changed';
            } else {
                $preview['unchanged_count']++;
                $status = 'unchanged';
            }

            $preview['items'][] = [
                'row_num'        => $row,
                'id'             => $vehicleType->id,
                'brand_name'     => $vehicleType->brand_name,
                'name'           => $vehicleType->name,
                'old_otr'        => $oldOtr,
                'new_otr'        => $newOtr,
                'otr_changed'    => $otrChanged,
                'old_diskon'     => $oldDiskon,
                'new_diskon'     => $newDiskon,
                'diskon_changed' => $diskonChanged,
                'old_netto'      => $oldNetto,
                'new_netto'      => $newNetto,
                'notes'          => $notesVal,
                'status'         => $status,
            ];
        }

        return $preview;
    }

    /**
     * Import Harga OTR and Diskon from an Excel spreadsheet.
     * If user is Manager / Admin, they can choose to auto-approve directly or create pending entries.
     * If user is Staff, all revisions are created as pending approval.
     */
    public function importFromExcel(UploadedFile $file, User $user, ?string $importNotes = null, bool $autoApprove = false): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $batchId = 'BATCH-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -4));
        $isManagerOrAdmin = $user->isManager() || $user->isAdmin();
        $shouldApproveImmediately = $isManagerOrAdmin && $autoApprove;

        $stats = [
            'total_rows'      => 0,
            'updated_count'   => 0,
            'pending_count'   => 0,
            'approved_count'  => 0,
            'skipped_count'   => 0,
            'batch_id'        => $batchId,
            'errors'          => [],
        ];

        DB::beginTransaction();
        try {
            // Find header row (either row 4 or first row with "Harga OTR")
            $startRow = 5;
            for ($r = 1; $r <= min(10, $highestRow); $r++) {
                $val = strtolower((string)$sheet->getCell("D{$r}")->getValue());
                if (str_contains($val, 'harga otr') || str_contains($val, 'otr')) {
                    $startRow = $r + 1;
                    break;
                }
            }

            for ($row = $startRow; $row <= $highestRow; $row++) {
                $idVal       = trim((string)$sheet->getCell("A{$row}")->getValue());
                $brandVal    = trim((string)$sheet->getCell("B{$row}")->getValue());
                $nameVal     = trim((string)$sheet->getCell("C{$row}")->getValue());
                $rawOtrVal   = $sheet->getCell("D{$row}")->getValue();
                $rawDiskonVal= $sheet->getCell("E{$row}")->getValue();
                $notesVal    = trim((string)$sheet->getCell("F{$row}")->getValue());

                // Skip blank rows
                if (!$idVal && !$nameVal) {
                    continue;
                }

                $stats['total_rows']++;

                // Lookup vehicle type: first by ID, fallback by exact name and brand
                $vehicleType = null;
                if ($idVal && is_numeric($idVal)) {
                    $vehicleType = CostingVehicleType::find((int)$idVal);
                }
                if (!$vehicleType && $nameVal) {
                    $query = CostingVehicleType::where('name', $nameVal);
                    if ($brandVal) {
                        $query->where('brand_name', $brandVal);
                    }
                    $vehicleType = $query->first();
                }

                if (!$vehicleType) {
                    $stats['skipped_count']++;
                    $stats['errors'][] = "Row {$row}: Vehicle type '{$nameVal}' not found in database.";
                    continue;
                }

                // Clean numeric values
                $newOtr = $this->cleanNumeric($rawOtrVal);
                $newDiskon = $this->cleanNumeric($rawDiskonVal);

                $oldOtr = (float)$vehicleType->harga_otr;
                $oldDiskon = (float)$vehicleType->diskon;

                // If no change, skip
                if (abs($newOtr - $oldOtr) < 0.01 && abs($newDiskon - $oldDiskon) < 0.01) {
                    $stats['skipped_count']++;
                    continue;
                }

                $combinedNotes = $notesVal ? ($importNotes ? "{$importNotes} | {$notesVal}" : $notesVal) : $importNotes;

                // If auto-approved (manager action)
                if ($shouldApproveImmediately) {
                    $vehicleType->update([
                        'harga_otr' => $newOtr,
                        'diskon'    => $newDiskon,
                    ]);

                    CostingVehicleTypePriceHistory::create([
                        'costing_vehicle_type_id' => $vehicleType->id,
                        'old_harga_otr'           => $oldOtr,
                        'new_harga_otr'           => $newOtr,
                        'old_diskon'              => $oldDiskon,
                        'new_diskon'              => $newDiskon,
                        'status'                  => 'approved',
                        'source'                  => 'excel_import',
                        'batch_id'                => $batchId,
                        'requested_by'            => $user->id,
                        'approved_by'             => $user->id,
                        'approved_at'             => now(),
                        'notes'                   => $combinedNotes,
                    ]);

                    $stats['approved_count']++;
                    $stats['updated_count']++;
                } else {
                    // Queue as pending approval
                    // Check if an existing pending history exists for this model, and update or replace it
                    $pending = CostingVehicleTypePriceHistory::where('costing_vehicle_type_id', $vehicleType->id)
                        ->where('status', 'pending')
                        ->first();

                    if ($pending) {
                        $pending->update([
                            'new_harga_otr' => $newOtr,
                            'new_diskon'    => $newDiskon,
                            'batch_id'      => $batchId,
                            'requested_by'  => $user->id,
                            'notes'         => $combinedNotes,
                            'created_at'    => now(),
                        ]);
                    } else {
                        CostingVehicleTypePriceHistory::create([
                            'costing_vehicle_type_id' => $vehicleType->id,
                            'old_harga_otr'           => $oldOtr,
                            'new_harga_otr'           => $newOtr,
                            'old_diskon'              => $oldDiskon,
                            'new_diskon'              => $newDiskon,
                            'status'                  => 'pending',
                            'source'                  => 'excel_import',
                            'batch_id'                => $batchId,
                            'requested_by'            => $user->id,
                            'notes'                   => $combinedNotes,
                        ]);
                    }

                    $stats['pending_count']++;
                    $stats['updated_count']++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $stats;
    }

    /**
     * Record a manual price/diskon change from web form.
     */
    public function recordManualChange(
        CostingVehicleType $vehicleType,
        float $newOtr,
        float $newDiskon,
        ?string $notes,
        User $user
    ): CostingVehicleTypePriceHistory {
        $oldOtr = (float)$vehicleType->harga_otr;
        $oldDiskon = (float)$vehicleType->diskon;
        $isManagerOrAdmin = $user->isManager() || $user->isAdmin();

        if ($isManagerOrAdmin) {
            // Apply immediately
            $vehicleType->update([
                'harga_otr' => $newOtr,
                'diskon'    => $newDiskon,
            ]);

            return CostingVehicleTypePriceHistory::create([
                'costing_vehicle_type_id' => $vehicleType->id,
                'old_harga_otr'           => $oldOtr,
                'new_harga_otr'           => $newOtr,
                'old_diskon'              => $oldDiskon,
                'new_diskon'              => $newDiskon,
                'status'                  => 'approved',
                'source'                  => 'manual_edit',
                'requested_by'            => $user->id,
                'approved_by'             => $user->id,
                'approved_at'             => now(),
                'notes'                   => $notes,
            ]);
        }

        // Staff request: save as pending approval
        $pending = CostingVehicleTypePriceHistory::where('costing_vehicle_type_id', $vehicleType->id)
            ->where('status', 'pending')
            ->first();

        if ($pending) {
            $pending->update([
                'new_harga_otr' => $newOtr,
                'new_diskon'    => $newDiskon,
                'notes'         => $notes,
                'requested_by'  => $user->id,
                'created_at'    => now(),
            ]);
            return $pending;
        }

        return CostingVehicleTypePriceHistory::create([
            'costing_vehicle_type_id' => $vehicleType->id,
            'old_harga_otr'           => $oldOtr,
            'new_harga_otr'           => $newOtr,
            'old_diskon'              => $oldDiskon,
            'new_diskon'              => $newDiskon,
            'status'                  => 'pending',
            'source'                  => 'manual_edit',
            'requested_by'            => $user->id,
            'notes'                   => $notes,
        ]);
    }

    /**
     * Approve a pending price revision (Manager / Admin only).
     */
    public function approveHistory(CostingVehicleTypePriceHistory $history, User $user, ?string $notes = null): void
    {
        if ($history->status === 'approved') {
            return;
        }

        DB::transaction(function () use ($history, $user, $notes) {
            $type = $history->vehicleType;
            if ($type) {
                $type->update([
                    'harga_otr' => $history->new_harga_otr,
                    'diskon'    => $history->new_diskon,
                ]);
            }

            $history->update([
                'status'      => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'notes'       => $notes ?: $history->notes,
            ]);
        });
    }

    /**
     * Reject a pending price revision (Manager / Admin only).
     */
    public function rejectHistory(CostingVehicleTypePriceHistory $history, User $user, ?string $reason = null): void
    {
        $history->update([
            'status'           => 'rejected',
            'approved_by'      => $user->id,
            'approved_at'      => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Bulk approve multiple pending histories.
     * Can be called with (array $historyIds, User $user) or simply (User $user) to approve all pending.
     */
    public function bulkApprove(array|User $historyIdsOrUser, ?User $user = null): int
    {
        if ($historyIdsOrUser instanceof User) {
            $user = $historyIdsOrUser;
            $historyIds = [];
        } else {
            $historyIds = (array) $historyIdsOrUser;
        }

        $query = CostingVehicleTypePriceHistory::where('status', 'pending');
        if (!empty($historyIds)) {
            $query->whereIn('id', $historyIds);
        }
        $histories = $query->get();

        $count = 0;
        foreach ($histories as $h) {
            $this->approveHistory($h, $user);
            $count++;
        }

        return $count;
    }

    /**
     * Clean numeric currency input from Excel or user input.
     */
    private function cleanNumeric(mixed $val): float
    {
        if ($val === null || $val === '') {
            return 0.0;
        }
        if (is_numeric($val)) {
            return max(0, (float)$val);
        }
        // Remove 'Rp', commas, periods if standard separator, spaces
        $clean = preg_replace('/[^0-9]/', '', (string)$val);
        return $clean ? (float)$clean : 0.0;
    }
}
