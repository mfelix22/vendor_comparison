<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>CLVP CAR – {{ $comparison->po_name }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        @page {
            size: A4 landscape;
            margin: 12mm 14mm 10mm 14mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #000;
        }

        h1 {
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .header-table {
            width: 100%;
            margin-bottom: 8px;
            border-collapse: collapse;
        }

        .header-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .cat-container {
            text-align: center;
            margin-top: 4px;
        }

        .cat-box {
            display: inline-block;
            margin: 0 10px;
            font-size: 9px;
        }

        .checkbox {
            display: inline-block;
            width: 11px;
            height: 11px;
            border: 1.5px solid #000;
            text-align: center;
            line-height: 10px;
            font-size: 8.5px;
            font-weight: bold;
            vertical-align: middle;
            margin-right: 3px;
        }

        table.matrix {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 0;
        }

        table.matrix th,
        table.matrix td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: middle;
        }

        table.matrix th {
            background: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-start { text-align: left; }
        .fw-bold { font-weight: bold; }
        .small { font-size: 8px; }

        .selected-vendor {
            background: #edf7ed;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
            border: 1px solid #000;
            border-top: none;
            font-size: 8.5px;
        }

        .footer-table td {
            padding: 6px 8px;
            vertical-align: top;
        }
    </style>
</head>

<body>

    @php
        $vendors = $comparison->vendors ?? [];
        $vendorCount = count($vendors);
        $hasFourthCol = ($vendorCount <= 3);
        $totalCols = $vendorCount + ($hasFourthCol ? 1 : 0);

        $logoPath = public_path('logo.png');
        $logoSrc = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $fmtJuta = function($val, $prefix = '') {
            if ($val <= 0) return '—';
            if ($val >= 1000000) {
                $juta = $val / 1000000;
                $formatted = (floor($juta) == $juta) ? number_format($juta, 0) : rtrim(rtrim(number_format($juta, 2, '.', ''), '0'), '.');
                return $prefix . ' ' . $formatted . ' Juta';
            }
            return $prefix . ' Rp ' . number_format($val, 0, ',', '.');
        };

        $hasAnyDealerNotes = collect($vendors)->contains(fn($v) => trim($v['notes'] ?? $v['bonus'] ?? '') !== '');
        $spacerVendorTds = '';
        foreach ($vendors as $v) {
            $isSel = (($v['name'] ?? '') === $comparison->selected_vendor);
            $spacerVendorTds .= '<td class="' . ($isSel ? 'selected-vendor' : '') . '"></td>';
        }
        if ($hasFourthCol) {
            $spacerVendorTds .= '<td></td>';
        }
    @endphp

    {{-- Header matching Comparisson PO 270 - Jalan Baru Jalan.pdf --}}
    <table class="header-table">
        <tr>
            <td style="width:20%; vertical-align:middle;">
                @if ($logoSrc)
                    <img src="{{ $logoSrc }}" style="width:130px; height:auto;" alt="HARENT">
                @else
                    <div style="font-size:14px; font-weight:bold; color:#1a4f8b;">HARENT</div>
                    <div style="font-size:8px; color:#444;">hartono rent car</div>
                @endif
            </td>
            <td style="width:60%; text-align:center; vertical-align:middle;">
                <h1>COMPARISON LOCAL VENDOR PRICE CAR ( CLVP CAR )</h1>
                <div class="cat-container">
                    <span class="cat-box"><span class="checkbox">V</span> Unit Baru</span>
                    <span class="cat-box"><span class="checkbox">&nbsp;</span> Aksesoris Mobil</span>
                    <span class="cat-box"><span class="checkbox">&nbsp;</span> Sparepart</span>
                    <span class="cat-box"><span class="checkbox">&nbsp;</span> Umum</span>
                </div>
            </td>
            <td style="width:20%; text-align:right; vertical-align:middle;">
                &nbsp;
            </td>
        </tr>
    </table>

    {{-- Main Comparison Matrix --}}
    <table class="matrix">
        <thead>
            <tr>
                <th rowspan="2" style="width:3%;">No</th>
                <th rowspan="2" style="width:30%;">Nama Barang</th>
                <th rowspan="2" style="width:6%;">Kode Barang</th>
                <th rowspan="2" style="width:4%;">QTY</th>
                <th rowspan="2" style="width:4%;">UoM</th>
                @if ($totalCols > 0)
                    <th colspan="{{ $totalCols }}" style="background:#e8e8e8;">Supplier / Vendor</th>
                @endif
            </tr>
            <tr>
                @php
                    $colW = $totalCols > 0 ? round(53 / $totalCols, 2) : 13.25;
                @endphp
                @foreach ($vendors as $v)
                    @php
                        $vPic = $v['pic'] ?? $v['sales_person'] ?? $v['contact'] ?? null;
                        $vPhone = $v['phone'] ?? null;
                    @endphp
                    <th style="width:{{ $colW }}%;" class="{{ ($v['name'] ?? '') === $comparison->selected_vendor ? 'selected-vendor' : '' }}">
                        <div class="fw-bold">{{ $v['name'] ?? '—' }}</div>
                        @if($vPic)<div class="small fw-normal">{{ $vPic }}</div>@endif
                        @if($vPhone)<div class="small fw-normal">{{ $vPhone }}</div>@endif
                    </th>
                @endforeach
                @if ($hasFourthCol)
                    <th style="width:{{ $colW }}%; text-align:center; vertical-align:middle; font-weight:bold; font-size:8px;">
                        PIC : TELP :
                    </th>
                @endif
            </tr>
        </thead>
        <tbody>
            {{-- Row 1: Vehicle Model & Offered Unit Price --}}
            <tr style="height:28px;">
                <td class="text-center fw-bold">1</td>
                <td class="text-start fw-bold" style="line-height:1.3; padding:5px 8px;">
                    {{ $comparison->car_model }}
                </td>
                <td class="text-center" style="font-size:8px; color:#555;">—</td>
                <td class="text-center fw-bold">{{ $comparison->qty }}</td>
                <td class="text-center">{{ $comparison->uom }}</td>
                @foreach ($vendors as $v)
                    @php
                        $isSelected = (($v['name'] ?? '') === $comparison->selected_vendor);
                        $offered = (float)($v['unit_price'] ?? $v['offered_price'] ?? $v['net_price'] ?? $comparison->pricelist_ori ?? 0);
                    @endphp
                    <td class="text-center fw-bold {{ $isSelected ? 'selected-vendor' : '' }}" style="color:#000; white-space:nowrap;">
                        Rp. {{ number_format($offered, 0, ',', '.') }}/unit
                    </td>
                @endforeach
                @if ($hasFourthCol)
                    <td></td>
                @endif
            </tr>

            {{-- Blank Spacer Row between Nama Barang and BBN (matching official form scan) --}}
            <tr style="height:20px;">
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                {!! $spacerVendorTds !!}
            </tr>

            {{-- Row 2: BBN (Vendor cells are completely blank) --}}
            <tr style="height:24px;">
                <td></td>
                <td class="text-start" style="font-size:8.5px; padding:4px 8px;">BBN : {{ $comparison->bbn ?: '—' }}</td>
                <td></td>
                <td></td>
                <td></td>
                {!! $spacerVendorTds !!}
            </tr>

            {{-- Row 3: VC (Vendor cells are completely blank) --}}
            <tr style="height:24px;">
                <td></td>
                <td class="text-start" style="font-size:8.5px; padding:4px 8px;">VC : {{ $comparison->vin_code ?: '—' }}</td>
                <td></td>
                <td></td>
                <td></td>
                {!! $spacerVendorTds !!}
            </tr>

            {{-- Row 4: Warna & Disc. --}}
            <tr style="height:24px;">
                <td></td>
                <td class="text-start" style="font-size:8.5px; padding:4px 8px;">Warna : {{ $comparison->warna ?: '—' }}</td>
                <td></td>
                <td></td>
                <td></td>
                @foreach ($vendors as $v)
                    @php
                        $isSelected = (($v['name'] ?? '') === $comparison->selected_vendor);
                        $disc = (float)($v['discount'] ?? 0);
                    @endphp
                    <td class="text-center {{ $isSelected ? 'selected-vendor' : '' }}" style="font-size:8.5px; color:#000; white-space:nowrap;">
                        {{ $fmtJuta($disc, 'Disc.') }}
                    </td>
                @endforeach
                @if ($hasFourthCol)
                    <td></td>
                @endif
            </tr>

            {{-- Row 5: Cashback --}}
            <tr style="height:24px;">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                @foreach ($vendors as $v)
                    @php
                        $isSelected = (($v['name'] ?? '') === $comparison->selected_vendor);
                        $cb = (float)($v['cashback'] ?? 0);
                    @endphp
                    <td class="text-center {{ $isSelected ? 'selected-vendor' : '' }}" style="font-size:8.5px; color:#000; white-space:nowrap;">
                        {{ $fmtJuta($cb, 'Cashback') }}
                    </td>
                @endforeach
                @if ($hasFourthCol)
                    <td></td>
                @endif
            </tr>

            {{-- Row 6: Total Disc + Cashback --}}
            <tr style="height:24px;">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                @foreach ($vendors as $v)
                    @php
                        $isSelected = (($v['name'] ?? '') === $comparison->selected_vendor);
                        $disc = (float)($v['discount'] ?? 0);
                        $cb = (float)($v['cashback'] ?? 0);
                        $totBen = (float)($v['total_benefit'] ?? ($disc + $cb));
                    @endphp
                    <td class="text-center fw-bold {{ $isSelected ? 'selected-vendor' : '' }}" style="font-size:8px; color:#000; white-space:normal; line-height:1.2;">
                        {{ $fmtJuta($totBen, 'Total Disc+Cashback') }}
                    </td>
                @endforeach
                @if ($hasFourthCol)
                    <td></td>
                @endif
            </tr>

            {{-- Spacer Row (1 box) between Total Disc+Cashback and TOTAL --}}
            <tr style="height:20px;">
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                {!! $spacerVendorTds !!}
            </tr>

            {{-- Row 7: TOTAL (Net Price * Qty matching General Comparison CLVP) --}}
            <tr style="height:24px; font-weight:bold; background:#f9f9f9;">
                <td colspan="5" style="text-align:right; font-weight:bold; padding-right:6px;">TOTAL</td>
                @foreach ($vendors as $v)
                    @php
                        $isSelected = (($v['name'] ?? '') === $comparison->selected_vendor);
                        $offered = (float)($v['unit_price'] ?? $v['offered_price'] ?? $v['net_price'] ?? $comparison->pricelist_ori ?? 0);
                        $disc = (float)($v['discount'] ?? 0);
                        $netUnit = $offered > 0 ? max(0, $offered - $disc) : 0;
                        $netTotal = $netUnit * $comparison->qty;
                    @endphp
                    <td class="text-center fw-bold {{ $isSelected ? 'selected-vendor' : '' }}" style="color:#000; white-space:nowrap;">
                        {{ $netTotal > 0 ? 'Rp. ' . number_format($netTotal, 0, ',', '.') : '—' }}
                    </td>
                @endforeach
                @if ($hasFourthCol)
                    <td></td>
                @endif
            </tr>

            {{-- Spacer Row (1 box) between TOTAL and Bonus / Notes (or Ready) --}}
            <tr style="height:20px;">
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                {!! $spacerVendorTds !!}
            </tr>

            {{-- Bonus / Notes Row per Dealer (before Ready / Indent) --}}
            @if ($hasAnyDealerNotes)
                <tr style="height:22px;">
                    <td></td>
                    <td class="text-start" style="font-size:8px; font-style:italic; color:#333; padding:3px 8px;">Bonus / Notes :</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    @foreach ($vendors as $v)
                        @php
                            $isSelected = (($v['name'] ?? '') === $comparison->selected_vendor);
                            $dNote = trim($v['notes'] ?? $v['bonus'] ?? '');
                        @endphp
                        <td class="text-center {{ $isSelected ? 'selected-vendor' : '' }}" style="font-size:8px; color:#000; white-space:normal; word-break:break-word; line-height:1.35; padding:3px 4px;">
                            {!! nl2br(e($dNote)) !!}
                        </td>
                    @endforeach
                    @if ($hasFourthCol)
                        <td></td>
                    @endif
                </tr>

                {{-- Spacer Row (1 box) between Bonus / Notes and Ready --}}
                <tr style="height:20px;">
                    <td>&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    {!! $spacerVendorTds !!}
                </tr>
            @endif

            {{-- Row 8: Dedicated READY Row with Square Box --}}
            <tr style="height:24px;">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                @foreach ($vendors as $v)
                    @php
                        $isSelected = (($v['name'] ?? '') === $comparison->selected_vendor);
                        $avail = strtolower($v['availability'] ?? $v['ready_indent'] ?? '');
                        $isReady = ($avail === 'ready' || !empty($v['ready']));
                    @endphp
                    <td class="{{ $isSelected ? 'selected-vendor' : '' }}" style="padding-left:8px;">
                        <span class="checkbox">{{ $isReady ? 'V' : '' }}</span>
                        <span style="font-size:8.5px; color:#000;">Ready</span>
                    </td>
                @endforeach
                @if ($hasFourthCol)
                    <td></td>
                @endif
            </tr>

            {{-- Row 9: Dedicated INDENT / KOSONG Row with Square Box --}}
            <tr style="height:24px;">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                @foreach ($vendors as $v)
                    @php
                        $isSelected = (($v['name'] ?? '') === $comparison->selected_vendor);
                        $avail = strtolower($v['availability'] ?? $v['ready_indent'] ?? '');
                        $isIndent = ($avail === 'indent' || !empty($v['indent']));
                    @endphp
                    <td class="{{ $isSelected ? 'selected-vendor' : '' }}" style="padding-left:8px;">
                        <span class="checkbox">{{ $isIndent ? 'V' : '' }}</span>
                        <span style="font-size:8.5px; color:#000;">Indent / Kosong</span>
                        @if($isIndent && (!empty($v['indent_duration']) || !empty($v['delivery_days'])))
                            <span style="font-size:7.5px; color:#555;">({{ $v['indent_duration'] ?? ($v['delivery_days'].' hr') }})</span>
                        @endif
                    </td>
                @endforeach
                @if ($hasFourthCol)
                    <td></td>
                @endif
            </tr>
        </tbody>
    </table>

    {{-- Bottom Section: Clean Unbordered Footer (Matching RFQ General with natural whitespace) --}}
    <table style="width:100%; border-collapse:collapse; margin-top:10px; font-size:8.5px;">
        <tr>
            <td style="width:68%; border:none; vertical-align:top; padding:0;">
                <div class="fw-bold mb-1">No. CLVP : {{ $comparison->comparison_code }}</div>
                <div style="color:#555; font-size:8px;">Ref PO: {{ $comparison->po_name }}</div>
                @if ($comparison->notes)
                    <div style="color:#444; font-size:8px; margin-top:4px; max-width:480px;">{{ $comparison->notes }}</div>
                @endif
            </td>
            <td style="width:32%; border:none; text-align:right; vertical-align:top; padding:0;">
                <div>Tgl {{ $comparison->created_at->format('d/m/y') }}</div>
                <div>Dibuat oleh,</div>
                <div style="height:36px;"></div>
                <div class="fw-bold">({{ $comparison->prepared_by_name }})</div>
                <div style="font-size:7px; color:#666; margin-top:2px;">SDP/FR/PCH/12, Rev.02</div>
            </td>
        </tr>
    </table>

</body>

</html>
