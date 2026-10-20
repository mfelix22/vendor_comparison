@extends('layouts.app')

@section('title', 'Costing ' . $costing->costing_number . ' – Excel Preview')
@section('main_container_class', 'container-fluid px-3 px-xl-4')

@push('styles')
<style>
    /* Excel Workbook Window Styling */
    .excel-window {
        background-color: #ffffff;
        border: 1px solid #d4d4d4;
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Excel Ribbon / Formula Bar Header */
    .excel-topbar {
        background: #f3f2f1;
        border-bottom: 1px solid #e1dfdd;
        padding: 8px 14px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .excel-app-tag {
        background: #107c41;
        color: #fff;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .excel-formula-container {
        display: flex;
        align-items: center;
        background: #fff;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        flex: 1;
        height: 32px;
        overflow: hidden;
    }

    .excel-namebox {
        width: 65px;
        border: none;
        border-right: 1px solid #e5e7eb;
        text-align: center;
        font-weight: 600;
        font-size: 0.85rem;
        color: #1f2937;
        background: #fafafa;
        height: 100%;
        line-height: 30px;
        user-select: none;
    }

    .excel-fx-icon {
        padding: 0 10px;
        font-size: 0.85rem;
        font-style: italic;
        font-weight: 700;
        color: #6b7280;
        user-select: none;
    }

    .excel-formula-input {
        flex: 1;
        border: none;
        outline: none;
        padding: 0 8px;
        font-size: 0.85rem;
        font-family: 'Consolas', 'Courier New', monospace;
        color: #111827;
        background: transparent;
        height: 100%;
    }

    /* Excel Sheet Tabs Bar */
    .excel-tabs-bar {
        background: #f3f2f1;
        border-bottom: 1px solid #d1d5db;
        padding: 6px 14px 0 14px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .excel-tab-btn {
        background: #e5e7eb;
        border: 1px solid #d1d5db;
        border-bottom: none;
        border-radius: 6px 6px 0 0;
        padding: 7px 18px;
        font-size: 0.84rem;
        font-weight: 600;
        color: #4b5563;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s ease;
        position: relative;
        bottom: -1px;
    }

    .excel-tab-btn:hover {
        background: #f9fafb;
        color: #111827;
    }

    .excel-tab-btn.active {
        background: #ffffff;
        color: #107c41;
        border-color: #d1d5db;
        border-bottom: 2px solid #ffffff;
        font-weight: 700;
        box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.04);
    }

    .excel-tab-btn.active::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: #107c41;
        border-radius: 3px 3px 0 0;
    }

    .excel-tab-badge {
        font-size: 0.7rem;
        padding: 2px 6px;
        border-radius: 10px;
        font-weight: 500;
    }

    .excel-tab-btn.active .excel-tab-badge {
        background: #dcfce7;
        color: #15803d;
    }

    .excel-tab-btn:not(.active) .excel-tab-badge {
        background: #d1d5db;
        color: #4b5563;
    }

    /* Excel Grid Table */
    .excel-grid-container {
        overflow-x: auto;
        max-height: 720px;
        background: #ffffff;
        position: relative;
    }

    .excel-table {
        border-collapse: collapse;
        width: 100%;
        table-layout: fixed;
        font-size: 0.85rem;
    }

    /* Headers: Row & Column indicators */
    .excel-corner {
        width: 44px;
        min-width: 44px;
        max-width: 44px;
        background: #f3f2f1;
        border: 1px solid #d4d4d4;
        position: sticky;
        top: 0;
        left: 0;
        z-index: 10;
        text-align: center;
        height: 28px;
    }

    .excel-col-hdr {
        background: #f3f2f1;
        border: 1px solid #d4d4d4;
        color: #605e5c;
        font-weight: 600;
        font-size: 0.78rem;
        text-align: center;
        height: 28px;
        position: sticky;
        top: 0;
        z-index: 9;
        user-select: none;
    }

    .excel-col-hdr.highlighted {
        background: #e1dfdd;
        color: #107c41;
        border-bottom: 2px solid #107c41;
    }

    .excel-row-hdr {
        width: 44px;
        min-width: 44px;
        max-width: 44px;
        background: #f3f2f1;
        border: 1px solid #d4d4d4;
        color: #605e5c;
        font-weight: 600;
        font-size: 0.78rem;
        text-align: center;
        position: sticky;
        left: 0;
        z-index: 8;
        user-select: none;
        height: 26px;
    }

    .excel-row-hdr.highlighted {
        background: #e1dfdd;
        color: #107c41;
        border-right: 2px solid #107c41;
    }

    /* Cells */
    .excel-cell {
        border: 1px solid #d4d4d4;
        padding: 4px 8px;
        height: 26px;
        vertical-align: middle;
        white-space: nowrap;
        background: #ffffff;
        cursor: cell;
        position: relative;
        font-family: 'Calibri', 'Segoe UI', Arial, sans-serif;
    }

    .excel-cell:hover {
        outline: 1px dashed #107c41;
    }

    .excel-cell.excel-cell-active {
        outline: 2px solid #107c41 !important;
        outline-offset: -1px;
        z-index: 2;
    }

    .excel-cell.excel-cell-active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        right: -2px;
        width: 6px;
        height: 6px;
        background: #107c41;
        z-index: 3;
    }

    /* Sheet Custom Styles matching COSTING SAMPEL.xlsx */
    .cell-yellow {
        background-color: #ffff00 !important;
        font-family: 'Times New Roman', Times, serif !important;
        font-weight: bold !important;
    }

    .cell-cyan {
        background-color: #66ffff !important;
        font-family: 'Times New Roman', Times, serif !important;
        font-weight: bold !important;
    }

    .cell-times-bold {
        font-family: 'Times New Roman', Times, serif !important;
        font-weight: bold !important;
    }

    .cell-times {
        font-family: 'Times New Roman', Times, serif !important;
    }

    .cell-calibri-bold {
        font-family: 'Calibri', Arial, sans-serif !important;
        font-weight: bold !important;
    }

    .cell-calibri {
        font-family: 'Calibri', Arial, sans-serif !important;
    }

    .cell-center {
        text-align: center;
    }

    .cell-right {
        text-align: right;
    }

    .cell-left {
        text-align: left;
    }

    .cell-no-border {
        border: none !important;
    }

    /* Excel Status Bar */
    .excel-statusbar {
        background: #107c41;
        color: #ffffff;
        font-size: 0.76rem;
        padding: 5px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        user-select: none;
    }

    @media print {
        @page {
            size: auto;
            margin: 8mm 10mm;
        }

        body {
            background: #ffffff !important;
            color: #000000 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Hide all UI elements outside the excel content */
        nav,
        footer,
        .navbar,
        .no-print,
        .excel-topbar,
        .excel-tabs-bar,
        .excel-statusbar,
        .btn,
        .alert {
            display: none !important;
        }

        /* Hide Excel coordinates (column letters A, B, C & row numbers 1, 2, 3) */
        .excel-table thead,
        .excel-corner,
        .excel-col-hdr,
        .excel-row-hdr,
        .excel-table colgroup {
            display: none !important;
        }

        main {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
            width: 100% !important;
        }

        .excel-window {
            box-shadow: none !important;
            border: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Always include both sheets in print: Page 1 = BU AB, Page 2 = SMD */
        .excel-grid-container,
        #gridBuAb,
        #gridSmd {
            display: block !important;
            max-height: none !important;
            overflow: visible !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        #gridSmd {
            page-break-before: always !important;
            break-before: page !important;
        }

        .excel-table {
            width: 100% !important;
            table-layout: auto !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
            empty-cells: show !important;
            border: none !important;
            font-size: 0.82rem !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .excel-table tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            background-color: transparent !important;
            height: auto !important;
        }

        /* Row 3 (Brand): Top edge of the costing table - gets complete top border */
        .excel-table tbody tr:nth-child(3) td.excel-cell {
            border-top: 1px solid #000000 !important;
            border-right: none !important;
            border-left: 1px solid #000000 !important;
            border-bottom: 1px solid #000000 !important;
            padding: 3px 8px !important;
            background-clip: padding-box !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            white-space: normal !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            line-height: 1.25 !important;
            box-sizing: border-box !important;
        }

        /* Rows 4 through row before Notes (Harga Unit + Aksesoris) */
        .excel-table tbody tr:nth-child(n+4):not(:last-child) td.excel-cell {
            border-top: none !important;
            border-right: none !important;
            border-left: 1px solid #000000 !important;
            border-bottom: 1px solid #000000 !important;
            padding: 3px 8px !important;
            background-clip: padding-box !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            white-space: normal !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            line-height: 1.25 !important;
            box-sizing: border-box !important;
        }

        /* Rightmost column in every costing row: closes the outer right edge */
        .excel-table tbody tr:nth-child(n+3):not(:last-child) td.excel-cell:last-child {
            border-right: 1px solid #000000 !important;
        }

        /* Column A width */
        .excel-table tbody tr:nth-child(n+3):not(:last-child) td.excel-cell[data-coord^="A"] {
            width: 30% !important;
        }

        /* White cells remain transparent */
        .excel-table tbody tr:nth-child(n+3):not(:last-child) td.excel-cell:not(.cell-yellow):not(.cell-cyan) {
            background-color: transparent !important;
        }

        /* Header yellow backgrounds (Brand and Type Kendaraan) */
        .excel-table td.cell-yellow,
        .excel-table .cell-yellow {
            background-color: #ffff00 !important;
            background-clip: padding-box !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Header cyan backgrounds (BBN and Pemakaian) */
        .excel-table td.cell-cyan,
        .excel-table .cell-cyan {
            background-color: #66ffff !important;
            background-clip: padding-box !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Row 1: Customer Name - clean text with no borders */
        .excel-table tbody tr:nth-child(1) {
            height: auto !important;
        }
        .excel-table tbody tr:nth-child(1) td {
            border: none !important;
            padding: 0 0 4px 0 !important;
            font-size: 0.95rem !important;
            font-weight: bold !important;
            background-color: transparent !important;
        }

        /* Row 2: Spacing gap above the table - clean space with no borders */
        .excel-table tbody tr:nth-child(2) {
            height: 16px !important;
        }
        .excel-table tbody tr:nth-child(2) td {
            border: none !important;
            height: 16px !important;
            padding: 0 !important;
            background-color: transparent !important;
        }

        /* Notes row at the bottom - clean text with no borders */
        .excel-table tbody tr:last-child td,
        .excel-cell.cell-no-border {
            border: none !important;
            padding-top: 14px !important;
            font-weight: bold !important;
            background-color: transparent !important;
        }

        /* Remove active cell focus outline and green indicator */
        .excel-cell.excel-cell-active {
            outline: none !important;
        }

        .excel-cell.excel-cell-active::after {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')

    {{-- Actions Bar --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 no-print">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('costing.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h4 class="fw-bold mb-0">Costing Details</h4>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                        <i class="bi bi-file-earmark-excel me-1"></i>Excel Grid View
                    </span>
                    @if ($costing->isTender())
                        <span class="badge bg-warning text-dark border border-warning-subtle px-2 py-1">
                            <i class="bi bi-award me-1"></i>Tender FPH
                        </span>
                        @if ($costing->isPendingApproval())
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                <i class="bi bi-clock me-1"></i>Pending Manager Approval
                            </span>
                        @elseif ($costing->isApproved())
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                <i class="bi bi-check-circle me-1"></i>Approved
                            </span>
                        @elseif ($costing->isRejected())
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                <i class="bi bi-x-circle me-1"></i>Rejected
                            </span>
                        @endif
                    @else
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                            <i class="bi bi-shop me-1"></i>Retail FPH
                        </span>
                    @endif
                </div>
                <div class="text-muted small mt-1">
                    <span class="fw-semibold text-primary">{{ $costing->costing_number }}</span>
                    &middot; Customer: <span class="fw-semibold text-dark">{{ $costing->customer_name }}</span>
                    @if ($costing->smd)
                        &middot; SMD: <span class="fw-semibold text-dark">{{ $costing->smd }}</span>
                    @endif
                    @if ($costing->masa_sewa)
                        &middot; Masa Sewa: <span class="fw-semibold text-dark">{{ $costing->masa_sewa }}</span>
                    @endif
                    @if ($costing->request_delivery_date)
                        &middot; Delivery: <span class="fw-semibold text-dark">{{ $costing->request_delivery_date->format('d/m/Y') }}</span>
                    @endif
                    &middot; {{ $costing->created_at->format('d/m/Y') }}
                    &middot; Created by <span class="fw-semibold text-dark">{{ $costing->creator->name ?? '—' }}</span>
                </div>
            </div>
        </div>
        @php
            $isPending = $costing->isPendingApproval();
            $isRejected = $costing->isTender() && $costing->isRejected();
            $isApproved = $costing->isTender() && $costing->isApproved();
            $canPrintDownload = !$isPending && !$isRejected;
            $canEdit = !$costing->isTender() || $costing->isRejected();
        @endphp
        <div class="d-flex gap-2">
            @if ($canPrintDownload)
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i>Print
                </button>
                <a href="{{ route('costing.export', $costing) }}" class="btn btn-success btn-sm fw-semibold">
                    <i class="bi bi-download me-1"></i>Download Excel (.xlsx)
                </a>
            @else
                <button type="button" class="btn btn-outline-secondary btn-sm text-muted" disabled 
                    title="{{ $isPending ? 'Cannot print while awaiting Manager approval' : 'Cannot print rejected costing' }}" 
                    style="cursor: not-allowed; opacity: 0.55;">
                    <i class="bi bi-printer me-1"></i>Print
                </button>
                <button type="button" class="btn btn-outline-success btn-sm text-muted" disabled 
                    title="{{ $isPending ? 'Cannot download Excel while awaiting Manager approval' : 'Cannot download rejected costing' }}" 
                    style="cursor: not-allowed; opacity: 0.55;">
                    <i class="bi bi-download me-1"></i>Download Excel (.xlsx)
                </button>
            @endif

            @if ($canEdit)
                <a href="{{ route('costing.edit', $costing) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i>Edit Sheet
                </a>
            @else
                <button type="button" class="btn btn-outline-secondary btn-sm text-muted" disabled 
                    title="{{ $isPending ? 'Editing locked while awaiting Manager approval' : 'Approved tender costing is locked' }}" 
                    style="cursor: not-allowed; opacity: 0.55;">
                    <i class="bi bi-lock-fill me-1"></i>Edit Sheet
                </button>
            @endif
        </div>
    </div>

    {{-- Tender Approval Status Card --}}
    @if ($costing->isTender())
        @php
            $isMgrOrAdmin = Auth::check() && (Auth::user()->isManager() || Auth::user()->isAdmin());
        @endphp
        @if ($costing->isPendingApproval())
            <div class="card border-warning-subtle bg-warning-subtle mb-3 no-print">
                <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill text-warning-emphasis fs-4"></i>
                        <div>
                            <div class="fw-bold text-dark">Tender Costing Review Required</div>
                            <div class="small text-muted">
                                @if ($isMgrOrAdmin)
                                    This sheet is marked as Tender FPH and requires your authorization as Manager/Admin.
                                @else
                                    This sheet is marked as Tender FPH and is currently awaiting approval by the Purchasing Manager.
                                @endif
                            </div>
                        </div>
                    </div>
                    @if ($isMgrOrAdmin)
                        <div class="d-flex gap-2">
                            <form method="POST" action="{{ route('costing.approve', $costing) }}" class="d-inline"
                                onsubmit="return confirm('Approve tender costing {{ $costing->costing_number }}?')">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm px-3 fw-semibold shadow-sm">
                                    <i class="bi bi-check-lg me-1"></i>Approve Tender
                                </button>
                            </form>
                            <button type="button" class="btn btn-outline-danger btn-sm px-3 fw-semibold shadow-sm"
                                onclick="openRejectModal()">
                                <i class="bi bi-x-lg me-1"></i>Reject...
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @elseif ($costing->isApproved())
            <div class="alert alert-success d-flex align-items-center justify-content-between py-2 px-3 mb-3 no-print">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    <div>
                        <span class="fw-bold">Tender FPH Approved</span>
                        <span class="text-muted small ms-2">
                            by <strong>{{ $costing->approver->name ?? 'Manager' }}</strong>
                            @if ($costing->approved_at)
                                on {{ $costing->approved_at->format('d M Y, H:i') }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        @elseif ($costing->isRejected())
            <div class="card border-danger-subtle bg-danger-subtle mb-3 no-print">
                <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-x-circle-fill text-danger fs-4"></i>
                        <div>
                            <div class="fw-bold text-danger">Tender FPH Rejected</div>
                            <div class="small text-dark">
                                By <strong>{{ $costing->approver->name ?? 'Manager' }}</strong>
                                @if ($costing->approved_at)
                                    on {{ $costing->approved_at->format('d M Y, H:i') }}
                                @endif
                                &middot; Reason: <em>"{{ $costing->rejection_reason }}"</em>
                            </div>
                        </div>
                    </div>
                    @if ($isMgrOrAdmin)
                        <form method="POST" action="{{ route('costing.approve', $costing) }}" class="d-inline"
                            onsubmit="return confirm('Re-approve tender costing {{ $costing->costing_number }}?')">
                            @csrf
                            <button type="submit" class="btn btn-outline-success btn-sm px-3 fw-semibold">
                                <i class="bi bi-arrow-repeat me-1"></i>Re-approve
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endif
    @endif

    @php
        // Auto-sort vehicles by Pulau -> Provinsi -> BBN -> sort_order so grouping & horizontal cell merging are cohesive
        $vehicles = $costing->vehicles->sortBy([
            ['pulau', 'asc'],
            ['provinsi', 'asc'],
            ['bbn', 'asc'],
            ['sort_order', 'asc'],
        ])->values();
        $colCount = count($vehicles);
        $colLetters = [];
        for ($i = 0; $i <= $colCount; $i++) {
            $colLetters[] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
        }
        $lastCol = end($colLetters);

        // Group adjacent columns for merged PULAU and PROVINSI displays
        $pulauGroups = [];
        $currPulau = null;
        $startColP = null;
        $spanP = 0;
        foreach ($vehicles as $idx => $v) {
            $cNum = $idx + 2;
            $val = strtoupper(trim($v->pulau ?: ''));
            if ($val === $currPulau) {
                $spanP++;
            } else {
                if ($currPulau !== null) {
                    $pulauGroups[] = ['val' => $currPulau, 'start' => $startColP, 'span' => $spanP];
                }
                $currPulau = $val;
                $startColP = $cNum;
                $spanP = 1;
            }
        }
        if ($currPulau !== null) {
            $pulauGroups[] = ['val' => $currPulau, 'start' => $startColP, 'span' => $spanP];
        }

        $provGroups = [];
        $currProv = null;
        $currProvPulau = null;
        $startColPr = null;
        $spanPr = 0;
        foreach ($vehicles as $idx => $v) {
            $cNum = $idx + 2;
            $val = strtoupper(trim($v->provinsi ?: ''));
            $pVal = strtoupper(trim($v->pulau ?: ''));
            if ($val === $currProv) {
                $spanPr++;
            } else {
                if ($currProv !== null) {
                    $provGroups[] = ['val' => $currProv, 'pulau' => $currProvPulau, 'start' => $startColPr, 'span' => $spanPr];
                }
                $currProv = $val;
                $currProvPulau = $pVal;
                $startColPr = $cNum;
                $spanPr = 1;
            }
        }
        if ($currProv !== null) {
            $provGroups[] = ['val' => $currProv, 'pulau' => $currProvPulau, 'start' => $startColPr, 'span' => $spanPr];
        }

        // Regional theme with per-province color inheritance
        $regionalTheme = [
            'JAWA' => [
                'main' => 'c5e0b4', // Soft Forest Mint Green
                'provinces' => [
                    'DKI JAKARTA'                => 'e2efda', // Light Mint
                    'JAWA BARAT'                 => 'd5e8d4', // Pale Celadon
                    'JAWA TENGAH'                => 'e2f0cb', // Lime Pasture
                    'JAWA TIMUR'                 => 'd0ead4', // Pale Mint Sage
                    'BANTEN'                     => 'cdeccd', // Light Meadow
                    'DAERAH ISTIMEWA YOGYAKARTA' => 'e5eed0', // Light Tea Green
                ],
            ],
            'SUMATERA' => [
                'main' => 'f8cbad', // Soft Amber Peach
                'provinces' => [
                    'ACEH'             => 'fce4d6', // Pale Peach
                    'SUMATERA UTARA'   => 'fce8d5', // Soft Apricot
                    'SUMATERA BARAT'   => 'fbe5c8', // Warm Bisque
                    'RIAU'             => 'fdebd0', // Creamy Sand
                    'KEPULAUAN RIAU'   => 'fce1c9', // Light Coral Peach
                    'JAMBI'            => 'f8e0c8', // Muted Apricot
                    'SUMATERA SELATAN' => 'fadbd8', // Soft Blush Peach
                    'BANGKA BELITUNG'  => 'fdebd2', // Warm Gold Cream
                    'BENGKULU'         => 'fbe8da', // Light Bisque
                    'LAMPUNG'          => 'f5d8c3', // Light Terracotta
                ],
            ],
            'KALIMANTAN' => [
                'main' => 'bdd7ee', // Soft Sky Blue
                'provinces' => [
                    'KALIMANTAN BARAT'   => 'ddebf7', // Light Ice Blue
                    'KALIMANTAN TENGAH'  => 'd2e5f5', // Soft Sky
                    'KALIMANTAN SELATAN' => 'e1edf8', // Pale Azure
                    'KALIMANTAN TIMUR'   => 'c9e0f2', // Powder Blue
                    'KALIMANTAN UTARA'   => 'd6e8f7', // Breeze Blue
                ],
            ],
            'SULAWESI' => [
                'main' => 'ccc0da', // Soft Lavender Violet
                'provinces' => [
                    'SULAWESI UTARA'    => 'e6dfec', // Pale Lavender
                    'GORONTALO'         => 'e1d5ea', // Lilac Mist
                    'SULAWESI TENGAH'   => 'ede5f3', // Soft Violet
                    'SULAWESI BARAT'    => 'dfd3eb', // Heather
                    'SULAWESI SELATAN'  => 'e4daee', // Light Orchid
                    'SULAWESI TENGGARA' => 'ebe2f5', // Pastel Lilac
                ],
            ],
            'BALI & NUSA TENGGARA' => [
                'main' => 'f4b084', // Soft Coral Rose
                'provinces' => [
                    'BALI'                => 'fce4d6', // Soft Coral
                    'NUSA TENGGARA BARAT' => 'fadbd8', // Pale Rose
                    'NUSA TENGGARA TIMUR' => 'f8d7da', // Pastel Salmon
                ],
            ],
            'PAPUA & MALUKU' => [
                'main' => 'a2d9ce', // Soft Seafoam Teal
                'provinces' => [
                    'MALUKU'            => 'd1f2eb', // Light Mint Aqua
                    'MALUKU UTARA'      => 'e8f8f5', // Pale Aqua
                    'PAPUA'             => 'd4efdf', // Soft Seafoam
                    'PAPUA BARAT'       => 'e5f9e0', // Pale Sage Aqua
                    'PAPUA BARAT DAYA'  => 'd0ece7', // Light Aqua Breeze
                    'PAPUA PEGUNUNGAN'  => 'e1f5fe', // Crisp Aqua
                    'PAPUA SELATAN'     => 'e0f2f1', // Pale Teal
                    'PAPUA TENGAH'      => 'dcecc9', // Pastel Aqua Grass
                ],
            ],
        ];

        $getPulauColor = function ($pulau) use ($regionalTheme) {
            $p = strtoupper(trim($pulau ?? ''));
            $hex = $regionalTheme[$p]['main'] ?? 'efefef';
            return '#' . $hex;
        };

        $getProvColor = function ($pulau, $provinsi) use ($regionalTheme) {
            $p = strtoupper(trim($pulau ?? ''));
            $pr = strtoupper(trim($provinsi ?? ''));
            $hex = $regionalTheme[$p]['provinces'][$pr] ?? ($regionalTheme[$p]['main'] ?? 'f8f8f8');
            return '#' . $hex;
        };
    @endphp

    {{-- Excel Workbook Container --}}
    <div class="excel-window mb-4">
        
        {{-- Excel Ribbon & Formula Bar --}}
        <div class="excel-topbar no-print">
            <div class="excel-app-tag">
                <i class="bi bi-file-earmark-excel-fill"></i> EXCEL PREVIEW
            </div>
            <div class="excel-formula-container">
                <div class="excel-namebox" id="cellCoordDisplay">A1</div>
                <div class="excel-fx-icon">fx</div>
                <input type="text" class="excel-formula-input" id="cellFormulaDisplay" value="{{ $costing->customer_name }}" readonly>
            </div>
            <div class="text-muted small d-none d-md-block" style="font-size: 0.78rem;">
                <i class="bi bi-cursor-fill me-1 text-success"></i>Click any cell to view formula & coordinate
            </div>
        </div>

        {{-- Excel Sheet Tabs (BU AB vs SMD) --}}
        <div class="excel-tabs-bar no-print">
            <button type="button" class="excel-tab-btn active" id="tabBtnBuAb" onclick="switchSheet('BU_AB')">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>BU AB
                <span class="excel-tab-badge">With Diskon & Nett</span>
            </button>
            <button type="button" class="excel-tab-btn" id="tabBtnSmd" onclick="switchSheet('SMD')">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>SMD
                <span class="excel-tab-badge">OTR + Estimasi (No Diskon)</span>
            </button>
        </div>

        {{-- ======================================================== --}}
        {{-- SHEET 1: BU AB GRID                                      --}}
        {{-- ======================================================== --}}
        <div class="excel-grid-container" id="gridBuAb">
            <table class="excel-table">
                <colgroup>
                    <col style="width: 44px;">
                    <col style="width: 280px;">
                    @foreach ($vehicles as $v)
                        <col style="width: 230px;">
                    @endforeach
                </colgroup>
                <thead>
                    <tr>
                        <th class="excel-corner"></th>
                        <th class="excel-col-hdr" data-col="A">A</th>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <th class="excel-col-hdr" data-col="{{ $colLet }}">{{ $colLet }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    {{-- Row 1: Customer Name (Merged Across Full Table Width) --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="1">1</td>
                        <td class="excel-cell cell-calibri-bold cell-left" colspan="{{ count($vehicles) + 1 }}" data-coord="A1" data-val="{{ $costing->customer_name }}">
                            {{ $costing->customer_name }}
                        </td>
                    </tr>

                    {{-- Row 2: SMD / Masa Sewa / Delivery Date (Merged Across Full Table Width) --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="2">2</td>
                        <td class="excel-cell text-muted small cell-left" colspan="{{ count($vehicles) + 1 }}" data-coord="A2" data-val="@if($costing->smd)SMD: {{ $costing->smd }} @endif @if($costing->masa_sewa)| Masa Sewa: {{ $costing->masa_sewa }} @endif @if($costing->request_delivery_date)| Delivery: {{ $costing->request_delivery_date->format('d/m/Y') }}@endif">
                            @if($costing->smd)
                                <span class="fw-semibold text-dark">SMD:</span> {{ $costing->smd }}
                            @endif
                            @if($costing->masa_sewa)
                                @if($costing->smd) &nbsp;&middot;&nbsp; @endif
                                <span class="fw-semibold text-dark">Masa Sewa:</span> {{ $costing->masa_sewa }}
                            @endif
                            @if($costing->request_delivery_date)
                                @if($costing->smd || $costing->masa_sewa) &nbsp;&middot;&nbsp; @endif
                                <span class="fw-semibold text-dark">Delivery:</span> {{ $costing->request_delivery_date->format('d/m/Y') }}
                            @endif
                        </td>
                    </tr>

                    {{-- Row 3: PULAU (Color-Coded by Regional Island Family) --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="3">3</td>
                        <td class="excel-cell cell-calibri-bold cell-left bg-light" data-coord="A3" data-val="PULAU">
                            PULAU
                        </td>
                        @foreach ($pulauGroups as $grp)
                            @php
                                $sLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($grp['start']);
                                $pBg = $getPulauColor($grp['val']);
                            @endphp
                            <td class="excel-cell cell-calibri-bold cell-center" colspan="{{ $grp['span'] }}" data-coord="{{ $sLet }}3" data-val="{{ $grp['val'] }}" style="background-color: {{ $pBg }} !important;">
                                {{ $grp['val'] ?: '—' }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 4: PROVINSI (Coordinating Lighter Pastel of Island) --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="4">4</td>
                        <td class="excel-cell cell-calibri-bold cell-left bg-light" data-coord="A4" data-val="PROVINSI">
                            PROVINSI
                        </td>
                        @foreach ($provGroups as $grp)
                            @php
                                $sLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($grp['start']);
                                $prBg = $getProvColor($grp['pulau'] ?? '', $grp['val'] ?? '');
                            @endphp
                            <td class="excel-cell cell-calibri-bold cell-center" colspan="{{ $grp['span'] }}" data-coord="{{ $sLet }}4" data-val="{{ $grp['val'] }}" style="background-color: {{ $prBg }} !important;">
                                {{ $grp['val'] ?: '—' }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 5: Brand --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="5">5</td>
                        <td class="excel-cell cell-yellow cell-left" data-coord="A5" data-val="Brand :">
                            Brand :
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-yellow cell-center" data-coord="{{ $colLet }}5" data-val="{{ $v->brand }}">
                                {{ $v->brand }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 6: Type Kendaraan --}}
                    <tr style="height: 34px;">
                        <td class="excel-row-hdr" data-row="6">6</td>
                        <td class="excel-cell cell-yellow cell-left" data-coord="A6" data-val="Type Kendaraan :">
                            Type Kendaraan :
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-yellow cell-center text-wrap" data-coord="{{ $colLet }}6" data-val="{{ $v->vehicle_type }}">
                                {{ $v->vehicle_type }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 7: BBN --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="7">7</td>
                        <td class="excel-cell cell-cyan cell-left" data-coord="A7" data-val="BBN :">
                            BBN :
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-cyan cell-center" data-coord="{{ $colLet }}7" data-val="{{ $v->bbn }}">
                                {{ $v->bbn }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 8: Pemakaian --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="8">8</td>
                        <td class="excel-cell cell-cyan cell-left" data-coord="A8" data-val="Pemakaian :">
                            Pemakaian :
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-cyan cell-center" data-coord="{{ $colLet }}8" data-val="{{ $v->pemakaian }}">
                                {{ $v->pemakaian }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 9: Harga OTR --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="9">9</td>
                        <td class="excel-cell cell-times-bold cell-left" data-coord="A9" data-val="Harga OTR">
                            Harga OTR
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-times-bold cell-right" data-coord="{{ $colLet }}9" data-val="{{ (float)$v->harga_otr }}">
                                {{ number_format($v->harga_otr, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 10: Diskon --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="10">10</td>
                        <td class="excel-cell cell-times cell-left" data-coord="A10" data-val="Diskon">
                            Diskon
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-calibri-bold cell-right {{ $v->diskon > 0 ? 'text-danger' : '' }}" data-coord="{{ $colLet }}10" data-val="{{ (float)$v->diskon }}">
                                {{ number_format($v->diskon, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 11: Harga Nett --}}
                    <tr style="background-color: #f0fdf4;">
                        <td class="excel-row-hdr" data-row="11">11</td>
                        <td class="excel-cell cell-times-bold cell-left text-success" data-coord="A11" data-val="Harga Nett">
                            Harga Nett
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php 
                                $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); 
                                $nett = (float)$v->harga_otr - (float)$v->diskon;
                            @endphp
                            <td class="excel-cell cell-calibri-bold cell-right text-success" data-coord="{{ $colLet }}11" data-formula="={{ $colLet }}9-{{ $colLet }}10" data-val="{{ $nett }}">
                                {{ number_format($nett, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 12: Blank --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="12">12</td>
                        <td class="excel-cell" data-coord="A12" data-val=""></td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell" data-coord="{{ $colLet }}12" data-val=""></td>
                        @endforeach
                    </tr>

                    {{-- Row 13: Estimasi kenaikan harga --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="13">13</td>
                        <td class="excel-cell cell-times-bold cell-left" data-coord="A13" data-val="Estimasi kenaikan harga">
                            Estimasi kenaikan harga
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-calibri-bold cell-right" data-coord="{{ $colLet }}13" data-val="{{ (float)$v->estimasi_kenaikan }}">
                                {{ number_format($v->estimasi_kenaikan, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 14: Blank --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="14">14</td>
                        <td class="excel-cell" data-coord="A14" data-val=""></td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell" data-coord="{{ $colLet }}14" data-val=""></td>
                        @endforeach
                    </tr>

                    {{-- Row 15: Harga setelah estimasi kenaikan --}}
                    <tr style="background-color: #fefce8;">
                        <td class="excel-row-hdr" data-row="15">15</td>
                        <td class="excel-cell cell-times-bold cell-left" data-coord="A15" data-val="Harga setelah estimasi kenaikan">
                            Harga setelah estimasi kenaikan
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php 
                                $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); 
                                $nett = (float)$v->harga_otr - (float)$v->diskon;
                                $afterIncrease = $nett + (float)$v->estimasi_kenaikan;
                            @endphp
                            <td class="excel-cell cell-calibri-bold cell-right" data-coord="{{ $colLet }}15" data-formula="={{ $colLet }}11+{{ $colLet }}13" data-val="{{ $afterIncrease }}">
                                {{ number_format($afterIncrease, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 16: Blank --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="16">16</td>
                        <td class="excel-cell" data-coord="A16" data-val=""></td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell" data-coord="{{ $colLet }}16" data-val=""></td>
                        @endforeach
                    </tr>

                    {{-- Row 17: Tambahan : --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="17">17</td>
                        <td class="excel-cell cell-calibri cell-left fw-semibold text-primary" data-coord="A17" data-val="Tambahan : ">
                            Tambahan : 
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell" data-coord="{{ $colLet }}17" data-val=""></td>
                        @endforeach
                    </tr>

                    {{-- Accessories Rows (Row 18 onwards) --}}
                    @php 
                        $curRowBu = 18;
                        $accStartBu = 18;
                    @endphp
                    @foreach ($accNames as $accName)
                        <tr>
                            <td class="excel-row-hdr" data-row="{{ $curRowBu }}">{{ $curRowBu }}</td>
                            <td class="excel-cell cell-calibri cell-left ps-3" data-coord="A{{ $curRowBu }}" data-val="{{ $accName }}">
                                {{ $accName }}
                            </td>
                            @foreach ($vehicles as $idx => $v)
                                @php 
                                    $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); 
                                    $amt = $accRows[$accName]['amounts'][$v->id] ?? 0;
                                @endphp
                                <td class="excel-cell cell-calibri cell-right" data-coord="{{ $colLet }}{{ $curRowBu }}" data-val="{{ $amt }}">
                                    {{ number_format($amt, 0, ',', '.') }}
                                </td>
                            @endforeach
                        </tr>
                        @php $curRowBu++; @endphp
                    @endforeach

                    @php $accEndBu = max($accStartBu, $curRowBu - 1); @endphp

                    {{-- Blank Row after accessories --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="{{ $curRowBu }}">{{ $curRowBu }}</td>
                        <td class="excel-cell" data-coord="A{{ $curRowBu }}" data-val=""></td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell" data-coord="{{ $colLet }}{{ $curRowBu }}" data-val=""></td>
                        @endforeach
                    </tr>
                    @php $curRowBu++; @endphp

                    {{-- Total Acc Row --}}
                    @php $totalAccRowBu = $curRowBu; @endphp
                    <tr style="background-color: #f1f5f9;">
                        <td class="excel-row-hdr" data-row="{{ $totalAccRowBu }}">{{ $totalAccRowBu }}</td>
                        <td class="excel-cell cell-calibri cell-left fw-semibold" data-coord="A{{ $totalAccRowBu }}" data-val="Total Acc">
                            Total Acc
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php 
                                $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); 
                                $accSum = 0;
                                foreach ($accNames as $an) {
                                    $accSum += ($accRows[$an]['amounts'][$v->id] ?? 0);
                                }
                                $sumFormula = count($accNames) > 0 ? "=SUM({$colLet}{$accStartBu}:{$colLet}{$accEndBu})" : "0";
                            @endphp
                            <td class="excel-cell cell-calibri cell-right fw-semibold" data-coord="{{ $colLet }}{{ $totalAccRowBu }}" data-formula="{{ $sumFormula }}" data-val="{{ $accSum }}">
                                {{ number_format($accSum, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>
                    @php $curRowBu++; @endphp

                    {{-- Blank Row after Total Acc --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="{{ $curRowBu }}">{{ $curRowBu }}</td>
                        <td class="excel-cell" data-coord="A{{ $curRowBu }}" data-val=""></td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell" data-coord="{{ $colLet }}{{ $curRowBu }}" data-val=""></td>
                        @endforeach
                    </tr>
                    @php $curRowBu++; @endphp

                    {{-- Grand Total: Harga Unit + Aksesoris --}}
                    @php $grandTotalRowBu = $curRowBu; @endphp
                    <tr style="background-color: #ede9fe;">
                        <td class="excel-row-hdr" data-row="{{ $grandTotalRowBu }}">{{ $grandTotalRowBu }}</td>
                        <td class="excel-cell cell-calibri-bold cell-left text-primary" data-coord="A{{ $grandTotalRowBu }}" data-val="Harga Unit + Aksesoris">
                            Harga Unit + Aksesoris
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php 
                                $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); 
                                $nett = (float)$v->harga_otr - (float)$v->diskon;
                                $afterIncrease = $nett + (float)$v->estimasi_kenaikan;
                                $accSum = 0;
                                foreach ($accNames as $an) {
                                    $accSum += ($accRows[$an]['amounts'][$v->id] ?? 0);
                                }
                                $grandTotal = $afterIncrease + $accSum;
                            @endphp
                            <td class="excel-cell cell-calibri-bold cell-right text-primary" data-coord="{{ $colLet }}{{ $grandTotalRowBu }}" data-formula="={{ $colLet }}15+{{ $colLet }}{{ $totalAccRowBu }}" data-val="{{ $grandTotal }}">
                                {{ number_format($grandTotal, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>
                    @php $curRowBu += 3; @endphp

                    {{-- Notes Row --}}
                    @php 
                        $notesRowBu = $curRowBu;
                        $notesText = 'Notes :' . ($costing->notes ? ' ' . $costing->notes : '');
                    @endphp
                    <tr>
                        <td class="excel-row-hdr" data-row="{{ $notesRowBu }}">{{ $notesRowBu }}</td>
                        <td class="excel-cell cell-calibri-bold cell-left cell-no-border" colspan="{{ $colCount + 1 }}" data-coord="A{{ $notesRowBu }}" data-val="{{ $notesText }}">
                            {{ $notesText }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- ======================================================== --}}
        {{-- SHEET 2: SMD GRID (NO DISKON, NO NETT)                    --}}
        {{-- ======================================================== --}}
        <div class="excel-grid-container d-none" id="gridSmd">
            <table class="excel-table">
                <colgroup>
                    <col style="width: 44px;">
                    <col style="width: 280px;">
                    @foreach ($vehicles as $v)
                        <col style="width: 230px;">
                    @endforeach
                </colgroup>
                <thead>
                    <tr>
                        <th class="excel-corner"></th>
                        <th class="excel-col-hdr" data-col="A">A</th>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <th class="excel-col-hdr" data-col="{{ $colLet }}">{{ $colLet }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    {{-- Row 1: Customer Name (Merged Across Full Table Width) --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="1">1</td>
                        <td class="excel-cell cell-calibri-bold cell-left" colspan="{{ count($vehicles) + 1 }}" data-coord="A1" data-val="{{ $costing->customer_name }}">
                            {{ $costing->customer_name }}
                        </td>
                    </tr>

                    {{-- Row 2: SMD / Masa Sewa / Delivery Date (Merged Across Full Table Width) --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="2">2</td>
                        <td class="excel-cell text-muted small cell-left" colspan="{{ count($vehicles) + 1 }}" data-coord="A2" data-val="@if($costing->smd)SMD: {{ $costing->smd }} @endif @if($costing->masa_sewa)| Masa Sewa: {{ $costing->masa_sewa }} @endif @if($costing->request_delivery_date)| Delivery: {{ $costing->request_delivery_date->format('d/m/Y') }}@endif">
                            @if($costing->smd)
                                <span class="fw-semibold text-dark">SMD:</span> {{ $costing->smd }}
                            @endif
                            @if($costing->masa_sewa)
                                @if($costing->smd) &nbsp;&middot;&nbsp; @endif
                                <span class="fw-semibold text-dark">Masa Sewa:</span> {{ $costing->masa_sewa }}
                            @endif
                            @if($costing->request_delivery_date)
                                @if($costing->smd || $costing->masa_sewa) &nbsp;&middot;&nbsp; @endif
                                <span class="fw-semibold text-dark">Delivery:</span> {{ $costing->request_delivery_date->format('d/m/Y') }}
                            @endif
                        </td>
                    </tr>

                    {{-- Row 3: PULAU (Color-Coded by Regional Island Family) --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="3">3</td>
                        <td class="excel-cell cell-calibri-bold cell-left bg-light" data-coord="A3" data-val="PULAU">
                            PULAU
                        </td>
                        @foreach ($pulauGroups as $grp)
                            @php
                                $sLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($grp['start']);
                                $pBg = $getPulauColor($grp['val']);
                            @endphp
                            <td class="excel-cell cell-calibri-bold cell-center" colspan="{{ $grp['span'] }}" data-coord="{{ $sLet }}3" data-val="{{ $grp['val'] }}" style="background-color: {{ $pBg }} !important;">
                                {{ $grp['val'] ?: '—' }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 4: PROVINSI (Coordinating Lighter Pastel of Island) --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="4">4</td>
                        <td class="excel-cell cell-calibri-bold cell-left bg-light" data-coord="A4" data-val="PROVINSI">
                            PROVINSI
                        </td>
                        @foreach ($provGroups as $grp)
                            @php
                                $sLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($grp['start']);
                                $prBg = $getProvColor($grp['pulau'] ?? '', $grp['val'] ?? '');
                            @endphp
                            <td class="excel-cell cell-calibri-bold cell-center" colspan="{{ $grp['span'] }}" data-coord="{{ $sLet }}4" data-val="{{ $grp['val'] }}" style="background-color: {{ $prBg }} !important;">
                                {{ $grp['val'] ?: '—' }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 5: Brand --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="5">5</td>
                        <td class="excel-cell cell-yellow cell-left" data-coord="A5" data-val="Brand :">
                            Brand :
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-yellow cell-center" data-coord="{{ $colLet }}5" data-val="{{ $v->brand }}">
                                {{ $v->brand }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 6: Type Kendaraan --}}
                    <tr style="height: 34px;">
                        <td class="excel-row-hdr" data-row="6">6</td>
                        <td class="excel-cell cell-yellow cell-left" data-coord="A6" data-val="Type Kendaraan :">
                            Type Kendaraan :
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-yellow cell-center text-wrap" data-coord="{{ $colLet }}6" data-val="{{ $v->vehicle_type }}">
                                {{ $v->vehicle_type }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 7: BBN --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="7">7</td>
                        <td class="excel-cell cell-cyan cell-left" data-coord="A7" data-val="BBN :">
                            BBN :
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-cyan cell-center" data-coord="{{ $colLet }}7" data-val="{{ $v->bbn }}">
                                {{ $v->bbn }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 8: Pemakaian --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="8">8</td>
                        <td class="excel-cell cell-cyan cell-left" data-coord="A8" data-val="Pemakaian :">
                            Pemakaian :
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-cyan cell-center" data-coord="{{ $colLet }}8" data-val="{{ $v->pemakaian }}">
                                {{ $v->pemakaian }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 9: Harga OTR --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="9">9</td>
                        <td class="excel-cell cell-times-bold cell-left" data-coord="A9" data-val="Harga OTR">
                            Harga OTR
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-times-bold cell-right" data-coord="{{ $colLet }}9" data-val="{{ (float)$v->harga_otr }}">
                                {{ number_format($v->harga_otr, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 10: Blank (NO DISKON!) --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="10">10</td>
                        <td class="excel-cell" data-coord="A10" data-val=""></td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell" data-coord="{{ $colLet }}10" data-val=""></td>
                        @endforeach
                    </tr>

                    {{-- Row 11: Estimasi kenaikan harga --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="11">11</td>
                        <td class="excel-cell cell-times-bold cell-left" data-coord="A11" data-val="Estimasi kenaikan harga">
                            Estimasi kenaikan harga
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-calibri-bold cell-right" data-coord="{{ $colLet }}11" data-val="{{ (float)$v->estimasi_kenaikan }}">
                                {{ number_format($v->estimasi_kenaikan, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 12: Blank --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="12">12</td>
                        <td class="excel-cell" data-coord="A12" data-val=""></td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell" data-coord="{{ $colLet }}12" data-val=""></td>
                        @endforeach
                    </tr>

                    {{-- Row 13: Harga setelah estimasi kenaikan (= OTR + Estimasi, NO DISKON!) --}}
                    <tr style="background-color: #fefce8;">
                        <td class="excel-row-hdr" data-row="13">13</td>
                        <td class="excel-cell cell-times-bold cell-left" data-coord="A13" data-val="Harga setelah estimasi kenaikan">
                            Harga setelah estimasi kenaikan
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php 
                                $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); 
                                $afterIncreaseSmd = (float)$v->harga_otr + (float)$v->estimasi_kenaikan;
                            @endphp
                            <td class="excel-cell cell-calibri-bold cell-right" data-coord="{{ $colLet }}13" data-formula="={{ $colLet }}9+{{ $colLet }}11" data-val="{{ $afterIncreaseSmd }}">
                                {{ number_format($afterIncreaseSmd, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 14: Blank --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="14">14</td>
                        <td class="excel-cell" data-coord="A14" data-val=""></td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell" data-coord="{{ $colLet }}14" data-val=""></td>
                        @endforeach
                    </tr>

                    {{-- Row 15: Tambahan : --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="15">15</td>
                        <td class="excel-cell cell-calibri cell-left fw-semibold text-primary" data-coord="A15" data-val="Tambahan : ">
                            Tambahan : 
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell" data-coord="{{ $colLet }}15" data-val=""></td>
                        @endforeach
                    </tr>

                    {{-- Accessories Rows (Row 16 onwards) --}}
                    @php 
                        $curRowSmd = 16;
                        $accStartSmd = 16;
                    @endphp
                    @foreach ($accNames as $accName)
                        <tr>
                            <td class="excel-row-hdr" data-row="{{ $curRowSmd }}">{{ $curRowSmd }}</td>
                            <td class="excel-cell cell-calibri cell-left ps-3" data-coord="A{{ $curRowSmd }}" data-val="{{ $accName }}">
                                {{ $accName }}
                            </td>
                            @foreach ($vehicles as $idx => $v)
                                @php 
                                    $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); 
                                    $amt = $accRows[$accName]['amounts'][$v->id] ?? 0;
                                @endphp
                                <td class="excel-cell cell-calibri cell-right" data-coord="{{ $colLet }}{{ $curRowSmd }}" data-val="{{ $amt }}">
                                    {{ number_format($amt, 0, ',', '.') }}
                                </td>
                            @endforeach
                        </tr>
                        @php $curRowSmd++; @endphp
                    @endforeach

                    @php $accEndSmd = max($accStartSmd, $curRowSmd - 1); @endphp

                    {{-- Blank Row after accessories --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="{{ $curRowSmd }}">{{ $curRowSmd }}</td>
                        <td class="excel-cell" data-coord="A{{ $curRowSmd }}" data-val=""></td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell" data-coord="{{ $colLet }}{{ $curRowSmd }}" data-val=""></td>
                        @endforeach
                    </tr>
                    @php $curRowSmd++; @endphp

                    {{-- Total Acc Row --}}
                    @php $totalAccRowSmd = $curRowSmd; @endphp
                    <tr style="background-color: #f1f5f9;">
                        <td class="excel-row-hdr" data-row="{{ $totalAccRowSmd }}">{{ $totalAccRowSmd }}</td>
                        <td class="excel-cell cell-calibri cell-left fw-semibold" data-coord="A{{ $totalAccRowSmd }}" data-val="Total Acc">
                            Total Acc
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php 
                                $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); 
                                $accSum = 0;
                                foreach ($accNames as $an) {
                                    $accSum += ($accRows[$an]['amounts'][$v->id] ?? 0);
                                }
                                $sumFormula = count($accNames) > 0 ? "=SUM({$colLet}{$accStartSmd}:{$colLet}{$accEndSmd})" : "0";
                            @endphp
                            <td class="excel-cell cell-calibri cell-right fw-semibold" data-coord="{{ $colLet }}{{ $totalAccRowSmd }}" data-formula="{{ $sumFormula }}" data-val="{{ $accSum }}">
                                {{ number_format($accSum, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>
                    @php $curRowSmd++; @endphp

                    {{-- Blank Row after Total Acc --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="{{ $curRowSmd }}">{{ $curRowSmd }}</td>
                        <td class="excel-cell" data-coord="A{{ $curRowSmd }}" data-val=""></td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell" data-coord="{{ $colLet }}{{ $curRowSmd }}" data-val=""></td>
                        @endforeach
                    </tr>
                    @php $curRowSmd++; @endphp

                    {{-- Grand Total: Harga Unit + Aksesoris (SMD) --}}
                    @php $grandTotalRowSmd = $curRowSmd; @endphp
                    <tr style="background-color: #ede9fe;">
                        <td class="excel-row-hdr" data-row="{{ $grandTotalRowSmd }}">{{ $grandTotalRowSmd }}</td>
                        <td class="excel-cell cell-calibri-bold cell-left text-primary" data-coord="A{{ $grandTotalRowSmd }}" data-val="Harga Unit + Aksesoris">
                            Harga Unit + Aksesoris
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php 
                                $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); 
                                $afterIncreaseSmd = (float)$v->harga_otr + (float)$v->estimasi_kenaikan;
                                $accSum = 0;
                                foreach ($accNames as $an) {
                                    $accSum += ($accRows[$an]['amounts'][$v->id] ?? 0);
                                }
                                $grandTotalSmd = $afterIncreaseSmd + $accSum;
                            @endphp
                            <td class="excel-cell cell-calibri-bold cell-right text-primary" data-coord="{{ $colLet }}{{ $grandTotalRowSmd }}" data-formula="={{ $colLet }}13+{{ $colLet }}{{ $totalAccRowSmd }}" data-val="{{ $grandTotalSmd }}">
                                {{ number_format($grandTotalSmd, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>
                    @php $curRowSmd += 3; @endphp

                    {{-- Notes Row --}}
                    @php 
                        $notesRowSmd = $curRowSmd;
                        $notesText = 'Notes :' . ($costing->notes ? ' ' . $costing->notes : '');
                    @endphp
                    <tr>
                        <td class="excel-row-hdr" data-row="{{ $notesRowSmd }}">{{ $notesRowSmd }}</td>
                        <td class="excel-cell cell-calibri-bold cell-left cell-no-border" colspan="{{ $colCount + 1 }}" data-coord="A{{ $notesRowSmd }}" data-val="{{ $notesText }}">
                            {{ $notesText }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Excel Status Bar --}}
        <div class="excel-statusbar no-print">
            <div class="d-flex align-items-center gap-3">
                <span><i class="bi bi-check2-circle me-1"></i>READY</span>
                <span class="opacity-75">|</span>
                <span id="activeSheetNameLabel">Sheet: BU AB</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span>{{ count($vehicles) }} Vehicles</span>
                <span class="opacity-75">|</span>
                <span>Columns: A – {{ $lastCol }}</span>
                <span class="opacity-75">|</span>
                <span id="sumDisplay">Formulas Active</span>
            </div>
        </div>

    </div>

    {{-- Reject Modal --}}
    @if ($costing->isTender() && (Auth::user()?->isManager() || Auth::user()?->isAdmin()))
        <div class="modal fade no-print" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form method="POST" action="{{ route('costing.reject', $costing) }}">
                        @csrf
                        <div class="modal-header bg-danger text-white">
                            <h6 class="modal-title fw-bold" id="rejectModalLabel">
                                <i class="bi bi-x-circle me-2"></i>Reject Tender Costing
                            </h6>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="small text-muted mb-3">
                                Rejecting Costing <strong class="text-dark">{{ $costing->costing_number }}</strong>. Please provide a clear reason for the procurement staff.
                            </p>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                                <textarea name="rejection_reason" class="form-control form-control-sm" rows="3"
                                    placeholder="Explain why this tender costing was rejected (e.g. margin too low, inaccurate BBN region, etc.)..." required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger btn-sm px-3">
                                <i class="bi bi-x-lg me-1"></i>Confirm Rejection
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
<script>
    function openRejectModal() {
        const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
        modal.show();
    }

    function switchSheet(sheet) {
        const gridBuAb = document.getElementById('gridBuAb');
        const gridSmd = document.getElementById('gridSmd');
        const tabBuAb = document.getElementById('tabBtnBuAb');
        const tabSmd = document.getElementById('tabBtnSmd');
        const activeSheetNameLabel = document.getElementById('activeSheetNameLabel');

        if (sheet === 'BU_AB') {
            gridBuAb.classList.remove('d-none');
            gridSmd.classList.add('d-none');
            tabBuAb.classList.add('active');
            tabSmd.classList.remove('active');
            activeSheetNameLabel.textContent = 'Sheet: BU AB';
        } else {
            gridBuAb.classList.add('d-none');
            gridSmd.classList.remove('d-none');
            tabSmd.classList.add('active');
            tabBuAb.classList.remove('active');
            activeSheetNameLabel.textContent = 'Sheet: SMD';
        }

        // Reset active cell
        clearCellSelection();
    }

    function clearCellSelection() {
        document.querySelectorAll('.excel-cell-active').forEach(el => el.classList.remove('excel-cell-active'));
        document.querySelectorAll('.excel-col-hdr.highlighted').forEach(el => el.classList.remove('highlighted'));
        document.querySelectorAll('.excel-row-hdr.highlighted').forEach(el => el.classList.remove('highlighted'));
        document.getElementById('cellCoordDisplay').textContent = 'A1';
        document.getElementById('cellFormulaDisplay').value = '{{ addslashes($costing->customer_name) }}';
    }

    document.addEventListener('DOMContentLoaded', function () {
        const coordDisplay = document.getElementById('cellCoordDisplay');
        const formulaDisplay = document.getElementById('cellFormulaDisplay');

        // Select all cells across both sheets
        document.querySelectorAll('.excel-cell').forEach(cell => {
            cell.addEventListener('click', function () {
                // Clear previous highlights
                document.querySelectorAll('.excel-cell-active').forEach(el => el.classList.remove('excel-cell-active'));
                document.querySelectorAll('.excel-col-hdr.highlighted').forEach(el => el.classList.remove('highlighted'));
                document.querySelectorAll('.excel-row-hdr.highlighted').forEach(el => el.classList.remove('highlighted'));

                // Set active cell
                this.classList.add('excel-cell-active');

                // Extract coordinate
                const coord = this.getAttribute('data-coord') || '';
                coordDisplay.textContent = coord || '—';

                // Display formula or value
                const formula = this.getAttribute('data-formula');
                const val = this.getAttribute('data-val');
                if (formula) {
                    formulaDisplay.value = formula;
                } else if (val !== null && val !== undefined) {
                    formulaDisplay.value = val;
                } else {
                    formulaDisplay.value = this.innerText.trim();
                }

                // Highlight corresponding row & col headers
                if (coord) {
                    const colLetter = coord.replace(/[0-9]/g, '');
                    const rowNum = coord.replace(/[^0-9]/g, '');
                    
                    const parentContainer = this.closest('.excel-grid-container');
                    if (parentContainer) {
                        const colHdr = parentContainer.querySelector(`.excel-col-hdr[data-col="${colLetter}"]`);
                        if (colHdr) colHdr.classList.add('highlighted');

                        const rowHdr = parentContainer.querySelector(`.excel-row-hdr[data-row="${rowNum}"]`);
                        if (rowHdr) rowHdr.classList.add('highlighted');
                    }
                }
            });
        });

        // Default highlight A1 on initial load
        const initialCell = document.querySelector('#gridBuAb .excel-cell[data-coord="A1"]');
        if (initialCell) {
            initialCell.classList.add('excel-cell-active');
        }
    });
</script>
@endpush
