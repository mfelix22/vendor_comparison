@extends('layouts.app')

@section('title', 'RFQ Car List')

@section('content')

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-file-earmark-text me-2"></i>Request For Quotation (RFQ) Car
        </h4>
        <div class="d-flex align-items-center gap-3">
            @if ($cachedAt)
                <span class="text-muted small">
                    <i class="bi bi-clock me-1"></i>Last synced: {{ \Illuminate\Support\Carbon::parse($cachedAt)->diffForHumans() }}
                </span>
            @endif
            <form method="POST" action="{{ route('car_rfq.refresh') }}">
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
            <i class="bi bi-info-circle me-2"></i>No Car RFQs found in the system.
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
                            <option value="purchase">Purchase Order</option>
                            <option value="done">Done</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="rfqClvpFilter" class="form-select form-select-sm">
                            <option value="">All CLVPs</option>
                            <option value="has">Has CLVP</option>
                            <option value="none">No CLVP</option>
                        </select>
                    </div>
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
                        <thead class="align-middle">
                            <tr>
                                <th class="ps-3" style="width:160px; white-space:nowrap;">PO Reference</th>
                                <th>Vendor</th>
                                <th style="white-space:nowrap;">Source Document</th>
                                <th>Buyer</th>
                                <th style="white-space:nowrap;">Purchase SPV</th>
                                <th class="text-center">Lines</th>
                                <th style="white-space:nowrap;">Order Deadline</th>
                                <th class="text-end" style="white-space:nowrap; min-width:140px;">Amount Total</th>
                                <th class="text-center" style="width:80px">Status</th>
                                <th class="text-center" style="width:130px; white-space:nowrap;">CAR CLVP</th>
                                <th class="text-center" style="width:100px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rfqs as $rfq)
                                @php
                                    $clvp = $existingComparisons[$rfq['id']] ?? null;
                                    $vendorName = is_array($rfq['partner_id']) ? $rfq['partner_id'][1] : '';
                                    $currency = is_array($rfq['currency_id']) ? $rfq['currency_id'][1] : 'IDR';
                                @endphp
                                <tr data-href="{{ ($clvp && !in_array($clvp->status, ['rejected', 'cancelled'])) ? route('car_comparisons.show', $clvp->id) : route('car_rfq.show', $rfq['id']) }}"
                                    data-rfq-name="{{ strtolower($rfq['name']) }}"
                                    data-vendor="{{ strtolower($vendorName) }}"
                                    data-origin="{{ strtolower($rfq['origin'] ?? '') }}"
                                    data-state="{{ $rfq['state'] }}"
                                    data-clvp="{{ $clvp ? 'has' : 'none' }}">
                                    <td class="ps-3 fw-semibold">{{ $rfq['name'] }}</td>
                                    <td>{{ $vendorName ?: '—' }}</td>
                                    <td class="text-muted">{{ $rfq['origin'] ?: '—' }}</td>
                                    <td class="text-muted">
                                        {{ is_array($rfq['user_id']) ? $rfq['user_id'][1] : '—' }}
                                    </td>
                                    <td class="text-muted">
                                        {{ is_array($rfq['purchase_spv_id'] ?? null) ? $rfq['purchase_spv_id'][1] : '—' }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary rounded-pill">
                                            {{ count($rfq['order_line'] ?? []) }}
                                        </span>
                                    </td>
                                    <td class="text-muted">
                                        {{ $rfq['date_order'] ? \Illuminate\Support\Carbon::parse($rfq['date_order'])->format('d M Y H:i') : '—' }}
                                    </td>
                                    <td class="text-end fw-semibold" style="white-space:nowrap;">
                                        {{ $currency }} {{ number_format($rfq['amount_total'] ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        @if ($rfq['state'] === 'sent')
                                            <span class="badge badge-sent">RFQ Sent</span>
                                        @elseif ($rfq['state'] === 'done')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Done</span>
                                        @elseif ($rfq['state'] === 'purchase')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">PO</span>
                                        @else
                                            <span class="badge badge-rfq">RFQ</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($clvp)
                                            <a href="{{ route('car_comparisons.show', $clvp->id) }}"
                                                class="badge text-decoration-none {{ $clvp->statusBadgeClass() }}">
                                                {{ $clvp->statusLabel() }}
                                            </a>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($clvp && !in_array($clvp->status, ['rejected', 'cancelled']))
                                            <a href="{{ route('car_comparisons.show', $clvp->id) }}"
                                                class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-eye me-1"></i>View
                                            </a>
                                        @else
                                            <a href="{{ route('car_rfq.show', $rfq['id']) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-search me-1"></i>Compare
                                            </a>
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

@push('styles')
    <style>
        #rfqTable tbody tr {
            cursor: pointer;
        }
    </style>
@endpush

    <script>
        (function() {
            const searchEl = document.getElementById('rfqSearch');
            const statusEl = document.getElementById('rfqStatusFilter');
            const clvpEl = document.getElementById('rfqClvpFilter');
            const countEl = document.getElementById('rfqCount');
            const rows = document.querySelectorAll('#rfqTable tbody tr');

            function filterTable() {
                const q = searchEl ? searchEl.value.toLowerCase().trim() : '';
                const status = statusEl ? statusEl.value : '';
                const clvp = clvpEl ? clvpEl.value : '';
                let visible = 0;

                rows.forEach(row => {
                    const matchQ = !q ||
                        row.dataset.rfqName.includes(q) ||
                        row.dataset.vendor.includes(q) ||
                        row.dataset.origin.includes(q);
                    const matchStatus = !status || row.dataset.state === status;
                    const matchClvp = !clvp || row.dataset.clvp === clvp;

                    const show = matchQ && matchStatus && matchClvp;
                    row.style.display = show ? '' : 'none';
                    if (show) visible++;
                });

                if (countEl) countEl.textContent = visible + ' record' + (visible !== 1 ? 's' : '');
            }

            if (searchEl) searchEl.addEventListener('input', filterTable);
            if (statusEl) statusEl.addEventListener('change', filterTable);
            if (clvpEl) clvpEl.addEventListener('change', filterTable);

            // Enable clicking anywhere on table rows
            const rfqTableBody = document.querySelector('#rfqTable tbody');
            if (rfqTableBody) {
                rfqTableBody.addEventListener('click', function(e) {
                    if (e.target.closest('a, button, input, select, textarea')) {
                        return;
                    }
                    const selection = window.getSelection();
                    if (selection && selection.toString().trim().length > 0) {
                        return;
                    }
                    const row = e.target.closest('tr');
                    if (!row || !rfqTableBody.contains(row)) return;

                    if (row.dataset.href) {
                        if (e.ctrlKey || e.metaKey) {
                            window.open(row.dataset.href, '_blank');
                        } else {
                            window.location.href = row.dataset.href;
                        }
                    }
                });

                rfqTableBody.addEventListener('auxclick', function(e) {
                    if (e.button === 1) { // Middle click
                        if (e.target.closest('a, button, input, select, textarea')) {
                            return;
                        }
                        const row = e.target.closest('tr');
                        if (!row || !rfqTableBody.contains(row)) return;

                        if (row.dataset.href) {
                            window.open(row.dataset.href, '_blank');
                        }
                    }
                });
            }
        })();
    </script>

@endsection
