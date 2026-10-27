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
                <div class="p-3 bg-light border-bottom">
                    <form method="GET" action="{{ route('costing.master.index') }}" class="row g-2 align-items-center">
                        <input type="hidden" name="tab" value="types">
                        <div class="col-md-3">
                            <select name="filter_brand" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">— Filter by Brand (All) —</option>
                                @foreach ($allBrands as $bOpt)
                                    <option value="{{ $bOpt->name }}" {{ request('filter_brand') === $bOpt->name ? 'selected' : '' }}>
                                        {{ $bOpt->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" name="search_type" class="form-control border-start-0"
                                    placeholder="Search Model / Type Name..." value="{{ request('search_type') }}">
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-secondary">Search</button>
                            @if (request('search_type') || request('filter_brand'))
                                <a href="{{ route('costing.master.index', ['tab' => 'types']) }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 py-3" style="width: 60px;">#</th>
                                <th style="width: 140px;">Brand</th>
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
    function openEditTypeModal(id, brandId, name, isActive) {
        document.getElementById('editTypeForm').action = "{{ url('costing/master/vehicle-types') }}/" + id;
        document.getElementById('editTypeBrandId').value = brandId;
        document.getElementById('editTypeName').value = name;
        document.getElementById('editTypeActive').checked = isActive;
        new bootstrap.Modal(document.getElementById('editTypeModal')).show();
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
</script>
@endpush
