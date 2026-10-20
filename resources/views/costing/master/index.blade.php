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
            <p class="text-muted small mb-0 mt-1">Manage Brand, Type Kendaraan, and BBN (Wilayah) strictly for Costing Portal.</p>
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
                <button type="button" class="btn btn-success btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addTypeModal">
                    <i class="bi bi-plus-lg me-1"></i>Add Vehicle Type
                </button>
            @elseif ($tab === 'bbn')
                <button type="button" class="btn btn-danger btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addBbnModal">
                    <i class="bi bi-plus-lg me-1"></i>Add BBN Wilayah
                </button>
            @elseif ($tab === 'regions')
                <button type="button" class="btn btn-info text-white btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addRegionModal">
                    <i class="bi bi-plus-lg me-1"></i>Add Wilayah
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
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 px-4 fw-semibold {{ $tab === 'bbn' ? 'active text-danger border-bottom border-danger border-2' : 'text-muted' }}"
                        href="{{ route('costing.master.index', ['tab' => 'bbn']) }}">
                        <i class="bi bi-geo-alt me-2"></i>BBN (Wilayah)
                        <span class="badge bg-danger-subtle text-danger rounded-pill ms-1">{{ $bbnList->total() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 px-4 fw-semibold {{ $tab === 'regions' ? 'active text-info border-bottom border-info border-2' : 'text-muted' }}"
                        href="{{ route('costing.master.index', ['tab' => 'regions']) }}">
                        <i class="bi bi-map me-2"></i>Data Wilayah (Pulau & Provinsi)
                        <span class="badge bg-info-subtle text-info rounded-pill ms-1">{{ $regionsList->total() }}</span>
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
                                <th class="ps-3 py-3" style="width: 70px;">#</th>
                                <th>Brand Name</th>
                                <th>Vehicle Types Count</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th class="text-center pe-3" style="width: 140px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($brands as $idx => $b)
                                <tr>
                                    <td class="ps-3 text-muted small">{{ $brands->firstItem() + $idx }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $b->name }}</div>
                                    </td>
                                    <td>
                                        <a href="{{ route('costing.master.index', ['tab' => 'types', 'filter_brand' => $b->name]) }}"
                                            class="text-decoration-none badge bg-light text-dark border">
                                            <i class="bi bi-car-front me-1"></i>{{ $b->vehicle_types_count }} model(s)
                                        </a>
                                    </td>
                                    <td>
                                        @if ($b->odoo_brand_id)
                                            <span class="badge bg-info-subtle text-info border border-info-subtle">
                                                <i class="bi bi-cloud-check me-1"></i>Odoo
                                            </span>
                                        @else
                                            <span class="badge bg-purple-subtle text-primary border border-primary-subtle" style="background-color: #ede9fe; color: #6d28d9;">
                                                <i class="bi bi-person me-1"></i>Local Custom
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($b->is_active)
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary"
                                                onclick="openEditBrandModal('{{ $b->id }}', '{{ addslashes($b->name) }}', {{ $b->is_active ? 'true' : 'false' }})"
                                                title="Edit Brand">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" action="{{ route('costing.master.brands.destroy', $b) }}" class="d-inline"
                                                onsubmit="return confirm('Delete Brand {{ $b->name }} and all its linked vehicle types?')">
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
                                        No brands found.
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
                <div class="p-3 bg-light border-bottom">
                    <form method="GET" action="{{ route('costing.master.index') }}" class="row g-2 align-items-center">
                        <input type="hidden" name="tab" value="types">
                        <div class="col-md-3">
                            <select name="filter_brand" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">— All Brands —</option>
                                @foreach ($allBrands as $br)
                                    <option value="{{ $br->name }}" {{ request('filter_brand') === $br->name ? 'selected' : '' }}>
                                        {{ $br->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" name="search_type" class="form-control border-start-0"
                                    placeholder="Search Model / Type Name..." value="{{ request('search_type') }}">
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
                            @if (request('filter_brand') || request('search_type'))
                                <a href="{{ route('costing.master.index', ['tab' => 'types']) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 py-3" style="width: 70px;">#</th>
                                <th style="width: 180px;">Brand</th>
                                <th>Type / Model Kendaraan</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th class="text-center pe-3" style="width: 140px;">Actions</th>
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
                                        <div class="fw-semibold text-dark">{{ $vt->name }}</div>
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
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary"
                                                onclick="openEditTypeModal('{{ $vt->id }}', '{{ $vt->costing_brand_id }}', '{{ addslashes($vt->name) }}', {{ $vt->is_active ? 'true' : 'false' }})"
                                                title="Edit Type">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" action="{{ route('costing.master.vehicle-types.destroy', $vt) }}" class="d-inline"
                                                onsubmit="return confirm('Delete vehicle type {{ $vt->name }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Delete Type">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-car-front display-6 d-block mb-2 text-muted"></i>
                                        No vehicle types found.
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
            {{-- TAB 3: BBN (WILAYAH)                                     --}}
            {{-- ======================================================== --}}
            @if ($tab === 'bbn')
                <div class="p-3 bg-light border-bottom">
                    <form method="GET" action="{{ route('costing.master.index') }}" class="row g-2 align-items-center">
                        <input type="hidden" name="tab" value="bbn">
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" name="search_bbn" class="form-control border-start-0"
                                    placeholder="Search Kode BBN or Wilayah Name (e.g. Jakarta, Malili, Surabaya)..." value="{{ request('search_bbn') }}">
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
                                <th class="ps-3 py-3" style="width: 70px;">#</th>
                                <th style="width: 140px;">Kode BBN</th>
                                <th>Wilayah / Meaning (Tanpa Singkatan)</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th class="text-center pe-3" style="width: 140px;">Actions</th>
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
                                                onsubmit="return confirm('Delete BBN {{ $bbn->name }} ({{ $bbn->kode }})?')">
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
                                        <i class="bi bi-geo-alt display-6 d-block mb-2 text-muted"></i>
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

            {{-- ======================================================== --}}
            {{-- TAB 4: REGIONS (DATA WILAYAH: PULAU & PROVINSI)          --}}
            {{-- ======================================================== --}}
            @if ($tab === 'regions')
                <div class="p-3 bg-light border-bottom">
                    <form method="GET" action="{{ route('costing.master.index') }}" class="row g-2 align-items-center">
                        <input type="hidden" name="tab" value="regions">
                        <div class="col-md-3">
                            <select name="filter_pulau" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Pulau</option>
                                @foreach ($pulauList as $p)
                                    <option value="{{ $p }}" {{ request('filter_pulau') === $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select name="filter_provinsi" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Provinsi</option>
                                @foreach ($provinsiList as $pr)
                                    <option value="{{ $pr }}" {{ request('filter_provinsi') === $pr ? 'selected' : '' }}>{{ $pr }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" name="search_area" class="form-control border-start-0"
                                    placeholder="Search Area / Kota..." value="{{ request('search_area') }}">
                            </div>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-dark w-100">Filter</button>
                            @if (request()->hasAny(['filter_pulau', 'filter_provinsi', 'search_area']))
                                <a href="{{ route('costing.master.index', ['tab' => 'regions']) }}" class="btn btn-sm btn-outline-secondary" title="Clear Filters">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th style="width: 220px;">Pulau</th>
                                <th style="width: 260px;">Provinsi</th>
                                <th>Area / Kota</th>
                                <th style="width: 120px;" class="text-center">Status</th>
                                <th style="width: 130px;" class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($regionsList as $idx => $r)
                                <tr>
                                    <td class="text-muted small">{{ $regionsList->firstItem() + $idx }}</td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                            <i class="bi bi-geo-alt me-1"></i>{{ $r->pulau }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">{{ $r->provinsi }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $r->area }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if ($r->is_active)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary"
                                                onclick="openEditRegionModal({{ $r->id }}, '{{ addslashes($r->pulau) }}', '{{ addslashes($r->provinsi) }}', '{{ addslashes($r->area) }}', {{ $r->is_active ? 'true' : 'false' }})"
                                                title="Edit Wilayah">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" action="{{ route('costing.master.regions.destroy', $r) }}" class="d-inline"
                                                onsubmit="return confirm('Delete wilayah {{ $r->area }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Delete Wilayah">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-map display-6 d-block mb-2 text-muted"></i>
                                        No Wilayah records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($regionsList->hasPages())
                    <div class="card-footer bg-white border-top py-2 px-3">
                        {{ $regionsList->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @endif

        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- MODALS: BRANDS                                               --}}
    {{-- ============================================================ --}}
    {{-- Add Brand Modal --}}
    <div class="modal fade" id="addBrandModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('costing.master.brands.store') }}">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-1 text-primary"></i>Add Brand Kendaraan</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Brand Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. TOYOTA, HONDA, CHERY, BYD..." required autocomplete="off">
                            <div class="text-muted small mt-1">Brand name will be formatted in uppercase automatically.</div>
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
                        <h6 class="modal-title fw-bold"><i class="bi bi-pencil me-1 text-primary"></i>Edit Brand Kendaraan</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Brand Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editBrandName" class="form-control" required autocomplete="off">
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
                        <h6 class="modal-title fw-bold"><i class="bi bi-pencil me-1 text-success"></i>Edit Vehicle Type</h6>
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
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editTypeActive" value="1">
                            <label class="form-check-label small fw-semibold" for="editTypeActive">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-success px-3">Update Type</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- MODALS: BBN (WILAYAH)                                        --}}
    {{-- ============================================================ --}}
    {{-- Add BBN Modal --}}
    <div class="modal fade" id="addBbnModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('costing.master.bbn.store') }}">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-1 text-danger"></i>Add BBN Wilayah</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Kode BBN <span class="text-danger">*</span></label>
                            <input type="text" name="kode" class="form-control font-monospace" placeholder="e.g. JKT, MLL, SKB..." required autocomplete="off" style="text-transform: uppercase;">
                            <div class="text-muted small mt-1">Short code used internally (e.g. JKT).</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Wilayah / Meaning (Tanpa Singkatan) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Jakarta, Malili, Sukabumi..." required autocomplete="off">
                            <div class="text-muted small mt-1">Full city / region name displayed in Costing sheets.</div>
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
                        <h6 class="modal-title fw-bold"><i class="bi bi-pencil me-1 text-danger"></i>Edit BBN Wilayah</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Kode BBN <span class="text-danger">*</span></label>
                            <input type="text" name="kode" id="editBbnCode" class="form-control font-monospace" required autocomplete="off" style="text-transform: uppercase;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Wilayah / Meaning (Tanpa Singkatan) <span class="text-danger">*</span></label>
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

    {{-- ============================================================ --}}
    {{-- MODALS: REGIONS (DATA WILAYAH)                               --}}
    {{-- ============================================================ --}}
    {{-- Add Region Modal --}}
    <div class="modal fade" id="addRegionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('costing.master.regions.store') }}">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-1 text-info"></i>Add Wilayah (Pulau & Provinsi)</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Pulau <span class="text-danger">*</span></label>
                            <input type="text" name="pulau" list="pulauDatalist" class="form-control" placeholder="e.g. JAWA, SUMATERA, KALIMANTAN..." required autocomplete="off" style="text-transform: uppercase;">
                            <datalist id="pulauDatalist">
                                @foreach ($pulauList as $p)
                                    <option value="{{ $p }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Provinsi <span class="text-danger">*</span></label>
                            <input type="text" name="provinsi" list="provinsiDatalist" class="form-control" placeholder="e.g. JAWA BARAT, JAWA TENGAH..." required autocomplete="off" style="text-transform: uppercase;">
                            <datalist id="provinsiDatalist">
                                @foreach ($provinsiList as $pr)
                                    <option value="{{ $pr }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Area / Kota <span class="text-danger">*</span></label>
                            <input type="text" name="area" class="form-control" placeholder="e.g. Bandung, Semarang, Kaligawe..." required autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-info text-white px-3">Save Wilayah</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Region Modal --}}
    <div class="modal fade" id="editRegionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="editRegionForm" action="">
                @csrf @method('PUT')
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h6 class="modal-title fw-bold"><i class="bi bi-pencil me-1 text-info"></i>Edit Wilayah</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Pulau <span class="text-danger">*</span></label>
                            <input type="text" name="pulau" id="editRegionPulau" list="pulauDatalist" class="form-control" required autocomplete="off" style="text-transform: uppercase;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Provinsi <span class="text-danger">*</span></label>
                            <input type="text" name="provinsi" id="editRegionProvinsi" list="provinsiDatalist" class="form-control" required autocomplete="off" style="text-transform: uppercase;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Area / Kota <span class="text-danger">*</span></label>
                            <input type="text" name="area" id="editRegionArea" class="form-control" required autocomplete="off">
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editRegionActive" value="1">
                            <label class="form-check-label small fw-semibold" for="editRegionActive">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-info text-white px-3">Update Wilayah</button>
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
    function openEditTypeModal(id, brandId, name, isActive) {
        document.getElementById('editTypeForm').action = "{{ url('costing/master/vehicle-types') }}/" + id;
        document.getElementById('editTypeBrandId').value = brandId;
        document.getElementById('editTypeName').value = name;
        document.getElementById('editTypeActive').checked = isActive;
        new bootstrap.Modal(document.getElementById('editTypeModal')).show();
    }

    // BBN Modal Helper
    function openEditBbnModal(id, code, name, isActive) {
        document.getElementById('editBbnForm').action = "{{ url('costing/master/bbn') }}/" + id;
        document.getElementById('editBbnCode').value = code;
        document.getElementById('editBbnName').value = name;
        document.getElementById('editBbnActive').checked = isActive;
        new bootstrap.Modal(document.getElementById('editBbnModal')).show();
    }

    // Region Modal Helper
    function openEditRegionModal(id, pulau, provinsi, area, isActive) {
        document.getElementById('editRegionForm').action = "{{ url('costing/master/regions') }}/" + id;
        document.getElementById('editRegionPulau').value = pulau;
        document.getElementById('editRegionProvinsi').value = provinsi;
        document.getElementById('editRegionArea').value = area;
        document.getElementById('editRegionActive').checked = isActive;
        new bootstrap.Modal(document.getElementById('editRegionModal')).show();
    }
</script>
@endpush
