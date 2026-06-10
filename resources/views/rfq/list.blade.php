@extends('layouts.app')

@section('title', 'RFQ List')

@section('content')

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="fw-bold mb-0"><i class="bi bi-file-earmark-text me-2"></i>RFQ List <span class="badge bg-secondary ms-2" style="font-size:.7rem">≤ Rp 250.000</span></h4>
        <div class="d-flex align-items-center gap-3">
            @if ($cachedAt)
                <span class="text-muted small">
                    <i class="bi bi-clock me-1"></i>Last synced: {{ $cachedAt->diffForHumans() }}
                </span>
            @endif
            <form method="POST" action="{{ route('rfq.refresh') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh from Odoo
                </button>
            </form>
        </div>
    </div>

    {{-- Odoo connection error --}}
    @if ($odooError)
        <div class="alert alert-danger d-flex align-items-center gap-2">
            <i class="bi bi-wifi-off fs-5"></i>
            <div>
                <strong>Cannot reach Odoo.</strong> Showing cached data if available.<br>
                <small class="text-muted">{{ $odooError }}</small>
            </div>
        </div>
    @endif

    @if (empty($rfqs) && !$odooError)
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>No RFQs with amount ≤ Rp 250.000 found.
        </div>
    @endif

    @if (!empty($rfqs))
        {{-- Search & Filter bar --}}
        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="rfqSearch" class="form-control"
                                placeholder="Search PO reference, vendor, source document…">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select id="rfqStatusFilter" class="form-select form-select-sm">
                            <option value="">All statuses</option>
                            <option value="draft">RFQ</option>
                            <option value="sent">RFQ Sent</option>
                        </select>
                    </div>
                    <div class="col-md-3"></div>
                    <div class="col-md-1 text-end">
                        <span id="rfqCount" class="text-muted small">{{ count($rfqs) }} records</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="rfqTable">
                        <thead>
                            <tr>
                                <th class="ps-3" style="width:160px">PO Reference</th>
                                <th>Vendor</th>
                                <th>Source Document</th>
                                <th>Buyer</th>
                                <th class="text-center">Lines</th>
                                <th>Order Deadline</th>
                                <th class="text-end">Amount Total</th>
                                <th class="text-center" style="width:80px">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rfqs as $rfq)
                                @php
                                    $vendorName = is_array($rfq['partner_id']) ? $rfq['partner_id'][1] : '';
                                    $currency = is_array($rfq['currency_id']) ? $rfq['currency_id'][1] : 'IDR';
                                    $modalId = 'rfqModal_' . $rfq['id'];
                                @endphp
                                <tr data-rfq-name="{{ strtolower($rfq['name']) }}"
                                    data-vendor="{{ strtolower($vendorName) }}"
                                    data-origin="{{ strtolower($rfq['origin'] ?? '') }}"
                                    data-state="{{ $rfq['state'] }}">
                                    <td class="ps-3 fw-semibold">
                                        <a href="#" class="text-decoration-none"
                                            data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                                            {{ $rfq['name'] }}
                                        </a>
                                    </td>
                                    <td>{{ $vendorName ?: '—' }}</td>
                                    <td class="text-muted">{{ $rfq['origin'] ?: '—' }}</td>
                                    <td class="text-muted">
                                        {{ is_array($rfq['user_id']) ? $rfq['user_id'][1] : '—' }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary rounded-pill">
                                            {{ count($rfq['order_line']) }}
                                        </span>
                                    </td>
                                    <td class="text-muted">
                                        {{ $rfq['date_order'] ? \Illuminate\Support\Carbon::parse($rfq['date_order'])->format('d M Y H:i') : '—' }}
                                    </td>
                                    <td class="text-end fw-semibold">
                                        {{ $currency }} {{ number_format($rfq['amount_total'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        @if ($rfq['state'] === 'sent')
                                            <span class="badge badge-sent">RFQ Sent</span>
                                        @else
                                            <span class="badge badge-rfq">RFQ</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Per-RFQ product modals --}}
    @foreach ($rfqs as $rfq)
        @php
            $vendorName = is_array($rfq['partner_id']) ? $rfq['partner_id'][1] : '—';
            $currency   = is_array($rfq['currency_id']) ? $rfq['currency_id'][1] : 'IDR';
            $modalId    = 'rfqModal_' . $rfq['id'];
            $lines      = $linesByRfq[$rfq['id']] ?? [];
        @endphp
        <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header" style="background:var(--brand-light)">
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="{{ $modalId }}Label">
                                <i class="bi bi-file-earmark-text me-2"></i>{{ $rfq['name'] }}
                            </h5>
                            <div class="text-muted small mt-1">
                                {{ $vendorName }}
                                @if ($rfq['origin'])
                                    &middot; {{ $rfq['origin'] }}
                                @endif
                                &middot; {{ $currency }} {{ number_format($rfq['amount_total'], 0, ',', '.') }}
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        @if (empty($lines))
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-inbox fs-4 d-block mb-2"></i>No product lines available.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-3">Product</th>
                                            <th class="text-muted small ps-3" style="font-weight:400">Internal Ref</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-center">UoM</th>
                                            <th class="text-end">Unit Price</th>
                                            <th class="text-end pe-3">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($lines as $line)
                                            @php
                                                $productName = is_array($line['product_id']) ? $line['product_id'][1] : ($line['name'] ?? '—');
                                                $internalRef = $line['default_code'] ?? '';
                                                $uom         = is_array($line['product_uom']) ? $line['product_uom'][1] : '';
                                            @endphp
                                            <tr>
                                                <td class="ps-3">
                                                    <div class="fw-semibold small">{{ $productName }}</div>
                                                    @if ($line['name'] && $line['name'] !== $productName)
                                                        <div class="text-muted" style="font-size:.75rem">{{ $line['name'] }}</div>
                                                    @endif
                                                </td>
                                                <td class="text-muted small ps-3">{{ $internalRef ?: '—' }}</td>
                                                <td class="text-center">{{ $line['product_qty'] }}</td>
                                                <td class="text-center text-muted small">{{ $uom }}</td>
                                                <td class="text-end">
                                                    {{ number_format($line['price_unit'], 0, ',', '.') }}
                                                </td>
                                                <td class="text-end pe-3 fw-semibold">
                                                    {{ number_format($line['price_subtotal'], 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-light">
                                            <td colspan="5" class="text-end pe-3 fw-semibold small ps-3">Total</td>
                                            <td class="text-end pe-3 fw-bold">
                                                {{ $currency }} {{ number_format($rfq['amount_total'], 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        (function() {
            const searchEl = document.getElementById('rfqSearch');
            const statusEl = document.getElementById('rfqStatusFilter');
            const countEl  = document.getElementById('rfqCount');
            const rows     = document.querySelectorAll('#rfqTable tbody tr');

            function filterTable() {
                const q      = searchEl.value.toLowerCase().trim();
                const status = statusEl.value;
                let visible  = 0;

                rows.forEach(row => {
                    const matchQ = !q ||
                        row.dataset.rfqName.includes(q) ||
                        row.dataset.vendor.includes(q) ||
                        row.dataset.origin.includes(q);
                    const matchStatus = !status || row.dataset.state === status;
                    const show = matchQ && matchStatus;
                    row.style.display = show ? '' : 'none';
                    if (show) visible++;
                });

                countEl.textContent = visible + ' record' + (visible !== 1 ? 's' : '');
            }

            searchEl.addEventListener('input', filterTable);
            statusEl.addEventListener('change', filterTable);
        })();
    </script>

@endsection
