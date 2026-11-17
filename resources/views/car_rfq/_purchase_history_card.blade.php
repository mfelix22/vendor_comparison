@php
    $carLine = $rfq['car_line'] ?? null;
    $hasProduct = $carLine && is_array($carLine['product_id'] ?? null);
@endphp

@if ($hasProduct)
@php
    $productId = $carLine['product_id'][0];
    $rawProductName = is_array($carLine['product_id']) ? $carLine['product_id'][1] : ($carLine['name'] ?? '');
    $cleanProductName = preg_replace('/^\[[^\]]*\]\s*/', '', $rawProductName);
    $uom = is_array($carLine['product_uom'] ?? null) ? $carLine['product_uom'][1] : 'unit';
    $currency = is_array($rfq['currency_id'] ?? null) ? $rfq['currency_id'][1] : 'IDR';
    $rfqVendorNm = is_array($rfq['partner_id'] ?? null) ? $rfq['partner_id'][1] : '—';
    $qty = $carLine['product_qty'] ?? 1;

    // Helper: compute car nett unit price (after discount)
    $getCarHistNett = function($r) {
        if (isset($r['nett_price']) && (float)$r['nett_price'] > 0) {
            return (float)$r['nett_price'];
        }
        $q = (float)($r['product_qty'] ?? 1);
        $sub = (float)($r['price_subtotal'] ?? 0);
        if ($q > 0 && $sub > 0) {
            return round($sub / $q, 2);
        }
        $pu = (float)($r['price_unit'] ?? 0);
        $fixDisc = (float)($r['fix_discount'] ?? 0);
        $disc = (float)($r['discount'] ?? 0);
        if ($fixDisc > 0) {
            return max(0, $pu - $fixDisc);
        }
        if ($pu > 0 && $disc > 0) {
            return round($pu * (1 - ($disc / 100)), 2);
        }
        return $pu;
    };

    // Helper: format discount in millions
    $fmtCarDisc = function($disc) {
        if ($disc <= 0) return '';
        if ($disc >= 1000000 && ($disc % 100000 == 0)) {
            $juta = $disc / 1000000;
            return (floor($juta) == $juta) ? number_format($juta, 0) . ' Juta' : rtrim(rtrim(number_format($juta, 2, '.', ''), '0'), '.') . ' Juta';
        }
        return number_format($disc, 0, ',', '.');
    };

    // Current RFQ car prices
    $currentPricelist = (float)($carLine['price_unit'] ?? 0);
    $currentQty = (float)($carLine['product_qty'] ?? 1);
    $currentSubtotal = (float)($carLine['price_subtotal'] ?? 0);
    $currentFixDisc = (float)($carLine['fix_discount'] ?? 0);
    $currentDisc = (float)($carLine['discount'] ?? 0);
    if ($currentQty > 0 && $currentSubtotal > 0) {
        $currentNett = round($currentSubtotal / $currentQty, 2);
    } elseif ($currentFixDisc > 0) {
        $currentNett = max(0, $currentPricelist - $currentFixDisc);
    } elseif ($currentDisc > 0) {
        $currentNett = round($currentPricelist * (1 - ($currentDisc / 100)), 2);
    } else {
        $currentNett = $currentPricelist;
    }
    $currentHasDisc = ($currentPricelist > $currentNett);
    $currentDiscAmount = max(0, $currentPricelist - $currentNett);

    $historyKey = $productId . '::' . \App\Models\VendorComparison::normalizeDescription($carLine['name'] ?? '');
    $vendorRows = $history[$historyKey] ?? $history[$productId] ?? [];
    $otherRows = array_values($vendorRows);
    $totalHistoryCount = count($otherRows);

    // 1. Most recent purchase (by date desc)
    $byDate = $otherRows;
    usort($byDate, fn($a, $b) => strtotime($b['date']) <=> strtotime($a['date']));
    $mostRecentRow = $byDate[0] ?? null;

    // 2. Best Price per Tahun (grouped by calendar year)
    $historyByYear = [];
    foreach ($otherRows as $r) {
        $yr = $r['year'] ?? (!empty($r['date']) ? (int) date('Y', strtotime($r['date'])) : null);
        if ($yr) {
            $historyByYear[$yr][] = $r;
        }
    }
    krsort($historyByYear); // Sort descending

    $bestPriceByYear = []; // [ year => row ]
    foreach ($historyByYear as $yr => $rowsInYear) {
        usort($rowsInYear, function($a, $b) use ($getCarHistNett) {
            $pa = $getCarHistNett($a);
            $pb = $getCarHistNett($b);
            if ($pa == $pb) {
                return strtotime($b['date']) <=> strtotime($a['date']);
            }
            return $pa <=> $pb;
        });
        if (!empty($rowsInYear[0])) {
            $bestPriceByYear[$yr] = $rowsInYear[0];
        }
    }

    // Overall best price purchase in history
    $byPriceAsc = $otherRows;
    usort($byPriceAsc, function($a, $b) use ($getCarHistNett) {
        $pa = $getCarHistNett($a);
        $pb = $getCarHistNett($b);
        if ($pa == $pb) {
            return strtotime($b['date']) <=> strtotime($a['date']);
        }
        return $pa <=> $pb;
    });
    $bestPriceRow = $byPriceAsc[0] ?? null;

    // 3. Highest price purchase in history
    $byPriceDesc = $otherRows;
    usort($byPriceDesc, function($a, $b) use ($getCarHistNett) {
        $pa = $getCarHistNett($a);
        $pb = $getCarHistNett($b);
        if ($pa == $pb) {
            return strtotime($b['date']) <=> strtotime($a['date']);
        }
        return $pb <=> $pa;
    });
    $worstPriceRow = $byPriceDesc[0] ?? null;

    // Pool valid nett prices to determine variation
    $pricePool = array_filter(array_merge(
        [$currentNett],
        array_map($getCarHistNett, $otherRows)
    ), fn($p) => $p !== null && $p > 0);

    $uniquePrices = array_unique($pricePool);
    $hasVariation = count($uniquePrices) > 1;
    $globalMin = $hasVariation ? min($uniquePrices) : null;
    $globalMax = $hasVariation ? max($uniquePrices) : null;

    $isCurrentBest = $hasVariation && $currentNett == $globalMin;
    $isCurrentWorst = $hasVariation && $currentNett == $globalMax;
    $currentClass = 'price-current';
    if ($isCurrentBest) $currentClass = 'price-best';
    elseif ($isCurrentWorst) $currentClass = 'price-worst';

    $latestNett = $mostRecentRow ? $getCarHistNett($mostRecentRow) : 0;
    $latestYear = $mostRecentRow ? ($mostRecentRow['year'] ?? (!empty($mostRecentRow['date']) ? (int) date('Y', strtotime($mostRecentRow['date'])) : null)) : null;
    $isLatestBestForYear = $latestYear && isset($bestPriceByYear[$latestYear]) && ($bestPriceByYear[$latestYear]['order_id'] ?? null) === ($mostRecentRow['order_id'] ?? null);
    $isLatestBestOverall = $hasVariation && $mostRecentRow && $latestNett == $globalMin;
    $isLatestWorst = $hasVariation && $mostRecentRow && $latestNett == $globalMax;

    // Track rendered order IDs
    $renderedOrderIds = [];
    if ($mostRecentRow && !empty($mostRecentRow['order_id'])) {
        $renderedOrderIds[] = $mostRecentRow['order_id'];
    }

    $standaloneYearlyBestRows = [];
    foreach ($bestPriceByYear as $yr => $bRow) {
        $bOrderId = $bRow['order_id'] ?? null;
        if ($bOrderId && in_array($bOrderId, $renderedOrderIds)) {
            continue;
        }
        $standaloneYearlyBestRows[$yr] = $bRow;
        if ($bOrderId) {
            $renderedOrderIds[] = $bOrderId;
        }
    }

    $showWorstRow = $hasVariation 
        && $worstPriceRow 
        && !empty($worstPriceRow['order_id'])
        && !in_array($worstPriceRow['order_id'], $renderedOrderIds)
        && $getCarHistNett($worstPriceRow) > $globalMin;
@endphp

<div class="card product-item-card mb-4 shadow-sm" style="border: 2px solid #94a3b8; border-radius: 8px; overflow: hidden;">
    <div class="card-header py-2 d-flex align-items-center gap-2 flex-wrap" style="background-color: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
        <i class="bi bi-car-front-fill text-primary"></i>
        <span class="badge bg-secondary">{{ $cleanProductName }}</span>
        <span class="badge bg-white text-dark border">{{ $qty }} {{ $uom }}</span>
        <span class="ms-auto text-muted small">
            RFQ Unit Price (Nett):&nbsp;
            <strong @class([
                'text-success' => $isCurrentBest,
                'text-danger'  => $isCurrentWorst,
            ])>
                {{ $currency }} {{ number_format($currentNett, 0, ',', '.') }}
            </strong>
            @if ($currentHasDisc)
                <span class="text-danger" style="font-size: 0.72rem;">(Pricelist: {{ number_format($currentPricelist, 0, ',', '.') }})</span>
            @endif
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle mb-0">
                <thead>
                    <tr class="table-light text-muted small text-nowrap">
                        <th class="ps-3" style="width: 28%;">Current Vendor</th>
                        <th class="text-center" style="width: 20%;">Quoted Price (Nett &amp; Pricelist)</th>
                        <th class="text-center" style="width: 6%;">Qty</th>
                        <th class="text-center" style="width: 6%;">UoM</th>
                        <th style="width: 14%;">RFQ / PO</th>
                        <th style="width: 13%;">Date</th>
                        <th class="text-center" style="width: 13%;">Note</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="{{ $currentClass }}">
                        <td class="ps-3 fw-semibold">
                            <i class="bi bi-star-fill text-warning me-1"></i>{{ $rfqVendorNm }}
                        </td>
                        <td class="text-center fw-bold">
                            <div>{{ $currency }} {{ number_format($currentNett, 0, ',', '.') }}</div>
                            @if ($currentHasDisc)
                                <div class="text-muted small fw-normal" style="font-size: 0.7rem;">
                                    List: {{ number_format($currentPricelist, 0, ',', '.') }}
                                    @if ($currentDiscAmount > 0)
                                        &bull; <span class="text-danger fw-semibold">Disc. {{ $fmtCarDisc($currentDiscAmount) }}</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="text-center">{{ $qty }}</td>
                        <td class="text-center">{{ $uom }}</td>
                        <td class="text-muted">{{ $rfq['name'] }}</td>
                        <td class="text-muted">
                            {{ !empty($rfq['date_order']) ? \Carbon\Carbon::parse($rfq['date_order'])->format('d M Y') : '—' }}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-warning text-dark">Current RFQ</span>
                            @if ($isCurrentBest)
                                <span class="badge bg-success">Best Price</span>
                            @elseif ($isCurrentWorst)
                                <span class="badge bg-danger">Highest Price</span>
                            @endif
                        </td>
                    </tr>

                    {{-- Divider: Purchase History --}}
                    <tr class="border-top border-bottom" style="background-color: #f8fafc;">
                        <td colspan="7" class="px-3 py-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-clock-history text-primary fs-6"></i>
                                    <span class="fw-bold text-dark" style="font-size: 0.82rem; letter-spacing: 0.02em;">Purchase History</span>
                                    <span class="badge bg-secondary-subtle text-secondary border" style="font-size: 0.68rem; font-weight: 500;">Odoo</span>
                                </div>
                                @if ($totalHistoryCount > 0)
                                    <span class="badge bg-white text-secondary border font-monospace shadow-sm" style="font-size: 0.72rem; font-weight: 500;">
                                        <i class="bi bi-receipt me-1"></i>{{ $totalHistoryCount }} past {{ Str::plural('purchase', $totalHistoryCount) }}
                                    </span>
                                @else
                                    <span class="text-muted small fst-italic">Belum ada riwayat pembelian untuk model mobil ini di Odoo.</span>
                                @endif
                            </div>
                        </td>
                    </tr>

                    @if ($totalHistoryCount > 0)
                        {{-- Historical Table Header Row --}}
                        <tr class="table-light text-muted small text-nowrap">
                            <th class="ps-3">Historical Vendor</th>
                            <th class="text-center">Unit Price (After Disc &amp; Pricelist)</th>
                            <th class="text-center">Qty</th>
                            <th class="text-center">UoM</th>
                            <th>Past PO</th>
                            <th>Purchase Date</th>
                            <th class="text-center">Note</th>
                        </tr>

                        {{-- Row 2: Latest Purchase --}}
                        @if ($mostRecentRow)
                            @php
                                $rNett = $getCarHistNett($mostRecentRow);
                                $rList = (float)($mostRecentRow['price_unit'] ?? 0);
                                $rFixDisc = (float)($mostRecentRow['fix_discount'] ?? 0);
                                $rDisc = (float)($mostRecentRow['discount'] ?? 0);
                                $rDiscAmount = max(0, $rList - $rNett);
                                $rHasDisc = ($rList > $rNett);
                            @endphp
                            <tr class="table-info">
                                <td class="ps-3 fw-semibold" style="box-shadow: inset 4px 0 0 #0d6efd;">{{ $mostRecentRow['vendor_name'] }}</td>
                                <td class="text-center fw-semibold">
                                    <div>{{ $currency }} {{ number_format($rNett, 0, ',', '.') }}</div>
                                    @if ($rHasDisc)
                                        <div class="text-muted small fw-normal" style="font-size: 0.7rem;">
                                            List: {{ number_format($rList, 0, ',', '.') }}
                                            @if ($rDiscAmount > 0)
                                                &bull; <span class="text-danger fw-semibold">Disc. {{ $fmtCarDisc($rDiscAmount) }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">{{ $mostRecentRow['product_qty'] }}</td>
                                <td class="text-center">{{ $mostRecentRow['uom'] }}</td>
                                <td>
                                    <span class="text-muted small">{{ $mostRecentRow['po_name'] }}</span>
                                </td>
                                <td class="fw-semibold">
                                    {{ \Carbon\Carbon::parse($mostRecentRow['date'])->format('d M Y H:i') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">Latest Purchase</span>
                                    @if ($isLatestBestForYear)
                                        <span class="badge bg-success">&#9733; Best Price {{ $latestYear }}</span>
                                    @elseif ($isLatestBestOverall)
                                        <span class="badge bg-success">&#9733; Best Price</span>
                                    @elseif ($isLatestWorst)
                                        <span class="badge bg-danger">&#9660; Highest Price</span>
                                    @endif
                                </td>
                            </tr>
                        @endif

                        {{-- Row 3+: Standalone Best Price per Year --}}
                        @foreach ($standaloneYearlyBestRows as $yr => $yearlyRow)
                            @php
                                $yNett = $getCarHistNett($yearlyRow);
                                $yList = (float)($yearlyRow['price_unit'] ?? 0);
                                $yDiscAmount = max(0, $yList - $yNett);
                                $yHasDisc = ($yList > $yNett);
                            @endphp
                            <tr class="price-best">
                                <td class="ps-3 fw-semibold">{{ $yearlyRow['vendor_name'] }}</td>
                                <td class="text-center fw-semibold">
                                    <div>{{ $currency }} {{ number_format($yNett, 0, ',', '.') }}</div>
                                    @if ($yHasDisc)
                                        <div class="text-muted small fw-normal" style="font-size: 0.7rem;">
                                            List: {{ number_format($yList, 0, ',', '.') }}
                                            @if ($yDiscAmount > 0)
                                                &bull; <span class="text-danger fw-semibold">Disc. {{ $fmtCarDisc($yDiscAmount) }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">{{ $yearlyRow['product_qty'] }}</td>
                                <td class="text-center">{{ $yearlyRow['uom'] }}</td>
                                <td>
                                    <span class="text-muted small">{{ $yearlyRow['po_name'] }}</span>
                                </td>
                                <td class="text-muted">
                                    {{ \Carbon\Carbon::parse($yearlyRow['date'])->format('d M Y H:i') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success">&#9733; Best Price {{ $yr }}</span>
                                    @if ($yNett == $globalMin && count($bestPriceByYear) > 1)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.65rem;">All-Time Best</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        {{-- Row 4: Highest Price (shown if distinct from Latest and all Best Price rows) --}}
                        @if ($showWorstRow)
                            @php
                                $wNett = $getCarHistNett($worstPriceRow);
                                $wList = (float)($worstPriceRow['price_unit'] ?? 0);
                                $wDiscAmount = max(0, $wList - $wNett);
                                $wHasDisc = ($wList > $wNett);
                            @endphp
                            <tr class="price-worst">
                                <td class="ps-3">{{ $worstPriceRow['vendor_name'] }}</td>
                                <td class="text-center">
                                    <div>{{ $currency }} {{ number_format($wNett, 0, ',', '.') }}</div>
                                    @if ($wHasDisc)
                                        <div class="text-muted small fw-normal" style="font-size: 0.7rem;">
                                            List: {{ number_format($wList, 0, ',', '.') }}
                                            @if ($wDiscAmount > 0)
                                                &bull; <span class="text-danger fw-semibold">Disc. {{ $fmtCarDisc($wDiscAmount) }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">{{ $worstPriceRow['product_qty'] }}</td>
                                <td class="text-center">{{ $worstPriceRow['uom'] }}</td>
                                <td>
                                    <span class="text-muted small">{{ $worstPriceRow['po_name'] }}</span>
                                </td>
                                <td class="text-muted">
                                    {{ \Carbon\Carbon::parse($worstPriceRow['date'])->format('d M Y H:i') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger">&#9660; Highest Price</span>
                                </td>
                            </tr>
                        @endif
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

