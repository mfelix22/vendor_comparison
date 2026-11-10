@extends('layouts.app')

@section('title', 'Car Vendor Comparison Approvals')

@section('content')

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">
                <i class="bi bi-car-front-fill text-primary me-2"></i>Car Comparison Approvals (Unit Baru)
            </h4>
            <div class="text-muted small">Supervisor & Manager review dashboard for vehicle comparisons</div>
        </div>
    </div>

    {{-- Stats cards --}}
    <div class="row row-cols-2 row-cols-md-5 g-3 mb-4">
        <div class="col">
            <div class="card text-center border-warning shadow-sm h-100 stat-filter-card" data-filter="pending_supervisor" role="button" title="Filter by Pending Supervisor">
                <div class="card-body py-3">
                    <div class="display-6 fw-bold text-warning">{{ $stats['pending_supervisor'] }}</div>
                    <div class="small text-muted mt-1 fw-semibold">Pending Supervisor</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-center border-info shadow-sm h-100 stat-filter-card" data-filter="pending_manager" role="button" title="Filter by Pending Manager">
                <div class="card-body py-3">
                    <div class="display-6 fw-bold text-info">{{ $stats['pending_manager'] }}</div>
                    <div class="small text-muted mt-1 fw-semibold">Pending Manager</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-center border-success shadow-sm h-100 stat-filter-card" data-filter="approved" role="button" title="Filter by Approved">
                <div class="card-body py-3">
                    <div class="display-6 fw-bold text-success">{{ $stats['approved'] }}</div>
                    <div class="small text-muted mt-1 fw-semibold">Approved</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-center border-danger shadow-sm h-100 stat-filter-card" data-filter="rejected" role="button" title="Filter by Rejected">
                <div class="card-body py-3">
                    <div class="display-6 fw-bold text-danger">{{ $stats['rejected'] }}</div>
                    <div class="small text-muted mt-1 fw-semibold">Rejected</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-center border-secondary shadow-sm h-100 stat-filter-card" data-filter="cancelled" role="button" title="Filter by Cancelled">
                <div class="card-body py-3">
                    <div class="display-6 fw-bold text-secondary">{{ $stats['cancelled'] }}</div>
                    <div class="small text-muted mt-1 fw-semibold">Cancelled</div>
                </div>
            </div>
        </div>
    </div>

    @if ($comparisons->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            No Car comparisons submitted yet.
            Go to <a href="{{ route('car_rfq.index') }}" class="fw-bold">Car RFQ List</a> and click <strong>Compare</strong> to submit one.
        </div>
    @else
        {{-- Search / status filter bar --}}
        <div class="mb-3 d-flex flex-wrap gap-2">
            <div class="input-group input-group-sm" style="max-width:360px;">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" id="cmpSearch" class="form-control" placeholder="Search PO, car model, dealer…">
            </div>
            <div class="input-group input-group-sm" style="max-width:220px;">
                <span class="input-group-text"><i class="bi bi-funnel"></i></span>
                <select id="cmpStatus" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending_supervisor">Pending Supervisor</option>
                    <option value="pending_manager">Pending Manager</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="cmpTable">
                        <thead class="table-light align-middle">
                            <tr>
                                <th class="ps-3 text-nowrap" style="min-width:140px;">PO Reference</th>
                                <th style="min-width:240px;">Vehicle Model</th>
                                <th class="text-center text-nowrap" style="width:80px;">Qty</th>
                                <th style="min-width:200px;">Recommended Dealer</th>
                                <th class="text-nowrap" style="min-width:130px;">Prepared By</th>
                                <th class="text-nowrap" style="min-width:130px;">Prepared At</th>
                                <th class="text-center text-nowrap" style="width:130px;">Status</th>
                                <th class="text-center text-nowrap" style="width:140px;">Approvals</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($comparisons as $c)
                                <tr data-href="{{ route('car_comparisons.show', $c) }}"
                                    data-status="{{ $c->status }}"
                                    data-search="{{ strtolower($c->comparison_code . ' ' . $c->po_name . ' ' . $c->car_model . ' ' . $c->selected_vendor) }}">
                                    <td class="ps-3 text-nowrap">
                                        <div class="fw-semibold text-primary">{{ $c->po_name }}</div>
                                        <div class="text-muted small">{{ $c->comparison_code ?: '—' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $c->car_model ?: '—' }}</div>
                                        @if($c->warna || $c->bbn || $c->vin_code)
                                            <div class="text-muted small">
                                                @if($c->warna)<span class="me-2"><i class="bi bi-palette me-1"></i>{{ $c->warna }}</span>@endif
                                                @if($c->bbn)<span class="me-2"><i class="bi bi-geo-alt me-1"></i>{{ $c->bbn }}</span>@endif
                                                @if($c->vin_code)<span><i class="bi bi-calendar me-1"></i>{{ $c->vin_code }}</span>@endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center text-nowrap fw-semibold">
                                        {{ $c->qty }} {{ $c->uom }}
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-success">{{ $c->selected_vendor ?: '—' }}</span>
                                    </td>
                                    <td class="text-nowrap">
                                        {{ $c->prepared_by_name }}
                                    </td>
                                    <td class="text-muted small text-nowrap">
                                        {{ $c->created_at->format('d M Y H:i') }}
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <span class="badge {{ $c->statusBadgeClass() }}">
                                            {{ $c->statusLabel() }}
                                        </span>
                                        @if ($c->isRejected())
                                            <div class="text-danger small mt-1" title="{{ $c->rejection_reason }}">
                                                <i class="bi bi-exclamation-circle me-1"></i>{{ Str::limit($c->rejection_reason, 25) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center text-nowrap">
                                        @if ($c->manager_approved_at)
                                            <span class="text-success small fw-semibold">
                                                <i class="bi bi-check-circle-fill me-1"></i>{{ $c->manager->name ?? 'Manager' }}
                                                @if ($c->isBypassed())
                                                    <span class="badge bg-warning text-dark ms-1" title="Supervisor step bypassed">Bypass</span>
                                                @endif
                                                <br>
                                                <span class="text-muted fw-normal">{{ $c->manager_approved_at->format('d M Y') }}</span>
                                            </span>
                                        @elseif ($c->supervisor_approved_at)
                                            <span class="text-info small fw-semibold">
                                                <i class="bi bi-check-circle me-1"></i>SPV: {{ $c->supervisor->name ?? 'Supervisor' }}<br>
                                                <span class="text-muted fw-normal">{{ $c->supervisor_approved_at->format('d M Y') }}</span>
                                            </span>
                                        @elseif ($c->isRejected() && $c->rejectedBy)
                                            <span class="text-danger small"><i class="bi bi-x-circle-fill me-1"></i>Rejected</span>
                                        @else
                                            <span class="text-muted small">—</span>
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
        #cmpTable thead th,
        #cmpTable tbody td {
            vertical-align: middle !important;
        }
        #cmpTable tbody tr {
            cursor: pointer;
        }
        .stat-filter-card {
            cursor: pointer;
            user-select: none;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .stat-filter-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        }
        .stat-filter-card.active-card {
            border-width: 2px !important;
            background-color: rgba(238, 242, 255, 0.6);
        }
    </style>
@endpush

    <script>
        (function() {
            const searchEl = document.getElementById('cmpSearch');
            const statusEl = document.getElementById('cmpStatus');
            const rows = document.querySelectorAll('#cmpTable tbody tr');
            const statCards = document.querySelectorAll('.stat-filter-card');

            function applyFilters() {
                const q = searchEl ? searchEl.value.toLowerCase().trim() : '';
                const st = statusEl ? statusEl.value : '';

                // Update active state on stat cards
                statCards.forEach(card => {
                    if (st && card.dataset.filter === st) {
                        card.classList.add('active-card');
                    } else {
                        card.classList.remove('active-card');
                    }
                });

                rows.forEach(r => {
                    const matchQ = !q || r.dataset.search.includes(q);
                    const matchSt = !st || r.dataset.status === st;
                    r.style.display = (matchQ && matchSt) ? '' : 'none';

                    // Update row data-href with status query param for seamless navigation
                    if (r.dataset.href) {
                        try {
                            let url = new URL(r.dataset.href, window.location.origin);
                            if (st) {
                                url.searchParams.set('status', st);
                            } else {
                                url.searchParams.delete('status');
                            }
                            r.dataset.href = url.toString();
                        } catch(e) {}
                    }
                });
            }

            if (searchEl) searchEl.addEventListener('input', applyFilters);
            if (statusEl) statusEl.addEventListener('change', applyFilters);

            // Stat card click toggle
            statCards.forEach(card => {
                card.addEventListener('click', function() {
                    const filterVal = this.dataset.filter;
                    if (statusEl.value === filterVal) {
                        statusEl.value = '';
                    } else {
                        statusEl.value = filterVal;
                    }
                    applyFilters();
                });
            });

            // Enable clicking anywhere on table rows to view details
            const cmpTableBody = document.querySelector('#cmpTable tbody');
            if (cmpTableBody) {
                cmpTableBody.addEventListener('click', function(e) {
                    // Ignore clicks on explicit links, buttons, or form controls
                    if (e.target.closest('a, button, input, select, textarea')) {
                        return;
                    }
                    // Ignore if user is selecting text (e.g. copying PO number)
                    const selection = window.getSelection();
                    if (selection && selection.toString().trim().length > 0) {
                        return;
                    }
                    const row = e.target.closest('tr');
                    if (!row || !cmpTableBody.contains(row)) return;

                    if (row.dataset.href) {
                        if (e.ctrlKey || e.metaKey) {
                            window.open(row.dataset.href, '_blank');
                        } else {
                            window.location.href = row.dataset.href;
                        }
                    }
                });

                cmpTableBody.addEventListener('auxclick', function(e) {
                    if (e.button === 1) { // Middle click
                        if (e.target.closest('a, button, input, select, textarea')) {
                            return;
                        }
                        const row = e.target.closest('tr');
                        if (!row || !cmpTableBody.contains(row)) return;

                        if (row.dataset.href) {
                            window.open(row.dataset.href, '_blank');
                        }
                    }
                });
            }
        })();
    </script>

@endsection

