@extends('layouts.app')

@section('title', 'Master Data – Costing Portal')

@section('content')

    {{-- Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('costing.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
                <h4 class="fw-bold mb-0"><i class="bi bi-database me-2 text-primary"></i>Costing Master Data</h4>
            </div>
            <p class="text-muted small mb-0 mt-1">Manage Brand, Type Kendaraan, Lokasi Pemakaian (514 baseline), and BBN (Samsat) strictly for Costing Portal.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form method="POST" action="{{ route('costing.master.sync-odoo') }}" class="d-inline">
                @csrf
                <input type="hidden" name="tab" value="{{ $tab }}">
                <button type="submit" class="btn btn-outline-secondary btn-sm"
                    onclick="return confirm('Sync newly added records from Odoo? Your local custom entries will be safely preserved.')">
                    <i class="bi bi-arrow-clockwise me-1"></i>Sync from Odoo
                </button>
            </form>

            @if ($tab === 'brands')
                <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addBrandModal">
                    <i class="bi bi-plus-lg me-1"></i>Add Brand
                </button>
            @elseif ($tab === 'types')
                <a href="{{ route('costing.master.vehicle-types.template') }}" class="btn btn-outline-success btn-sm px-3 shadow-sm" title="Download Excel Template untuk Harga OTR & Diskon">
                    <i class="bi bi-file-earmark-excel me-1"></i>Template Excel
                </a>
                <button type="button" class="btn btn-outline-primary btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#importPricesModal">
                    <i class="bi bi-file-earmark-arrow-up me-1"></i>Import Excel
                </button>
                <button type="button" class="btn btn-success btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addTypeModal">
                    <i class="bi bi-plus-lg me-1"></i>Add Vehicle Type
                </button>
            @elseif ($tab === 'pemakaian')
                <button type="button" class="btn btn-info text-white btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addPemakaianModal">
                    <i class="bi bi-plus-lg me-1"></i>Add Lokasi Pemakaian
                </button>
            @elseif ($tab === 'bbn')
                <button type="button" class="btn btn-danger btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addBbnModal">
                    <i class="bi bi-plus-lg me-1"></i>Add BBN (Samsat)
                </button>
            @endif
        </div>
    </div>

    {{-- Feedback Messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <div class="fw-semibold mb-1"><i class="bi bi-exclamation-circle me-1"></i>Please fix the following errors:</div>
            <ul class="mb-0 small ps-3">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Master Tabs Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom p-0">
            <ul class="nav nav-tabs card-header-tabs m-0 border-0" id="masterTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link py-3 px-4 fw-semibold {{ $tab === 'brands' ? 'active text-primary border-bottom border-primary border-2' : 'text-muted' }}"
                        href="{{ route('costing.master.index', ['tab' => 'brands']) }}">
                        <i class="bi bi-tag me-2"></i>Brand Kendaraan
                        <span class="badge bg-primary-subtle text-primary rounded-pill ms-1">{{ $brands->total() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 px-4 fw-semibold {{ $tab === 'types' ? 'active text-success border-bottom border-success border-2' : 'text-muted' }}"
                        href="{{ route('costing.master.index', ['tab' => 'types']) }}">
                        <i class="bi bi-car-front me-2"></i>Type Kendaraan
                        <span class="badge bg-success-subtle text-success rounded-pill ms-1">{{ $vehicleTypes->total() }}</span>
                        @if ($pendingPriceCount > 0)
                            <span class="badge bg-warning text-dark rounded-pill ms-1" title="{{ $pendingPriceCount }} perubahan harga menunggu persetujuan Purchasing Manager">
                                <i class="bi bi-clock-history me-1"></i>{{ $pendingPriceCount }} Pending
                            </span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 px-4 fw-semibold {{ $tab === 'pemakaian' ? 'active text-info border-bottom border-info border-2' : 'text-muted' }}"
                        href="{{ route('costing.master.index', ['tab' => 'pemakaian']) }}">
                        <i class="bi bi-geo-alt me-2"></i>Lokasi Pemakaian
                        <span class="badge bg-info-subtle text-info rounded-pill ms-1">{{ $pemakaianList->total() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 px-4 fw-semibold {{ $tab === 'bbn' ? 'active text-danger border-bottom border-danger border-2' : 'text-muted' }}"
                        href="{{ route('costing.master.index', ['tab' => 'bbn']) }}">
                        <i class="bi bi-card-heading me-2"></i>BBN (Samsat)
                        <span class="badge bg-danger-subtle text-danger rounded-pill ms-1">{{ $bbnList->total() }}</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body p-0">

            {{-- ======================================================== --}}
            {{-- TAB 1: BRANDS                                            --}}
            {{-- ======================================================== --}}
            @if ($tab === 'brands')
                <div class="p-3 bg-light border-bottom">
                    <form method="GET" action="{{ route('costing.master.index') }}" class="row g-2 align-items-center">
                        <input type="hidden" name="tab" value="brands">
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" name="search_brand" class="form-control border-start-0"
                                    placeholder="Search Brand Name..." value="{{ request('search_brand') }}">
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-secondary">Search</button>
                            @if (request('search_brand'))
                                <a href="{{ route('costing.master.index', ['tab' => 'brands']) }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 py-3" style="width: 60px;">#</th>
                                <th>Brand Name</th>
                                <th class="text-center" style="width: 160px;">Vehicle Types</th>
                                <th style="width: 140px;">Source</th>
                                <th style="width: 110px;">Status</th>
                                <th class="text-center pe-3" style="width: 140px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($brands as $idx => $br)
                                <tr>
                                    <td class="ps-3 text-muted small">{{ $brands->firstItem() + $idx }}</td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6">{{ $br->name }}</div>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('costing.master.index', ['tab' => 'types', 'filter_brand' => $br->name]) }}"
                                            class="badge bg-light text-dark border text-decoration-none px-2 py-1">
                                            <i class="bi bi-car-front me-1"></i>{{ $br->vehicle_types_count }} models
                                        </a>
                                    </td>
                                    <td>
                                        @if ($br->odoo_brand_id)
                                            <span class="badge bg-info-subtle text-info border border-info-subtle">
                                                <i class="bi bi-cloud-check me-1"></i>Odoo
                                            </span>
                                        @else
                                            <span class="badge border" style="background-color: #ede9fe; color: #6d28d9; border-color: #d8d4f8 !important;">
                                                <i class="bi bi-person me-1"></i>Local Custom
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($br->is_active)
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary"
                                                onclick="openEditBrandModal('{{ $br->id }}', '{{ addslashes($br->name) }}', {{ $br->is_active ? 'true' : 'false' }})"
                                                title="Edit Brand">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" action="{{ route('costing.master.brands.destroy', $br) }}" class="d-inline"
                                                onsubmit="return confirm('Delete brand {{ $br->name }}? This will also remove its associated vehicle types.')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Delete Brand">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-tag display-6 d-block mb-2 text-muted"></i>
                                        No vehicle brands found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($brands->hasPages())
                    <div class="card-footer bg-white border-top py-2 px-3">
                        {{ $brands->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @endif

            {{-- ======================================================== --}}
            {{-- TAB 2: VEHICLE TYPES                                     --}}
            {{-- ======================================================== --}}
            @if ($tab === 'types')
                {{-- Manager Pending Notification Banner --}}
                @if ($pendingPriceCount > 0)
                    <div class="alert alert-warning border-0 shadow-sm d-flex flex-wrap align-items-center justify-content-between p-3 m-3 mb-0 rounded-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning text-dark fs-6 px-2 py-1"><i class="bi bi-exclamation-triangle-fill"></i></span>
                            <div>
                                <span class="fw-bold">Terdapat {{ $pendingPriceCount }} perubahan Harga OTR / Diskon yang menunggu persetujuan Purchasing Manager.</span>
                                <div class="small text-muted">Harga aktif di form Costing tidak akan berubah sampai disetujui.</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                            @if (request('price_status') !== 'pending')
                                <a href="{{ route('costing.master.index', ['tab' => 'types', 'price_status' => 'pending']) }}" class="btn btn-sm btn-dark">
                                    <i class="bi bi-funnel me-1"></i>Filter Menunggu Approval
                                </a>
                            @endif
                            @if (Auth::user()->isManager() || Auth::user()->isAdmin())
                                <form method="POST" action="{{ route('costing.master.vehicle-types.price-bulk-approve') }}" class="d-inline"
                                    onsubmit="return confirm('Apakah Anda yakin ingin MENYETUJUI SEMUA ({{ $pendingPriceCount }}) perubahan harga yang sedang pending?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success px-3 fw-semibold">
                                        <i class="bi bi-check-all me-1"></i>Approve Semua ({{ $pendingPriceCount }})
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="p-3 bg-light border-bottom">
                    <form method="GET" action="{{ route('costing.master.index') }}" class="row g-2 align-items-center">
                        <input type="hidden" name="tab" value="types">
                        <div class="col-md-3">
                            <select name="filter_brand" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">— Filter Brand (Semua) —</option>
                                @foreach ($allBrands as $bOpt)
                                    <option value="{{ $bOpt->name }}" {{ request('filter_brand') === $bOpt->name ? 'selected' : '' }}>
                                        {{ $bOpt->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="price_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">— Status Harga (Semua) —</option>
                                <option value="pending" {{ request('price_status') === 'pending' ? 'selected' : '' }}>
                                    ⏳ Menunggu Approval ({{ $pendingPriceCount }})
                                </option>
                                <option value="priced" {{ request('price_status') === 'priced' ? 'selected' : '' }}>
                                    ✓ Sudah Ada Harga OTR
                                </option>
                                <option value="unpriced" {{ request('price_status') === 'unpriced' ? 'selected' : '' }}>
                                    — Belum Ada Harga OTR (0)
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" name="search_type" class="form-control border-start-0"
                                    placeholder="Cari Model / Tipe Kendaraan..." value="{{ request('search_type') }}">
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
                            @if (request('search_type') || request('filter_brand') || request('price_status'))
                                <a href="{{ route('costing.master.index', ['tab' => 'types']) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 py-3" style="width: 50px;">#</th>
                                <th style="width: 120px;">Brand</th>
                                <th style="min-width: 260px;">Type / Model Kendaraan</th>
                                <th class="text-end" style="width: 150px;">Harga OTR</th>
                                <th class="text-end" style="width: 130px;">Diskon</th>
                                <th class="text-end" style="width: 150px;">Netto (Baseline)</th>
                                <th style="width: 100px;">Source</th>
                                <th style="width: 80px;">Status</th>
                                <th class="text-center pe-3" style="min-width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($vehicleTypes as $idx => $vt)
                                <tr>
                                    <td class="ps-3 text-muted small">{{ $vehicleTypes->firstItem() + $idx }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border fw-bold px-2 py-1">
                                            {{ $vt->brand_name }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark fs-6">{{ $vt->name }}</div>

                                        {{-- Pending Price Revision Box --}}
                                        @if ($vt->pendingPriceHistory)
                                            <div class="mt-2 p-2 rounded bg-warning-subtle border border-warning text-dark small shadow-sm">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <span class="badge bg-warning text-dark fw-bold">
                                                        <i class="bi bi-clock-history me-1"></i>Revisi Menunggu Approval
                                                    </span>
                                                    <span class="text-muted" style="font-size: 11px;">
                                                        {{ $vt->pendingPriceHistory->created_at ? $vt->pendingPriceHistory->created_at->diffForHumans() : '' }}
                                                    </span>
                                                </div>
                                                <div class="row g-2 text-secondary" style="font-size: 12px;">
                                                    <div class="col-sm-6 text-nowrap">
                                                        <span class="text-muted small d-block">Harga OTR Usulan:</span>
                                                        <strong class="text-dark font-monospace text-nowrap">Rp {{ number_format($vt->pendingPriceHistory->new_harga_otr, 0, ',', '.') }}</strong>
                                                        @if (abs((float)$vt->pendingPriceHistory->old_harga_otr - (float)$vt->pendingPriceHistory->new_harga_otr) >= 0.01)
                                                            <div class="text-muted small text-nowrap" style="font-size: 11px;">
                                                                Sebelumnya: <span class="text-decoration-line-through">Rp {{ number_format($vt->pendingPriceHistory->old_harga_otr, 0, ',', '.') }}</span>
                                                            </div>
                                                        @else
                                                            <div class="text-muted small text-nowrap" style="font-size: 11px;">
                                                                <span class="badge bg-light text-muted border py-0 px-1" style="font-size: 10px; font-weight: normal;">Tidak berubah</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="col-sm-6 text-nowrap">
                                                        <span class="text-muted small d-block">Diskon Usulan:</span>
                                                        <strong class="text-danger font-monospace text-nowrap">Rp {{ number_format($vt->pendingPriceHistory->new_diskon, 0, ',', '.') }}</strong>
                                                        @if (abs((float)$vt->pendingPriceHistory->old_diskon - (float)$vt->pendingPriceHistory->new_diskon) >= 0.01)
                                                            <div class="text-muted small text-nowrap" style="font-size: 11px;">
                                                                Sebelumnya: <span class="text-decoration-line-through">Rp {{ number_format($vt->pendingPriceHistory->old_diskon, 0, ',', '.') }}</span>
                                                            </div>
                                                        @else
                                                            <div class="text-muted small text-nowrap" style="font-size: 11px;">
                                                                <span class="badge bg-light text-muted border py-0 px-1" style="font-size: 10px; font-weight: normal;">Tidak berubah</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if ($vt->pendingPriceHistory->notes)
                                                    <div class="text-muted fst-italic mt-1" style="font-size: 11px;">
                                                        "{{ $vt->pendingPriceHistory->notes }}"
                                                    </div>
                                                @endif
                                                <div class="d-flex align-items-center justify-content-between mt-2 pt-1 border-top border-warning-subtle">
                                                    <span class="text-muted" style="font-size: 11px;">
                                                        Oleh: <strong>{{ $vt->pendingPriceHistory->requester->name ?? 'Staff' }}</strong>
                                                    </span>
                                                    @if (Auth::user()->isManager() || Auth::user()->isAdmin())
                                                        <div class="btn-group btn-group-sm">
                                                            <form method="POST" action="{{ route('costing.master.vehicle-types.price-approve', $vt->pendingPriceHistory) }}" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-success px-2 py-0"
                                                                    onclick="return confirm('Setujui revisi harga OTR & Diskon untuk {{ addslashes($vt->name) }}?')"
                                                                    title="Setujui Revisi Harga">
                                                                    <i class="bi bi-check-lg me-1"></i>Approve
                                                                </button>
                                                            </form>
                                                            <button type="button" class="btn btn-sm btn-outline-danger px-2 py-0"
                                                                onclick="openRejectPriceModal('{{ $vt->pendingPriceHistory->id }}', '{{ addslashes($vt->name) }}')"
                                                                title="Tolak Revisi">
                                                                <i class="bi bi-x-lg me-1"></i>Reject
                                                            </button>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($vt->harga_otr > 0)
                                            <span class="fw-bold text-dark font-monospace">Rp {{ number_format($vt->harga_otr, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-muted small fst-italic">Rp 0</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($vt->diskon > 0)
                                            <span class="fw-semibold text-danger font-monospace">Rp {{ number_format($vt->diskon, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-muted small fst-italic">Rp 0</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($vt->harga_netto > 0)
                                            <span class="fw-bold text-success font-monospace">Rp {{ number_format($vt->harga_netto, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-muted small fst-italic">Rp 0</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($vt->odoo_product_id)
                                            <span class="badge bg-info-subtle text-info border border-info-subtle">
                                                <i class="bi bi-cloud-check me-1"></i>Odoo
                                            </span>
                                        @else
                                            <span class="badge border" style="background-color: #ede9fe; color: #6d28d9; border-color: #d8d4f8 !important;">
                                                <i class="bi bi-person me-1"></i>Local Custom
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($vt->is_active)
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-3">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center text-nowrap"
                                                style="padding: 0.25rem 0.5rem;"
                                                onclick="openPriceHistoryModal('{{ $vt->id }}', '{{ addslashes($vt->name) }}')"
                                                title="Riwayat Perubahan Harga ({{ $vt->price_histories_count }} catatan)">
                                                <i class="bi bi-clock-history"></i>
                                                @if ($vt->price_histories_count > 0)
                                                    <span class="badge bg-secondary rounded-pill ms-1" style="font-size: 0.65rem; padding: 1px 4px; line-height: 1.1;">
                                                        {{ $vt->price_histories_count }}
                                                    </span>
                                                @endif
                                            </button>
                                            <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center justify-content-center"
                                                style="padding: 0.25rem 0.5rem;"
                                                onclick="openEditTypeModal('{{ $vt->id }}', '{{ $vt->costing_brand_id }}', '{{ addslashes($vt->name) }}', {{ (float)$vt->harga_otr }}, {{ (float)$vt->diskon }}, {{ $vt->is_active ? 'true' : 'false' }})"
                                                title="Edit Tipe &amp; Harga">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" action="{{ route('costing.master.vehicle-types.destroy', $vt) }}" class="d-inline"
                                                onsubmit="return confirm('Hapus tipe kendaraan {{ addslashes($vt->name) }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger d-inline-flex align-items-center justify-content-center"
                                                    style="padding: 0.25rem 0.5rem;" title="Hapus Tipe">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="bi bi-car-front display-6 d-block mb-2 text-muted"></i>
                                        Tidak ada data tipe kendaraan ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($vehicleTypes->hasPages())
                    <div class="card-footer bg-white border-top py-2 px-3">
                        {{ $vehicleTypes->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @endif

            {{-- ======================================================== --}}
            {{-- TAB 3: LOKASI PEMAKAIAN (514 BASELINE)                   --}}
            {{-- ======================================================== --}}
            @if ($tab === 'pemakaian')
                <div class="p-3 bg-light border-bottom">
                    <form method="GET" action="{{ route('costing.master.index') }}" class="row g-2 align-items-center">
                        <input type="hidden" name="tab" value="pemakaian">
                        <div class="col-md-3">
                            <select name="filter_pulau" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">— Filter by Pulau (All) —</option>
                                @foreach ($pulauList as $pOpt)
                                    <option value="{{ $pOpt }}" {{ request('filter_pulau') === $pOpt ? 'selected' : '' }}>
                                        {{ $pOpt }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="filter_provinsi" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">— Filter by Provinsi (All) —</option>
                                @foreach ($provinsiList as $prOpt)
                                    <option value="{{ $prOpt }}" {{ request('filter_provinsi') === $prOpt ? 'selected' : '' }}>
                                        {{ $prOpt }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" name="search_pemakaian" class="form-control border-start-0"
                                    placeholder="Search Kota / Kabupaten (e.g. Ungaran, Morowali, Surabaya)..." value="{{ request('search_pemakaian') }}">
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-secondary">Search</button>
                            @if (request('search_pemakaian') || request('filter_pulau') || request('filter_provinsi'))
                                <a href="{{ route('costing.master.index', ['tab' => 'pemakaian']) }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 py-3" style="width: 60px;">#</th>
                                <th>Lokasi Pemakaian (Kota / Kabupaten / Area)</th>
                                <th style="width: 220px;">Provinsi</th>
                                <th style="width: 180px;">Pulau</th>
                                <th style="width: 90px;">Status</th>
                                <th class="text-center pe-3" style="width: 110px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pemakaianList as $idx => $lok)
                                <tr>
                                    <td class="ps-3 text-muted small">{{ $pemakaianList->firstItem() + $idx }}</td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6">{{ $lok->name }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $lok->provinsi }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary border">{{ $lok->pulau }}</span>
                                    </td>
                                    <td>
                                        @if ($lok->is_active)
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary"
                                                onclick="openEditPemakaianModal('{{ $lok->id }}', '{{ addslashes($lok->name) }}', '{{ addslashes($lok->pulau) }}', '{{ addslashes($lok->provinsi) }}', {{ $lok->is_active ? 'true' : 'false' }})"
                                                title="Edit Lokasi Pemakaian">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" action="{{ route('costing.master.pemakaian.destroy', $lok) }}" class="d-inline"
                                                onsubmit="return confirm('Delete Lokasi Pemakaian {{ $lok->name }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Delete Lokasi">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-geo-alt display-6 d-block mb-2 text-muted"></i>
                                        No lokasi pemakaian found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($pemakaianList->hasPages())
                    <div class="card-footer bg-white border-top py-2 px-3">
                        {{ $pemakaianList->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @endif

            {{-- ======================================================== --}}
            {{-- TAB 4: BBN (SAMSAT VEHICLE REGISTRATION)                 --}}
            {{-- ======================================================== --}}
            @if ($tab === 'bbn')
                <div class="p-3 bg-light border-bottom">
                    <form method="GET" action="{{ route('costing.master.index') }}" class="row g-2 align-items-center">
                        <input type="hidden" name="tab" value="bbn">
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" name="search_bbn" class="form-control border-start-0"
                                    placeholder="Search Kode BBN or Samsat Name (e.g. JKT, SBY, Semarang)..." value="{{ request('search_bbn') }}">
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-secondary">Search</button>
                            @if (request('search_bbn'))
                                <a href="{{ route('costing.master.index', ['tab' => 'bbn']) }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 py-3" style="width: 60px;">#</th>
                                <th style="width: 140px;">Kode BBN</th>
                                <th>Wilayah / Samsat Plat Registration</th>
                                <th style="width: 140px;">Source</th>
                                <th style="width: 110px;">Status</th>
                                <th class="text-center pe-3" style="width: 110px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bbnList as $idx => $bbn)
                                <tr>
                                    <td class="ps-3 text-muted small">{{ $bbnList->firstItem() + $idx }}</td>
                                    <td>
                                        <span class="badge bg-dark-subtle text-dark border font-monospace px-2 py-1">
                                            {{ $bbn->kode }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6">{{ $bbn->name ?: $bbn->display_name }}</div>
                                        @if ($bbn->display_name && $bbn->display_name !== $bbn->name)
                                            <div class="small text-muted">{{ $bbn->display_name }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($bbn->odoo_bbn_id)
                                            <span class="badge bg-info-subtle text-info border border-info-subtle">
                                                <i class="bi bi-cloud-check me-1"></i>Odoo
                                            </span>
                                        @else
                                            <span class="badge border" style="background-color: #ede9fe; color: #6d28d9; border-color: #d8d4f8 !important;">
                                                <i class="bi bi-person me-1"></i>Local Custom
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($bbn->is_active)
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary"
                                                onclick="openEditBbnModal('{{ $bbn->id }}', '{{ addslashes($bbn->kode) }}', '{{ addslashes($bbn->name) }}', {{ $bbn->is_active ? 'true' : 'false' }})"
                                                title="Edit BBN">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" action="{{ route('costing.master.bbn.destroy', $bbn) }}" class="d-inline"
                                                onsubmit="return confirm('Delete BBN {{ $bbn->name }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Delete BBN">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-card-heading display-6 d-block mb-2 text-muted"></i>
                                        No BBN records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($bbnList->hasPages())
                    <div class="card-footer bg-white border-top py-2 px-3">
                        {{ $bbnList->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @endif

        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- MODALS: BRAND                                                --}}
    {{-- ============================================================ --}}
    {{-- Add Brand Modal --}}
    <div class="modal fade" id="addBrandModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('costing.master.brands.store') }}">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-1 text-primary"></i>Add New Brand</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Brand Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. TOYOTA, HONDA, MITSUBISHI..." required autocomplete="off" style="text-transform: uppercase;">
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary px-3">Save Brand</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Brand Modal --}}
    <div class="modal fade" id="editBrandModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="editBrandForm" action="">
                @csrf @method('PUT')
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h6 class="modal-title fw-bold"><i class="bi bi-pencil me-1 text-primary"></i>Edit Brand</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Brand Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editBrandName" class="form-control" required autocomplete="off" style="text-transform: uppercase;">
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editBrandActive" value="1">
                            <label class="form-check-label small fw-semibold" for="editBrandActive">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary px-3">Update Brand</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- MODALS: VEHICLE TYPES                                        --}}
    {{-- ============================================================ --}}
    {{-- Add Type Modal --}}
    <div class="modal fade" id="addTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('costing.master.vehicle-types.store') }}">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-1 text-success"></i>Add Vehicle Type</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Brand <span class="text-danger">*</span></label>
                            <select name="costing_brand_id" class="form-select" required>
                                <option value="">— Select Brand —</option>
                                @foreach ($allBrands as $br)
                                    <option value="{{ $br->id }}" {{ request('filter_brand') === $br->name ? 'selected' : '' }}>
                                        {{ $br->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Type / Model Kendaraan <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. ALL NEW AVANZA 1.5 G CVT..." required autocomplete="off">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold">Harga OTR (Rp) <span class="text-muted fw-normal">(Opsional)</span></label>
                                <input type="number" step="any" name="harga_otr" class="form-control" placeholder="0" min="0">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold">Diskon (Rp) <span class="text-muted fw-normal">(Opsional)</span></label>
                                <input type="number" step="any" name="diskon" class="form-control" placeholder="0" min="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Catatan / Keterangan <span class="text-muted fw-normal">(Opsional)</span></label>
                            <input type="text" name="notes" class="form-control" placeholder="e.g. Price list dealer terbaru...">
                        </div>
                        @if (Auth::user()->isManager() || Auth::user()->isAdmin())
                            <div class="alert alert-info py-2 small mb-0">
                                <i class="bi bi-shield-check me-1"></i>Sebagai Purchasing Manager / Admin, harga yang dimasukkan akan langsung disetujui & aktif di Costing.
                            </div>
                        @else
                            <div class="alert alert-warning py-2 small mb-0">
                                <i class="bi bi-clock-history me-1"></i>Jika harga diisi, status harga akan <strong>Menunggu Persetujuan Purchasing Manager</strong> sebelum aktif di Costing.
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-success px-3">Save Vehicle Type</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Type Modal --}}
    <div class="modal fade" id="editTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="editTypeForm" action="">
                @csrf @method('PUT')
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h6 class="modal-title fw-bold"><i class="bi bi-pencil me-1 text-success"></i>Edit Vehicle Type & Harga</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Brand <span class="text-danger">*</span></label>
                            <select name="costing_brand_id" id="editTypeBrandId" class="form-select" required>
                                <option value="">— Select Brand —</option>
                                @foreach ($allBrands as $br)
                                    <option value="{{ $br->id }}">{{ $br->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Type / Model Kendaraan <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editTypeName" class="form-control" required autocomplete="off">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold">Harga OTR (Rp)</label>
                                <input type="number" step="any" name="harga_otr" id="editTypeOtr" class="form-control" placeholder="0" min="0">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold">Diskon (Rp)</label>
                                <input type="number" step="any" name="diskon" id="editTypeDiskon" class="form-control" placeholder="0" min="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Alasan Perubahan / Catatan <span class="text-muted fw-normal">(Opsional)</span></label>
                            <input type="text" name="notes" id="editTypeNotes" class="form-control" placeholder="e.g. Update kenaikan OTR kuartal baru...">
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editTypeActive" value="1">
                            <label class="form-check-label small fw-semibold" for="editTypeActive">Active</label>
                        </div>
                        @if (Auth::user()->isManager() || Auth::user()->isAdmin())
                            <div class="alert alert-info py-2 small mb-0">
                                <i class="bi bi-shield-check me-1"></i>Sebagai Purchasing Manager / Admin, perubahan harga akan langsung aktif dan tercatat di riwayat.
                            </div>
                        @else
                            <div class="alert alert-warning py-2 small mb-0">
                                <i class="bi bi-clock-history me-1"></i>Perubahan harga OTR/Diskon akan berstatus <strong>Menunggu Persetujuan Purchasing Manager</strong> sebelum aktif di Costing.
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-success px-3">Update Type & Harga</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Double-Check Confirmation Modal for Manager/Admin Price Update --}}
    <div class="modal fade" id="confirmPriceUpdateModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom bg-warning-subtle text-dark">
                    <h6 class="modal-title fw-bold">
                        <i class="bi bi-shield-exclamation text-warning me-2 fs-5"></i>Konfirmasi Pembaruan Harga (Double-Check)
                    </h6>
                    <button type="button" class="btn-close" id="btnCancelPriceConfirmClose" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">
                        Apakah Anda yakin ingin memperbarui data dan harga master untuk tipe kendaraan berikut?
                    </p>

                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-secondary-subtle text-dark border" id="confirmPreviewBrand">Brand</span>
                            <strong class="text-dark" id="confirmPreviewName">Model Kendaraan</strong>
                        </div>

                        {{-- Standardized 2-line vertical diff table --}}
                        <div class="table-responsive rounded border bg-white mt-2">
                            <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th>HARGA OTR</th>
                                        <th>DISKON</th>
                                        <th>NETTO (BASELINE)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-nowrap" id="confirmPreviewOtr"></td>
                                        <td class="text-nowrap" id="confirmPreviewDiskon"></td>
                                        <td class="text-nowrap" id="confirmPreviewNetto"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-2 text-muted small" style="font-size: 11.5px;">
                            <i class="bi bi-chat-left-text me-1"></i>Catatan: <strong class="text-dark" id="confirmPreviewNotes">—</strong>
                        </div>
                    </div>

                    @if (Auth::user()->isManager() || Auth::user()->isAdmin())
                        <div class="alert alert-warning border-0 rounded-3 py-2 px-3 small mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-lightning-charge-fill text-warning fs-5 flex-shrink-0"></i>
                            <div style="font-size: 11.5px; line-height: 1.35;">
                                Sebagai <strong>Purchasing Manager / Admin</strong>, pembaruan harga ini akan <strong>langsung aktif</strong> di seluruh sistem Costing.
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-top bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" id="btnCancelPriceConfirm">
                        <i class="bi bi-arrow-left me-1"></i>Periksa Kembali
                    </button>
                    <button type="button" class="btn btn-sm btn-success px-3 shadow-sm fw-semibold" id="btnExecutePriceUpdate">
                        <i class="bi bi-check-circle me-1"></i>Ya, Simpan &amp; Terapkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Import Prices Modal --}}
    <div class="modal fade" id="importPricesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" id="importModalDialog">
            <form method="POST" id="importPricesForm" action="{{ route('costing.master.vehicle-types.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h6 class="modal-title fw-bold" id="importModalTitle">
                            <i class="bi bi-file-earmark-spreadsheet me-2 text-success fs-5"></i>Import Harga OTR &amp; Diskon dari Excel
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    {{-- ======================================================== --}}
                    {{-- STEP 1: UPLOAD FORM VIEW                                  --}}
                    {{-- ======================================================== --}}
                    <div id="importStepUpload">
                        <div class="modal-body p-4">
                            {{-- Step 1: Panduan & Download Template Card --}}
                            <div class="card border border-light-subtle bg-light rounded-3 p-3 mb-3 shadow-none">
                                <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                                    <div>
                                        <div class="fw-bold text-dark small mb-1">
                                            <i class="bi bi-info-circle text-primary me-1"></i>Format &amp; Template Excel
                                        </div>
                                        <div class="text-muted" style="font-size: 11.5px; line-height: 1.4;">
                                            Gunakan template resmi yang sudah memuat seluruh Tipe Kendaraan.<br>
                                            Isi kolom <strong>Harga OTR</strong> dan <strong>Diskon</strong> berupa angka murni (tanpa titik/koma/Rp).
                                        </div>
                                    </div>
                                    <a href="{{ route('costing.master.vehicle-types.template') }}" class="btn btn-sm btn-outline-success text-nowrap shadow-sm">
                                        <i class="bi bi-download me-1"></i>Download Template (.xlsx)
                                    </a>
                                </div>
                            </div>

                            {{-- Step 2: File Upload --}}
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-dark">
                                    Pilih File Excel (.xlsx / .xls) <span class="text-danger">*</span>
                                </label>
                                <input type="file" name="file" id="importFileInput" class="form-control" accept=".xlsx,.xls" required>
                                <div class="text-muted small mt-1" style="font-size: 11px;">
                                    <i class="bi bi-check2 text-success me-1"></i>Mendukung format file Microsoft Excel (.xlsx dan .xls)
                                </div>
                            </div>

                            {{-- Step 3: Optional Notes --}}
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-dark">
                                    Catatan / Keterangan Import <span class="text-muted fw-normal">(Opsional)</span>
                                </label>
                                <input type="text" name="notes" id="importNotesInput" class="form-control" placeholder="Contoh: Pembaruan Price List Nasional Q4 2026">
                            </div>

                            {{-- Step 4: Manager Auto-Approve Switch Card --}}
                            @if (Auth::user()->isManager() || Auth::user()->isAdmin())
                                <div class="card border border-secondary-subtle bg-light rounded-3 p-3 mb-0 shadow-none" id="autoApproveCard">
                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                        <div class="pe-2">
                                            <label class="form-check-label fw-bold text-dark small mb-1 d-block" for="importAutoApprove" style="cursor: pointer;">
                                                <i class="bi bi-lightning text-muted me-1" id="autoApproveIcon"></i>Langsung Setujui (Auto-approve immediately)
                                            </label>
                                            <div class="text-muted" id="autoApproveDesc" style="font-size: 11px; line-height: 1.35;">
                                                <strong>Non-aktif (Default):</strong> Harga baru dari Excel akan berstatus <em>Menunggu Persetujuan Purchasing Manager</em>.
                                            </div>
                                        </div>
                                        <div class="form-check form-switch m-0 p-0 d-flex align-items-center flex-shrink-0">
                                            <input class="form-check-input m-0" type="checkbox" name="auto_approve" id="importAutoApprove" value="1"
                                                style="cursor: pointer; width: 2.4em; height: 1.3em; float: none;">
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning border-0 rounded-3 py-2 px-3 small mb-0 d-flex align-items-center gap-2">
                                    <i class="bi bi-clock-history fs-5 text-warning flex-shrink-0"></i>
                                    <div style="font-size: 11.5px; line-height: 1.35;">
                                        Hasil import Anda akan berstatus <strong>Menunggu Persetujuan Purchasing Manager</strong> sebelum aktif di Costing.
                                    </div>
                                </div>
                            @endif

                            {{-- Upload Error Alert Container --}}
                            <div id="importUploadError" class="alert alert-danger border-0 rounded-3 mt-3 mb-0 py-2 px-3 small d-none">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i><span id="importUploadErrorMsg"></span>
                            </div>
                        </div>
                        <div class="modal-footer border-top bg-light d-flex justify-content-between">
                            <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Batal</button>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" id="btnPreviewExcel" class="btn btn-sm btn-primary px-3 shadow-sm fw-semibold">
                                    <i class="bi bi-eye me-1"></i>Lihat Perubahan (Preview)
                                </button>
                                <button type="submit" class="btn btn-sm btn-outline-primary px-2" title="Import langsung tanpa cek preview">
                                    <i class="bi bi-upload me-1"></i>Import Langsung
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- ======================================================== --}}
                    {{-- STEP 2: PREVIEW / VIEW CHANGES VIEW                       --}}
                    {{-- ======================================================== --}}
                    <div id="importStepPreview" style="display: none;">
                        <div class="modal-body p-3 p-md-4">
                            {{-- Stats Banner --}}
                            <div class="row g-2 mb-3" id="previewStatsContainer">
                                <div class="col-6 col-md-3">
                                    <div class="card border rounded-3 p-2 text-center bg-light shadow-none">
                                        <div class="text-muted small" style="font-size: 11px;">Total Baris Excel</div>
                                        <div class="fs-5 fw-bold text-dark font-monospace" id="previewStatTotal">0</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="card border border-warning-subtle bg-warning-subtle rounded-3 p-2 text-center shadow-none">
                                        <div class="text-dark small fw-semibold" style="font-size: 11px;"><i class="bi bi-pencil-square me-1"></i>Ada Perubahan</div>
                                        <div class="fs-5 fw-bold text-dark font-monospace" id="previewStatChanged">0</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="card border rounded-3 p-2 text-center bg-light shadow-none">
                                        <div class="text-muted small" style="font-size: 11px;">Harga Tetap (Sama)</div>
                                        <div class="fs-5 fw-bold text-secondary font-monospace" id="previewStatUnchanged">0</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="card border border-danger-subtle bg-danger-subtle rounded-3 p-2 text-center shadow-none">
                                        <div class="text-danger small" style="font-size: 11px;">Tidak Dikenal</div>
                                        <div class="fs-5 fw-bold text-danger font-monospace" id="previewStatNotFound">0</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Filter and Search Controls in Preview --}}
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-2 bg-light rounded-3 border mb-3">
                                <div class="form-check form-switch m-0 d-flex align-items-center">
                                    <input class="form-check-input me-2" type="checkbox" id="previewFilterOnlyChanged" checked style="cursor: pointer;">
                                    <label class="form-check-label small fw-semibold text-dark" for="previewFilterOnlyChanged" style="cursor: pointer;">
                                        Hanya tampilkan tipe yang mengalami perubahan harga
                                    </label>
                                </div>
                                <div style="min-width: 220px;">
                                    <input type="text" id="previewSearchInput" class="form-control form-control-sm" placeholder="Cari model / brand di preview...">
                                </div>
                            </div>

                            {{-- Preview Changes Table Container --}}
                            <div class="table-responsive rounded-3 border" style="max-height: 380px; overflow-y: auto;">
                                <table class="table table-sm table-hover align-middle mb-0" id="previewChangesTable" style="font-size: 0.85rem;">
                                    <thead class="table-light sticky-top" style="z-index: 1;">
                                        <tr>
                                            <th style="width: 45px;" class="text-center">#</th>
                                            <th style="width: 110px;">BRAND</th>
                                            <th>TYPE / MODEL KENDARAAN</th>
                                            <th style="min-width: 175px;">HARGA OTR</th>
                                            <th style="min-width: 165px;">DISKON</th>
                                            <th style="min-width: 175px;">NETTO (BASELINE)</th>
                                            <th style="width: 110px;" class="text-center">STATUS</th>
                                        </tr>
                                    </thead>
                                    <tbody id="previewChangesTbody">
                                        <!-- Rows injected via JavaScript -->
                                    </tbody>
                                </table>
                            </div>

                            {{-- Target Notes & Approval Reminder in Preview --}}
                            <div class="mt-3 p-2 bg-light-subtle rounded-3 border d-flex flex-wrap align-items-center justify-content-between gap-2 small">
                                <div>
                                    <i class="bi bi-file-earmark-text text-muted me-1"></i>
                                    Catatan: <strong id="previewNotesDisplay" class="text-dark">Tidak ada</strong>
                                </div>
                                <div id="previewApprovalBadgeContainer">
                                    <!-- Auto-approve status badge/label will show here -->
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top bg-light d-flex justify-content-between">
                            <button type="button" class="btn btn-sm btn-outline-secondary px-3" id="btnBackToUpload">
                                <i class="bi bi-arrow-left me-1"></i>Ganti File / Kembali
                            </button>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-sm btn-success px-3 shadow-sm fw-semibold" id="btnConfirmImport">
                                    <i class="bi bi-check-circle me-1"></i>Konfirmasi &amp; Import Excel Sekarang
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Reject Price Modal --}}
    <div class="modal fade" id="rejectPriceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="rejectPriceForm" action="">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom bg-danger-subtle text-danger">
                        <h6 class="modal-title fw-bold"><i class="bi bi-x-circle me-1"></i>Tolak Revisi Harga</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-3">
                            Anda akan menolak revisi harga untuk tipe kendaraan: <strong class="text-dark" id="rejectTypeName"></strong>. Harga aktif saat ini tidak akan berubah.
                        </p>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Alasan Penolakan <span class="text-muted fw-normal">(Opsional)</span></label>
                            <textarea name="notes" id="rejectNotes" class="form-control" rows="3" placeholder="e.g. Diskon terlalu kecil, konfirmasi ulang ke ATPM/dealer..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-danger px-3">Tolak Revisi Harga</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Price History Modal --}}
    <div class="modal fade" id="priceHistoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom">
                    <div>
                        <h6 class="modal-title fw-bold mb-0">
                            <i class="bi bi-clock-history me-1 text-primary"></i>Riwayat Perubahan Harga
                        </h6>
                        <div class="text-muted small" id="histTypeName">—</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3" id="histModalBody">
                    {{-- Dynamically loaded via AJAX --}}
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- MODALS: LOKASI PEMAKAIAN                                     --}}
    {{-- ============================================================ --}}
    {{-- Add Lokasi Pemakaian Modal --}}
    <div class="modal fade" id="addPemakaianModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('costing.master.pemakaian.store') }}">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-1 text-info"></i>Add Lokasi Pemakaian</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Kota / Kabupaten / Area Site <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Ungaran, Morowali, IKN, Cilegon..." required autocomplete="off">
                            <div class="text-muted small mt-1">Operational site or regency where vehicle will run.</div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Pulau <span class="text-danger">*</span></label>
                                <input type="text" name="pulau" list="pemakaianPulauDatalist" class="form-control" placeholder="Select or type Pulau..." required autocomplete="off">
                                <datalist id="pemakaianPulauDatalist">
                                    @foreach ($pulauList as $p)
                                        <option value="{{ $p }}">{{ $p }}</option>
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Provinsi <span class="text-danger">*</span></label>
                                <input type="text" name="provinsi" list="pemakaianProvinsiDatalist" class="form-control" placeholder="Select or type Provinsi..." required autocomplete="off">
                                <datalist id="pemakaianProvinsiDatalist">
                                    @foreach ($provinsiList as $pr)
                                        <option value="{{ $pr }}">{{ $pr }}</option>
                                    @endforeach
                                </datalist>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-info text-white px-3">Save Lokasi</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Lokasi Pemakaian Modal --}}
    <div class="modal fade" id="editPemakaianModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="editPemakaianForm" action="">
                @csrf @method('PUT')
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h6 class="modal-title fw-bold"><i class="bi bi-pencil me-1 text-info"></i>Edit Lokasi Pemakaian</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Kota / Kabupaten / Area Site <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editPemakaianName" class="form-control" required autocomplete="off">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Pulau <span class="text-danger">*</span></label>
                                <input type="text" name="pulau" id="editPemakaianPulau" list="pemakaianPulauDatalist" class="form-control" required autocomplete="off">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Provinsi <span class="text-danger">*</span></label>
                                <input type="text" name="provinsi" id="editPemakaianProvinsi" list="pemakaianProvinsiDatalist" class="form-control" required autocomplete="off">
                            </div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editPemakaianActive" value="1">
                            <label class="form-check-label small fw-semibold" for="editPemakaianActive">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-info text-white px-3">Update Lokasi</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- MODALS: BBN (SAMSAT VEHICLE REGISTRATION)                    --}}
    {{-- ============================================================ --}}
    {{-- Add BBN Modal --}}
    <div class="modal fade" id="addBbnModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('costing.master.bbn.store') }}">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-1 text-danger"></i>Add BBN (Samsat)</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Kode BBN <span class="text-danger">*</span></label>
                            <input type="text" name="kode" class="form-control font-monospace" placeholder="e.g. JKT, SBY, SMG..." required autocomplete="off" style="text-transform: uppercase;">
                            <div class="text-muted small mt-1">Short code used internally (e.g. JKT, SBY).</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Wilayah Samsat / Meaning <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Jakarta, Surabaya, Semarang..." required autocomplete="off">
                            <div class="text-muted small mt-1">Vehicle STNK registration / tax area name.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-danger px-3">Save BBN</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit BBN Modal --}}
    <div class="modal fade" id="editBbnModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="editBbnForm" action="">
                @csrf @method('PUT')
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h6 class="modal-title fw-bold"><i class="bi bi-pencil me-1 text-danger"></i>Edit BBN (Samsat)</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Kode BBN <span class="text-danger">*</span></label>
                            <input type="text" name="kode" id="editBbnCode" class="form-control font-monospace" required autocomplete="off" style="text-transform: uppercase;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Wilayah Samsat / Meaning <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editBbnName" class="form-control" required autocomplete="off">
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editBbnActive" value="1">
                            <label class="form-check-label small fw-semibold" for="editBbnActive">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-danger px-3">Update BBN</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // Brand Modal Helper
    function openEditBrandModal(id, name, isActive) {
        document.getElementById('editBrandForm').action = "{{ url('costing/master/brands') }}/" + id;
        document.getElementById('editBrandName').value = name;
        document.getElementById('editBrandActive').checked = isActive;
        new bootstrap.Modal(document.getElementById('editBrandModal')).show();
    }

    // Vehicle Type Modal Helper
    function openEditTypeModal(id, brandId, name, hargaOtr, diskon, isActive) {
        const form = document.getElementById('editTypeForm');
        form.action = "{{ url('costing/master/vehicle-types') }}/" + id;
        form.dataset.origOtr = hargaOtr || 0;
        form.dataset.origDiskon = diskon || 0;
        form.dataset.confirmed = 'false';
        document.getElementById('editTypeBrandId').value = brandId;
        document.getElementById('editTypeName').value = name;
        document.getElementById('editTypeOtr').value = (hargaOtr && Number(hargaOtr) > 0) ? hargaOtr : '';
        document.getElementById('editTypeDiskon').value = (diskon && Number(diskon) > 0) ? diskon : '';
        document.getElementById('editTypeNotes').value = '';
        document.getElementById('editTypeActive').checked = isActive;
        new bootstrap.Modal(document.getElementById('editTypeModal')).show();
    }

    // Reject Price Revision Helper
    function openRejectPriceModal(historyId, typeName) {
        document.getElementById('rejectPriceForm').action = "{{ url('costing/master/vehicle-types/price-approvals') }}/" + historyId + "/reject";
        document.getElementById('rejectTypeName').innerText = typeName;
        document.getElementById('rejectNotes').value = '';
        new bootstrap.Modal(document.getElementById('rejectPriceModal')).show();
    }

    // Price History Modal & AJAX Fetcher
    async function openPriceHistoryModal(typeId, typeName) {
        document.getElementById('histTypeName').innerText = typeName;
        const bodyEl = document.getElementById('histModalBody');
        bodyEl.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="text-muted small mt-2">Memuat riwayat perubahan harga...</div>
            </div>
        `;
        const modal = new bootstrap.Modal(document.getElementById('priceHistoryModal'));
        modal.show();

        try {
            const res = await fetch("{{ url('costing/master/vehicle-types') }}/" + typeId + "/price-history");
            if (!res.ok) throw new Error('Gagal memuat riwayat harga dari server.');
            const data = await res.json();
            renderPriceHistory(data);
        } catch (err) {
            bodyEl.innerHTML = `<div class="alert alert-danger m-3"><i class="bi bi-exclamation-triangle me-1"></i>${err.message}</div>`;
        }
    }

    function renderPriceHistory(data) {
        const bodyEl = document.getElementById('histModalBody');
        const vt = data.vehicle_type;
        const histories = data.histories || [];

        const currentOtr = Number(vt.harga_otr || 0);
        const currentDisc = Number(vt.diskon || 0);
        const currentNetto = Math.max(0, currentOtr - currentDisc);

        let html = `
            <div class="p-3 bg-light rounded border mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2 shadow-sm">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Harga Aktif Saat Ini:</span>
                    <div class="d-flex align-items-baseline gap-3 mt-1">
                        <div><span class="text-muted small">OTR:</span> <strong class="text-dark font-monospace text-nowrap">Rp ${currentOtr.toLocaleString('id-ID')}</strong></div>
                        <div><span class="text-muted small">Diskon:</span> <strong class="text-danger font-monospace text-nowrap">Rp ${currentDisc.toLocaleString('id-ID')}</strong></div>
                        <div><span class="text-muted small">Netto:</span> <strong class="text-success font-monospace text-nowrap">Rp ${currentNetto.toLocaleString('id-ID')}</strong></div>
                    </div>
                </div>
                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">${histories.length} Catatan Riwayat</span>
            </div>
        `;

        if (histories.length === 0) {
            html += `
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-clock-history display-6 d-block mb-2 text-muted"></i>
                    Belum ada riwayat perubahan harga untuk model ini.
                </div>
            `;
            bodyEl.innerHTML = html;
            return;
        }

        html += `
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 130px;">Waktu</th>
                            <th style="width: 90px;">Status</th>
                            <th>Harga OTR</th>
                            <th>Diskon</th>
                            <th>Netto</th>
                            <th style="width: 90px;">Sumber</th>
                            <th>Diajukan</th>
                            <th>Disetujui / Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        histories.forEach(h => {
            const oldOtr = Number(h.old_harga_otr || 0);
            const newOtr = Number(h.new_harga_otr || 0);
            const oldDisc = Number(h.old_diskon || 0);
            const newDisc = Number(h.new_diskon || 0);
            const oldNet = Math.max(0, oldOtr - oldDisc);
            const newNet = Math.max(0, newOtr - newDisc);

            let statusBadge = '';
            if (h.status === 'approved') {
                statusBadge = '<span class="badge bg-success-subtle text-success">Approved</span>';
            } else if (h.status === 'pending') {
                statusBadge = '<span class="badge bg-warning-subtle text-dark border border-warning">Pending</span>';
            } else if (h.status === 'rejected') {
                statusBadge = '<span class="badge bg-danger-subtle text-danger">Rejected</span>';
            }

            let sourceBadge = '';
            if (h.source === 'excel_import') {
                sourceBadge = '<span class="badge bg-info-subtle text-info"><i class="bi bi-file-earmark-excel me-1"></i>Excel</span>';
            } else if (h.source === 'initial_create') {
                sourceBadge = '<span class="badge bg-secondary-subtle text-secondary">Initial</span>';
            } else {
                sourceBadge = '<span class="badge bg-light text-dark border">Manual</span>';
            }

            const dt = new Date(h.created_at);
            const dateStr = dt.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

            const reqUser = h.requester ? h.requester.name : '—';
            let appInfo = '—';
            if (h.approver) {
                appInfo = `<span class="fw-semibold text-dark">${h.approver.name}</span>`;
                if (h.approved_at) {
                    const aDt = new Date(h.approved_at);
                    appInfo += `<div class="text-muted" style="font-size: 10px;">${aDt.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })}</div>`;
                }
            }
            if (h.notes) {
                appInfo += `<div class="text-muted fst-italic mt-1" style="font-size: 11px;">"${h.notes}"</div>`;
            }

            const otrChanged = Math.abs(newOtr - oldOtr) >= 0.01;
            const discChanged = Math.abs(newDisc - oldDisc) >= 0.01;
            const netChanged = Math.abs(newNet - oldNet) >= 0.01;

            const otrSub = otrChanged
                ? `<div class="text-muted text-nowrap" style="font-size: 11px;">Sebelumnya: <span class="text-decoration-line-through">Rp ${oldOtr.toLocaleString('id-ID')}</span></div>`
                : `<div class="text-muted text-nowrap" style="font-size: 11px;"><span class="badge bg-light text-muted border py-0 px-1" style="font-size: 10px; font-weight: normal;">Tidak berubah</span></div>`;

            const discSub = discChanged
                ? `<div class="text-muted text-nowrap" style="font-size: 11px;">Sebelumnya: <span class="text-decoration-line-through">Rp ${oldDisc.toLocaleString('id-ID')}</span></div>`
                : `<div class="text-muted text-nowrap" style="font-size: 11px;"><span class="badge bg-light text-muted border py-0 px-1" style="font-size: 10px; font-weight: normal;">Tidak berubah</span></div>`;

            const netSub = netChanged
                ? `<div class="text-muted text-nowrap" style="font-size: 11px;">Sebelumnya: <span class="text-decoration-line-through">Rp ${oldNet.toLocaleString('id-ID')}</span></div>`
                : `<div class="text-muted text-nowrap" style="font-size: 11px;"><span class="badge bg-light text-muted border py-0 px-1" style="font-size: 10px; font-weight: normal;">Tidak berubah</span></div>`;

            html += `
                <tr>
                    <td class="text-muted small text-nowrap">${dateStr}</td>
                    <td>${statusBadge}</td>
                    <td class="text-nowrap">
                        <div class="fw-bold text-dark font-monospace text-nowrap">Rp ${newOtr.toLocaleString('id-ID')}</div>
                        ${otrSub}
                    </td>
                    <td class="text-nowrap">
                        <div class="fw-bold text-danger font-monospace text-nowrap">Rp ${newDisc.toLocaleString('id-ID')}</div>
                        ${discSub}
                    </td>
                    <td class="text-nowrap">
                        <div class="fw-bold text-success font-monospace text-nowrap">Rp ${newNet.toLocaleString('id-ID')}</div>
                        ${netSub}
                    </td>
                    <td>${sourceBadge}</td>
                    <td>${reqUser}</td>
                    <td>${appInfo}</td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;
        bodyEl.innerHTML = html;
    }

    // Lokasi Pemakaian Modal Helper
    function openEditPemakaianModal(id, name, pulau, provinsi, isActive) {
        document.getElementById('editPemakaianForm').action = "{{ url('costing/master/pemakaian') }}/" + id;
        document.getElementById('editPemakaianName').value = name;
        document.getElementById('editPemakaianPulau').value = pulau || '';
        document.getElementById('editPemakaianProvinsi').value = provinsi || '';
        document.getElementById('editPemakaianActive').checked = isActive;
        new bootstrap.Modal(document.getElementById('editPemakaianModal')).show();
    }

    // BBN Modal Helper
    function openEditBbnModal(id, code, name, isActive) {
        document.getElementById('editBbnForm').action = "{{ url('costing/master/bbn') }}/" + id;
        document.getElementById('editBbnCode').value = code;
        document.getElementById('editBbnName').value = name;
        document.getElementById('editBbnActive').checked = isActive;
        new bootstrap.Modal(document.getElementById('editBbnModal')).show();
    }

    // Auto-approve switch visual feedback in Import Modal
    document.addEventListener('DOMContentLoaded', function () {
        const autoApproveEl = document.getElementById('importAutoApprove');
        if (autoApproveEl) {
            autoApproveEl.addEventListener('change', function () {
                const card = document.getElementById('autoApproveCard');
                const icon = document.getElementById('autoApproveIcon');
                const desc = document.getElementById('autoApproveDesc');
                if (this.checked) {
                    if (card) card.className = 'card border border-warning-subtle bg-warning-subtle rounded-3 p-3 mb-0 shadow-none';
                    if (icon) icon.className = 'bi bi-lightning-charge-fill text-warning me-1';
                    if (desc) desc.innerHTML = '<strong>Aktif:</strong> Harga baru dari Excel akan langsung disetujui tanpa perlu antre approval.';
                } else {
                    if (card) card.className = 'card border border-secondary-subtle bg-light rounded-3 p-3 mb-0 shadow-none';
                    if (icon) icon.className = 'bi bi-lightning text-muted me-1';
                    if (desc) desc.innerHTML = '<strong>Non-aktif (Default):</strong> Harga baru dari Excel akan berstatus <em>Menunggu Persetujuan Purchasing Manager</em>.';
                }
            });
        }

        // ── Excel Import Preview Logic ──────────────────────────────────────────
        let previewDataCache = null;
        const btnPreviewExcel = document.getElementById('btnPreviewExcel');
        const importFileInput = document.getElementById('importFileInput');
        const importModalDialog = document.getElementById('importModalDialog');
        const importStepUpload = document.getElementById('importStepUpload');
        const importStepPreview = document.getElementById('importStepPreview');
        const btnBackToUpload = document.getElementById('btnBackToUpload');
        const previewFilterOnlyChanged = document.getElementById('previewFilterOnlyChanged');
        const previewSearchInput = document.getElementById('previewSearchInput');

        if (btnPreviewExcel) {
            btnPreviewExcel.addEventListener('click', async function () {
                if (!importFileInput || !importFileInput.files || importFileInput.files.length === 0) {
                    if (importFileInput) importFileInput.reportValidity();
                    return;
                }

                const file = importFileInput.files[0];
                const formData = new FormData();
                formData.append('file', file);

                const origBtnHtml = btnPreviewExcel.innerHTML;
                btnPreviewExcel.disabled = true;
                btnPreviewExcel.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Menganalisis File Excel...';

                const errDiv = document.getElementById('importUploadError');
                const errMsg = document.getElementById('importUploadErrorMsg');
                if (errDiv) errDiv.classList.add('d-none');

                try {
                    const resp = await fetch("{{ route('costing.master.vehicle-types.preview-prices') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await resp.json();

                    if (!resp.ok || !data.success) {
                        throw new Error(data.message || 'Gagal membaca atau memproses file Excel.');
                    }

                    previewDataCache = data.preview;
                    renderPreviewData();

                    // Transition modal to Step 2 (Preview View)
                    importModalDialog.classList.remove('modal-dialog-centered');
                    importModalDialog.classList.add('modal-xl', 'modal-dialog-scrollable');
                    importStepUpload.style.display = 'none';
                    importStepPreview.style.display = 'block';
                    document.getElementById('importModalTitle').innerHTML = '<i class="bi bi-eye text-primary me-2 fs-5"></i>Preview Perubahan Harga Excel';

                    // Update Notes display
                    const notesVal = document.getElementById('importNotesInput') ? document.getElementById('importNotesInput').value.trim() : '';
                    const notesDisp = document.getElementById('previewNotesDisplay');
                    if (notesDisp) notesDisp.textContent = notesVal ? `"${notesVal}"` : '— (Kosong)';

                    // Update Approval Mode Badge in Preview
                    const autoApproveEl = document.getElementById('importAutoApprove');
                    const badgeContainer = document.getElementById('previewApprovalBadgeContainer');
                    if (badgeContainer) {
                        if (autoApproveEl && autoApproveEl.checked) {
                            badgeContainer.innerHTML = '<span class="badge bg-warning text-dark border border-warning"><i class="bi bi-lightning-charge-fill me-1"></i>Mode: Auto-Approve Langsung Aktif</span>';
                        } else {
                            badgeContainer.innerHTML = '<span class="badge bg-secondary-subtle text-secondary border"><i class="bi bi-clock-history me-1"></i>Mode: Antrean Persetujuan Manager</span>';
                        }
                    }
                } catch (err) {
                    if (errDiv && errMsg) {
                        errMsg.textContent = err.message || 'Terjadi kesalahan saat memproses preview Excel.';
                        errDiv.classList.remove('d-none');
                    } else {
                        alert(err.message || 'Terjadi kesalahan saat memproses preview Excel.');
                    }
                } finally {
                    btnPreviewExcel.disabled = false;
                    btnPreviewExcel.innerHTML = origBtnHtml;
                }
            });
        }

        function renderPreviewData() {
            if (!previewDataCache) return;

            // Update stats counters
            const elTotal = document.getElementById('previewStatTotal');
            const elChanged = document.getElementById('previewStatChanged');
            const elUnchanged = document.getElementById('previewStatUnchanged');
            const elNotFound = document.getElementById('previewStatNotFound');

            if (elTotal) elTotal.textContent = Number(previewDataCache.total_rows || 0).toLocaleString('id-ID');
            if (elChanged) elChanged.textContent = Number(previewDataCache.changed_count || 0).toLocaleString('id-ID');
            if (elUnchanged) elUnchanged.textContent = Number(previewDataCache.unchanged_count || 0).toLocaleString('id-ID');
            if (elNotFound) elNotFound.textContent = Number(previewDataCache.not_found_count || 0).toLocaleString('id-ID');

            const tbody = document.getElementById('previewChangesTbody');
            if (!tbody) return;

            const onlyChanged = previewFilterOnlyChanged ? previewFilterOnlyChanged.checked : true;
            const query = previewSearchInput ? previewSearchInput.value.toLowerCase().trim() : '';
            const items = previewDataCache.items || [];

            let rowsHtml = '';
            let displayIdx = 1;

            items.forEach(item => {
                if (onlyChanged && item.status === 'unchanged') {
                    return;
                }
                if (query) {
                    const matchName = (item.name || '').toLowerCase().includes(query);
                    const matchBrand = (item.brand_name || '').toLowerCase().includes(query);
                    if (!matchName && !matchBrand) {
                        return;
                    }
                }

                const isChanged = item.status === 'changed';
                const isNotFound = item.status === 'not_found';

                let rowClass = '';
                if (isChanged) rowClass = 'table-warning bg-opacity-25';
                else if (isNotFound) rowClass = 'table-danger bg-opacity-25';

                // Standardized 2-line vertical placement (Matching History Modal):
                // Top: Harga Baru (bold)
                // Bottom: Harga Sebelumnya (strikethrough) OR [Tidak berubah]

                // 1. OTR
                let otrSub = '';
                if (isNotFound) {
                    otrSub = `<div class="text-muted text-nowrap" style="font-size: 11px;"><span class="badge bg-danger-subtle text-danger border border-danger py-0 px-1" style="font-size: 10px; font-weight: normal;">Model baru</span></div>`;
                } else if (item.otr_changed) {
                    otrSub = `<div class="text-muted text-nowrap" style="font-size: 11px;">Sebelumnya: <span class="text-decoration-line-through">Rp ${Number(item.old_otr || 0).toLocaleString('id-ID')}</span></div>`;
                } else {
                    otrSub = `<div class="text-muted text-nowrap" style="font-size: 11px;"><span class="badge bg-light text-muted border py-0 px-1" style="font-size: 10px; font-weight: normal;">Tidak berubah</span></div>`;
                }
                const otrHtml = `
                    <div class="fw-bold text-dark font-monospace text-nowrap">Rp ${Number(item.new_otr || 0).toLocaleString('id-ID')}</div>
                    ${otrSub}
                `;

                // 2. Diskon
                let diskonSub = '';
                if (isNotFound) {
                    diskonSub = `<div class="text-muted text-nowrap" style="font-size: 11px;"><span class="badge bg-danger-subtle text-danger border border-danger py-0 px-1" style="font-size: 10px; font-weight: normal;">Model baru</span></div>`;
                } else if (item.diskon_changed) {
                    diskonSub = `<div class="text-muted text-nowrap" style="font-size: 11px;">Sebelumnya: <span class="text-decoration-line-through">Rp ${Number(item.old_diskon || 0).toLocaleString('id-ID')}</span></div>`;
                } else {
                    diskonSub = `<div class="text-muted text-nowrap" style="font-size: 11px;"><span class="badge bg-light text-muted border py-0 px-1" style="font-size: 10px; font-weight: normal;">Tidak berubah</span></div>`;
                }
                const diskonHtml = `
                    <div class="fw-bold text-danger font-monospace text-nowrap">Rp ${Number(item.new_diskon || 0).toLocaleString('id-ID')}</div>
                    ${diskonSub}
                `;

                // 3. Netto
                const oldNet = Number(item.old_netto || 0);
                const newNet = Number(item.new_netto || 0);
                const netChanged = isChanged && (oldNet !== newNet);

                let nettoSub = '';
                if (isNotFound) {
                    nettoSub = `<div class="text-muted text-nowrap" style="font-size: 11px;"><span class="badge bg-danger-subtle text-danger border border-danger py-0 px-1" style="font-size: 10px; font-weight: normal;">Model baru</span></div>`;
                } else if (netChanged) {
                    nettoSub = `<div class="text-muted text-nowrap" style="font-size: 11px;">Sebelumnya: <span class="text-decoration-line-through">Rp ${oldNet.toLocaleString('id-ID')}</span></div>`;
                } else {
                    nettoSub = `<div class="text-muted text-nowrap" style="font-size: 11px;"><span class="badge bg-light text-muted border py-0 px-1" style="font-size: 10px; font-weight: normal;">Tidak berubah</span></div>`;
                }
                const nettoHtml = `
                    <div class="fw-bold text-success font-monospace text-nowrap">Rp ${newNet.toLocaleString('id-ID')}</div>
                    ${nettoSub}
                `;

                // 4. Status Badge
                let statusBadge = '';
                if (isChanged) {
                    statusBadge = '<span class="badge bg-warning text-dark border border-warning"><i class="bi bi-pencil-fill me-1"></i>Berubah</span>';
                } else if (isNotFound) {
                    statusBadge = '<span class="badge bg-danger text-white"><i class="bi bi-question-circle me-1"></i>Model Baru</span>';
                } else {
                    statusBadge = '<span class="badge bg-light text-secondary border">Tetap</span>';
                }

                rowsHtml += `
                    <tr class="${rowClass}">
                        <td class="text-center text-muted small">${displayIdx++}</td>
                        <td><span class="badge bg-light text-dark border">${item.brand_name || '—'}</span></td>
                        <td class="fw-semibold text-dark">${item.name}</td>
                        <td class="text-nowrap">${otrHtml}</td>
                        <td class="text-nowrap">${diskonHtml}</td>
                        <td class="text-nowrap">${nettoHtml}</td>
                        <td class="text-center">${statusBadge}</td>
                    </tr>
                `;
            });

            if (!rowsHtml) {
                rowsHtml = `
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-info-circle me-1 fs-5 d-block mb-1"></i>
                            Tidak ada baris yang sesuai dengan filter (Semua harga sama / tidak ada perubahan harga).
                        </td>
                    </tr>
                `;
            }

            tbody.innerHTML = rowsHtml;
        }

        if (previewFilterOnlyChanged) {
            previewFilterOnlyChanged.addEventListener('change', renderPreviewData);
        }
        if (previewSearchInput) {
            previewSearchInput.addEventListener('input', renderPreviewData);
        }

        if (btnBackToUpload) {
            btnBackToUpload.addEventListener('click', function () {
                if (importStepPreview) importStepPreview.style.display = 'none';
                if (importStepUpload) importStepUpload.style.display = 'block';
                if (importModalDialog) {
                    importModalDialog.classList.remove('modal-xl', 'modal-dialog-scrollable');
                    importModalDialog.classList.add('modal-dialog-centered');
                }
                const titleEl = document.getElementById('importModalTitle');
                if (titleEl) {
                    titleEl.innerHTML = '<i class="bi bi-file-earmark-spreadsheet me-2 text-success fs-5"></i>Import Harga OTR &amp; Diskon dari Excel';
                }
            });
        }

        const importPricesModalEl = document.getElementById('importPricesModal');
        if (importPricesModalEl) {
            importPricesModalEl.addEventListener('hidden.bs.modal', function () {
                if (importStepPreview) importStepPreview.style.display = 'none';
                if (importStepUpload) importStepUpload.style.display = 'block';
                if (importModalDialog) {
                    importModalDialog.classList.remove('modal-xl', 'modal-dialog-scrollable');
                    importModalDialog.classList.add('modal-dialog-centered');
                }
                const titleEl = document.getElementById('importModalTitle');
                if (titleEl) {
                    titleEl.innerHTML = '<i class="bi bi-file-earmark-spreadsheet me-2 text-success fs-5"></i>Import Harga OTR &amp; Diskon dari Excel';
                }
                const errDiv = document.getElementById('importUploadError');
                if (errDiv) errDiv.classList.add('d-none');
            });
        }

        // ── Double-Check Confirmation for Manager/Admin Manual Price Update ──
        const editTypeForm = document.getElementById('editTypeForm');
        const confirmPriceUpdateModal = document.getElementById('confirmPriceUpdateModal');
        const btnCancelPriceConfirm = document.getElementById('btnCancelPriceConfirm');
        const btnCancelPriceConfirmClose = document.getElementById('btnCancelPriceConfirmClose');
        const btnExecutePriceUpdate = document.getElementById('btnExecutePriceUpdate');

        if (editTypeForm) {
            editTypeForm.addEventListener('submit', function (e) {
                if (this.dataset.confirmed === 'true') {
                    return true;
                }

                const isManagerOrAdmin = {{ (Auth::user()->isManager() || Auth::user()->isAdmin()) ? 'true' : 'false' }};
                const origOtr = Number(this.dataset.origOtr || 0);
                const origDiskon = Number(this.dataset.origDiskon || 0);
                const newOtr = Number(document.getElementById('editTypeOtr').value || 0);
                const newDiskon = Number(document.getElementById('editTypeDiskon').value || 0);

                const otrChanged = Math.abs(newOtr - origOtr) >= 0.01;
                const diskonChanged = Math.abs(newDiskon - origDiskon) >= 0.01;

                // Show double-check preview popup if user is Manager/Admin and price was modified
                if (isManagerOrAdmin && (otrChanged || diskonChanged)) {
                    e.preventDefault();

                    const brandSelect = document.getElementById('editTypeBrandId');
                    const brandName = brandSelect.options[brandSelect.selectedIndex] ? brandSelect.options[brandSelect.selectedIndex].text : '—';
                    const typeName = document.getElementById('editTypeName').value;
                    const notesVal = document.getElementById('editTypeNotes').value.trim();

                    const oldNet = Math.max(0, origOtr - origDiskon);
                    const newNet = Math.max(0, newOtr - newDiskon);
                    const netChanged = Math.abs(newNet - oldNet) >= 0.01;

                    // Fill preview
                    document.getElementById('confirmPreviewBrand').textContent = brandName;
                    document.getElementById('confirmPreviewName').textContent = typeName;
                    document.getElementById('confirmPreviewNotes').textContent = notesVal ? `"${notesVal}"` : '— (Kosong)';

                    // OTR cell
                    const otrSub = otrChanged
                        ? `<div class="text-muted text-nowrap" style="font-size: 11px;">Sebelumnya: <span class="text-decoration-line-through">Rp ${origOtr.toLocaleString('id-ID')}</span></div>`
                        : `<div class="text-muted text-nowrap" style="font-size: 11px;"><span class="badge bg-light text-muted border py-0 px-1" style="font-size: 10px; font-weight: normal;">Tidak berubah</span></div>`;
                    document.getElementById('confirmPreviewOtr').innerHTML = `
                        <div class="fw-bold text-dark font-monospace text-nowrap">Rp ${newOtr.toLocaleString('id-ID')}</div>
                        ${otrSub}
                    `;

                    // Diskon cell
                    const discSub = diskonChanged
                        ? `<div class="text-muted text-nowrap" style="font-size: 11px;">Sebelumnya: <span class="text-decoration-line-through">Rp ${origDiskon.toLocaleString('id-ID')}</span></div>`
                        : `<div class="text-muted text-nowrap" style="font-size: 11px;"><span class="badge bg-light text-muted border py-0 px-1" style="font-size: 10px; font-weight: normal;">Tidak berubah</span></div>`;
                    document.getElementById('confirmPreviewDiskon').innerHTML = `
                        <div class="fw-bold text-danger font-monospace text-nowrap">Rp ${newDiskon.toLocaleString('id-ID')}</div>
                        ${discSub}
                    `;

                    // Netto cell
                    const netSub = netChanged
                        ? `<div class="text-muted text-nowrap" style="font-size: 11px;">Sebelumnya: <span class="text-decoration-line-through">Rp ${oldNet.toLocaleString('id-ID')}</span></div>`
                        : `<div class="text-muted text-nowrap" style="font-size: 11px;"><span class="badge bg-light text-muted border py-0 px-1" style="font-size: 10px; font-weight: normal;">Tidak berubah</span></div>`;
                    document.getElementById('confirmPreviewNetto').innerHTML = `
                        <div class="fw-bold text-success font-monospace text-nowrap">Rp ${newNet.toLocaleString('id-ID')}</div>
                        ${netSub}
                    `;

                    // Hide edit modal and show confirm modal
                    const editModalEl = document.getElementById('editTypeModal');
                    const editModalInst = bootstrap.Modal.getInstance(editModalEl);
                    if (editModalInst) editModalInst.hide();

                    const confirmModal = new bootstrap.Modal(document.getElementById('confirmPriceUpdateModal'));
                    confirmModal.show();
                }
            });
        }

        function returnToEditModal() {
            const confirmModalEl = document.getElementById('confirmPriceUpdateModal');
            const confirmModalInst = bootstrap.Modal.getInstance(confirmModalEl);
            if (confirmModalInst) confirmModalInst.hide();

            const editModal = new bootstrap.Modal(document.getElementById('editTypeModal'));
            editModal.show();
        }

        if (btnCancelPriceConfirm) {
            btnCancelPriceConfirm.addEventListener('click', returnToEditModal);
        }
        if (btnCancelPriceConfirmClose) {
            btnCancelPriceConfirmClose.addEventListener('click', returnToEditModal);
        }

        if (btnExecutePriceUpdate) {
            btnExecutePriceUpdate.addEventListener('click', function () {
                if (editTypeForm) {
                    editTypeForm.dataset.confirmed = 'true';
                    editTypeForm.submit();
                }
            });
        }
    });
</script>
@endpush
