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
    $quotedPrice = (float)($carLine['price_unit'] ?? 0);
    $qty = $carLine['product_qty'] ?? 1;

    $historyKey = $productId . '::' . \App\Models\VendorComparison::normalizeDescription($carLine['name'] ?? '');
    $vendorRows = $history[$historyKey] ?? $history[$productId] ?? [];
    $otherRows = array_values($vendorRows);
    $totalHistoryCount = count($otherRows);

    // 1. Most recent purchase (by date desc)
    $byDate = $otherRows;
    usort($byDate, fn($a, $b) => strtotime($b['date']) <=> strtotime($a['date']));
    $mostRecentRow = $byDate[0] ?? null;

    // 2. Best price purchase in history
    $byPriceAsc = $otherRows;
    usort($byPriceAsc, function($a, $b) {
        if ($a['price_unit'] == $b['price_unit']) {
            return strtotime($b['date']) <=> strtotime($a['date']);
        }
        return $a['price_unit'] <=> $b['price_unit'];
    });
    $bestPriceRow = $byPriceAsc[0] ?? null;

    // 3. Highest price purchase in history
    $byPriceDesc = $otherRows;
    usort($byPriceDesc, function($a, $b) {
        if ($a['price_unit'] == $b['price_unit']) {
            return strtotime($b['date']) <=> strtotime($a['date']);
        }
        return $b['price_unit'] <=> $a['price_unit'];
    });
    $worstPriceRow = $byPriceDesc[0] ?? null;

    // Pool valid prices
    $pricePool = array_filter([
        $quotedPrice,
        $mostRecentRow['price_unit'] ?? null,
        $bestPriceRow['price_unit'] ?? null,
        $worstPriceRow['price_unit'] ?? null,
    ], fn($p) => $p !== null && $p > 0);

    $uniquePrices = array_unique($pricePool);
    $hasVariation = count($uniquePrices) > 1;
    $globalMin = $hasVariation ? min($uniquePrices) : null;
    $globalMax = $hasVariation ? max($uniquePrices) : null;

    $isCurrentBest = $hasVariation && $quotedPrice == $globalMin;
    $isCurrentWorst = $hasVariation && $quotedPrice == $globalMax;
    $currentClass = 'price-current';
    if ($isCurrentBest) $currentClass = 'price-best';
    elseif ($isCurrentWorst) $currentClass = 'price-worst';

    $isLatestBest = $hasVariation && $mostRecentRow && $mostRecentRow['price_unit'] == $globalMin;
    $isLatestWorst = $hasVariation && $mostRecentRow && $mostRecentRow['price_unit'] == $globalMax;

    $showBestRow = $hasVariation && $bestPriceRow && ($bestPriceRow['order_id'] ?? null) !== ($mostRecentRow['order_id'] ?? null) && ($bestPriceRow['price_unit'] < ($mostRecentRow['price_unit'] ?? 0));
    $showWorstRow = $hasVariation && $worstPriceRow && ($worstPriceRow['order_id'] ?? null) !== ($mostRecentRow['order_id'] ?? null) && ($worstPriceRow['order_id'] ?? null) !== ($bestPriceRow['order_id'] ?? null) && ($worstPriceRow['price_unit'] > ($mostRecentRow['price_unit'] ?? 0));
@endphp

<div class="card product-item-card mb-4 shadow-sm" style="border: 2px solid #94a3b8; border-radius: 8px; overflow: hidden;">
    <div class="card-header py-2 d-flex align-items-center gap-2 flex-wrap" style="background-color: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
        <i class="bi bi-car-front-fill text-primary"></i>
        <span class="badge bg-secondary">{{ $cleanProductName }}</span>
        <span class="badge bg-white text-dark border">{{ $qty }} {{ $uom }}</span>
        <span class="ms-auto text-muted small">
            RFQ Unit Price:&nbsp;
            <strong @class([
                'text-success' => $isCurrentBest,
                'text-danger'  => $isCurrentWorst,
            ])>
                {{ $currency }} {{ number_format($quotedPrice, 0, ',', '.') }}
            </strong>
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle mb-0">
                <thead>
                    <tr class="table-light text-muted small text-nowrap">
                        <th class="ps-3" style="width: 30%;">Current Vendor</th>
                        <th class="text-center" style="width: 16%;">Quoted Price</th>
                        <th class="text-center" style="width: 6%;">Qty</th>
                        <th class="text-center" style="width: 6%;">UoM</th>
                        <th style="width: 15%;">RFQ / PO</th>
                        <th style="width: 14%;">Date</th>
                        <th class="text-center" style="width: 13%;">Note</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="{{ $currentClass }}">
                        <td class="ps-3 fw-semibold">
                            <i class="bi bi-star-fill text-warning me-1"></i>{{ $rfqVendorNm }}
                        </td>
                        <td class="text-center fw-bold">
                            {{ $currency }} {{ number_format($quotedPrice, 0, ',', '.') }}
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
                            <th class="text-center">Unit Price</th>
                            <th class="text-center">Qty</th>
                            <th class="text-center">UoM</th>
                            <th>Past PO</th>
                            <th>Purchase Date</th>
                            <th class="text-center">Note</th>
                        </tr>

                        {{-- Row 2: Latest Purchase --}}
                        @if ($mostRecentRow)
                            <tr class="table-info">
                                <td class="ps-3 fw-semibold" style="box-shadow: inset 4px 0 0 #0d6efd;">{{ $mostRecentRow['vendor_name'] }}</td>
                                <td class="text-center fw-semibold">
                                    {{ $currency }} {{ number_format($mostRecentRow['price_unit'], 0, ',', '.') }}
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
                                    @if ($isLatestBest)
                                        <span class="badge bg-success">Best Price</span>
                                    @elseif ($isLatestWorst)
                                        <span class="badge bg-danger">Highest Price</span>
                                    @endif
                                </td>
                            </tr>
                        @endif

                        {{-- Row 3: Best Price (shown if distinct from Latest Purchase) --}}
                        @if ($showBestRow)
                            <tr class="price-best">
                                <td class="ps-3 fw-semibold">{{ $bestPriceRow['vendor_name'] }}</td>
                                <td class="text-center fw-semibold">
                                    {{ $currency }} {{ number_format($bestPriceRow['price_unit'], 0, ',', '.') }}
                                </td>
                                <td class="text-center">{{ $bestPriceRow['product_qty'] }}</td>
                                <td class="text-center">{{ $bestPriceRow['uom'] }}</td>
                                <td>
                                    <span class="text-muted small">{{ $bestPriceRow['po_name'] }}</span>
                                </td>
                                <td class="text-muted">
                                    {{ \Carbon\Carbon::parse($bestPriceRow['date'])->format('d M Y H:i') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success">Best Price</span>
                                </td>
                            </tr>
                        @endif

                        {{-- Row 4: Highest Price (shown if distinct from Latest and Best Price) --}}
                        @if ($showWorstRow)
                            <tr class="price-worst">
                                <td class="ps-3">{{ $worstPriceRow['vendor_name'] }}</td>
                                <td class="text-center">
                                    {{ $currency }} {{ number_format($worstPriceRow['price_unit'], 0, ',', '.') }}
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
                                    <span class="badge bg-danger">Highest Price</span>
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

