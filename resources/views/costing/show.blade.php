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
        .no-print {
            display: none !important;
        }
        .excel-window {
            box-shadow: none !important;
            border: none !important;
        }
        .excel-topbar, .excel-tabs-bar, .excel-statusbar {
            display: none !important;
        }
        .excel-grid-container {
            max-height: none !important;
            overflow: visible !important;
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
                <div class="d-flex align-items-center gap-2">
                    <h4 class="fw-bold mb-0">Costing Details</h4>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                        <i class="bi bi-file-earmark-excel me-1"></i>Excel Grid View
                    </span>
                </div>
                <div class="text-muted small">
                    <span class="fw-semibold text-primary">{{ $costing->costing_number }}</span>
                    &middot; Customer: <span class="fw-semibold text-dark">{{ $costing->customer_name }}</span>
                    &middot; {{ $costing->created_at->format('d/m/Y') }}
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Print
            </button>
            <a href="{{ route('costing.export', $costing) }}" class="btn btn-success btn-sm fw-semibold">
                <i class="bi bi-download me-1"></i>Download Excel (.xlsx)
            </a>
            <a href="{{ route('costing.edit', $costing) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil me-1"></i>Edit Sheet
            </a>
        </div>
    </div>

    @php
        $vehicles = $costing->vehicles;
        $colCount = count($vehicles);
        $colLetters = [];
        for ($i = 0; $i <= $colCount; $i++) {
            $colLetters[] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
        }
        $lastCol = end($colLetters);
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
                    {{-- Row 1: Customer Name --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="1">1</td>
                        <td class="excel-cell cell-calibri-bold" data-coord="A1" data-val="{{ $costing->customer_name }}">
                            {{ $costing->customer_name }}
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell" data-coord="{{ $colLet }}1" data-val=""></td>
                        @endforeach
                    </tr>

                    {{-- Row 2: Blank --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="2">2</td>
                        <td class="excel-cell" data-coord="A2" data-val=""></td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell" data-coord="{{ $colLet }}2" data-val=""></td>
                        @endforeach
                    </tr>

                    {{-- Row 3: Brand --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="3">3</td>
                        <td class="excel-cell cell-yellow cell-left" data-coord="A3" data-val="Brand :">
                            Brand :
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-yellow cell-center" data-coord="{{ $colLet }}3" data-val="{{ $v->brand }}">
                                {{ $v->brand }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 4: Type Kendaraan --}}
                    <tr style="height: 34px;">
                        <td class="excel-row-hdr" data-row="4">4</td>
                        <td class="excel-cell cell-yellow cell-left" data-coord="A4" data-val="Type Kendaraan :">
                            Type Kendaraan :
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-yellow cell-center text-wrap" data-coord="{{ $colLet }}4" data-val="{{ $v->vehicle_type }}">
                                {{ $v->vehicle_type }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 5: BBN --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="5">5</td>
                        <td class="excel-cell cell-cyan cell-left" data-coord="A5" data-val="BBN :">
                            BBN :
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-cyan cell-center" data-coord="{{ $colLet }}5" data-val="{{ $v->bbn }}">
                                {{ $v->bbn }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 6: Pemakaian --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="6">6</td>
                        <td class="excel-cell cell-cyan cell-left" data-coord="A6" data-val="Pemakaian :">
                            Pemakaian :
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-cyan cell-center" data-coord="{{ $colLet }}6" data-val="{{ $v->pemakaian }}">
                                {{ $v->pemakaian }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 7: Harga OTR --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="7">7</td>
                        <td class="excel-cell cell-times-bold cell-left" data-coord="A7" data-val="Harga OTR">
                            Harga OTR
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-times-bold cell-right" data-coord="{{ $colLet }}7" data-val="{{ (float)$v->harga_otr }}">
                                {{ number_format($v->harga_otr, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 8: Diskon --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="8">8</td>
                        <td class="excel-cell cell-times cell-left" data-coord="A8" data-val="Diskon">
                            Diskon
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-calibri-bold cell-right {{ $v->diskon > 0 ? 'text-danger' : '' }}" data-coord="{{ $colLet }}8" data-val="{{ (float)$v->diskon }}">
                                {{ number_format($v->diskon, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 9: Harga Nett --}}
                    <tr style="background-color: #f0fdf4;">
                        <td class="excel-row-hdr" data-row="9">9</td>
                        <td class="excel-cell cell-times-bold cell-left text-success" data-coord="A9" data-val="Harga Nett">
                            Harga Nett
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php 
                                $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); 
                                $nett = (float)$v->harga_otr - (float)$v->diskon;
                            @endphp
                            <td class="excel-cell cell-calibri-bold cell-right text-success" data-coord="{{ $colLet }}9" data-formula="={{ $colLet }}7-{{ $colLet }}8" data-val="{{ $nett }}">
                                {{ number_format($nett, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 10: Blank --}}
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

                    {{-- Row 13: Harga setelah estimasi kenaikan --}}
                    <tr style="background-color: #fefce8;">
                        <td class="excel-row-hdr" data-row="13">13</td>
                        <td class="excel-cell cell-times-bold cell-left" data-coord="A13" data-val="Harga setelah estimasi kenaikan">
                            Harga setelah estimasi kenaikan
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php 
                                $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); 
                                $nett = (float)$v->harga_otr - (float)$v->diskon;
                                $afterIncrease = $nett + (float)$v->estimasi_kenaikan;
                            @endphp
                            <td class="excel-cell cell-calibri-bold cell-right" data-coord="{{ $colLet }}13" data-formula="={{ $colLet }}9+{{ $colLet }}11" data-val="{{ $afterIncrease }}">
                                {{ number_format($afterIncrease, 0, ',', '.') }}
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
                        $curRowBu = 16;
                        $accStartBu = 16;
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
                            <td class="excel-cell cell-calibri-bold cell-right text-primary" data-coord="{{ $colLet }}{{ $grandTotalRowBu }}" data-formula="={{ $colLet }}13+{{ $colLet }}{{ $totalAccRowBu }}" data-val="{{ $grandTotal }}">
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
                    {{-- Row 1: Customer Name --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="1">1</td>
                        <td class="excel-cell cell-calibri-bold" data-coord="A1" data-val="{{ $costing->customer_name }}">
                            {{ $costing->customer_name }}
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell" data-coord="{{ $colLet }}1" data-val=""></td>
                        @endforeach
                    </tr>

                    {{-- Row 2: Blank --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="2">2</td>
                        <td class="excel-cell" data-coord="A2" data-val=""></td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell" data-coord="{{ $colLet }}2" data-val=""></td>
                        @endforeach
                    </tr>

                    {{-- Row 3: Brand --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="3">3</td>
                        <td class="excel-cell cell-yellow cell-left" data-coord="A3" data-val="Brand :">
                            Brand :
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-yellow cell-center" data-coord="{{ $colLet }}3" data-val="{{ $v->brand }}">
                                {{ $v->brand }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 4: Type Kendaraan --}}
                    <tr style="height: 34px;">
                        <td class="excel-row-hdr" data-row="4">4</td>
                        <td class="excel-cell cell-yellow cell-left" data-coord="A4" data-val="Type Kendaraan :">
                            Type Kendaraan :
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-yellow cell-center text-wrap" data-coord="{{ $colLet }}4" data-val="{{ $v->vehicle_type }}">
                                {{ $v->vehicle_type }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 5: BBN --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="5">5</td>
                        <td class="excel-cell cell-cyan cell-left" data-coord="A5" data-val="BBN :">
                            BBN :
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-cyan cell-center" data-coord="{{ $colLet }}5" data-val="{{ $v->bbn }}">
                                {{ $v->bbn }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 6: Pemakaian --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="6">6</td>
                        <td class="excel-cell cell-cyan cell-left" data-coord="A6" data-val="Pemakaian :">
                            Pemakaian :
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-cyan cell-center" data-coord="{{ $colLet }}6" data-val="{{ $v->pemakaian }}">
                                {{ $v->pemakaian }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 7: Harga OTR --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="7">7</td>
                        <td class="excel-cell cell-times-bold cell-left" data-coord="A7" data-val="Harga OTR">
                            Harga OTR
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-times-bold cell-right" data-coord="{{ $colLet }}7" data-val="{{ (float)$v->harga_otr }}">
                                {{ number_format($v->harga_otr, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 8: Blank (NO DISKON!) --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="8">8</td>
                        <td class="excel-cell" data-coord="A8" data-val=""></td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell" data-coord="{{ $colLet }}8" data-val=""></td>
                        @endforeach
                    </tr>

                    {{-- Row 9: Estimasi kenaikan harga --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="9">9</td>
                        <td class="excel-cell cell-times-bold cell-left" data-coord="A9" data-val="Estimasi kenaikan harga">
                            Estimasi kenaikan harga
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell cell-calibri-bold cell-right" data-coord="{{ $colLet }}9" data-val="{{ (float)$v->estimasi_kenaikan }}">
                                {{ number_format($v->estimasi_kenaikan, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>

                    {{-- Row 10: Blank --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="10">10</td>
                        <td class="excel-cell" data-coord="A10" data-val=""></td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell" data-coord="{{ $colLet }}10" data-val=""></td>
                        @endforeach
                    </tr>

                    {{-- Row 11: Harga setelah estimasi kenaikan (= OTR + Estimasi, NO DISKON!) --}}
                    <tr style="background-color: #fefce8;">
                        <td class="excel-row-hdr" data-row="11">11</td>
                        <td class="excel-cell cell-times-bold cell-left" data-coord="A11" data-val="Harga setelah estimasi kenaikan">
                            Harga setelah estimasi kenaikan
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php 
                                $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); 
                                $afterIncreaseSmd = (float)$v->harga_otr + (float)$v->estimasi_kenaikan;
                            @endphp
                            <td class="excel-cell cell-calibri-bold cell-right" data-coord="{{ $colLet }}11" data-formula="={{ $colLet }}7+{{ $colLet }}9" data-val="{{ $afterIncreaseSmd }}">
                                {{ number_format($afterIncreaseSmd, 0, ',', '.') }}
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

                    {{-- Row 13: Tambahan : --}}
                    <tr>
                        <td class="excel-row-hdr" data-row="13">13</td>
                        <td class="excel-cell cell-calibri cell-left fw-semibold text-primary" data-coord="A13" data-val="Tambahan : ">
                            Tambahan : 
                        </td>
                        @foreach ($vehicles as $idx => $v)
                            @php $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2); @endphp
                            <td class="excel-cell" data-coord="{{ $colLet }}13" data-val=""></td>
                        @endforeach
                    </tr>

                    {{-- Accessories Rows (Row 14 onwards) --}}
                    @php 
                        $curRowSmd = 14;
                        $accStartSmd = 14;
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
                            <td class="excel-cell cell-calibri-bold cell-right text-primary" data-coord="{{ $colLet }}{{ $grandTotalRowSmd }}" data-formula="={{ $colLet }}11+{{ $colLet }}{{ $totalAccRowSmd }}" data-val="{{ $grandTotalSmd }}">
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

@endsection

@push('scripts')
<script>
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
