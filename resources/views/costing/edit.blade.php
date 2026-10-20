@extends('layouts.app')

@section('title', 'Edit Costing ' . $costing->costing_number)
@section('main_container_class', 'container-fluid px-3 px-xl-4')

@push('styles')

    <style>
        .costing-table {
            width: 100% !important;
            border-collapse: separate;
            border-spacing: 0;
        }

        .costing-table th,
        .costing-table td {
            vertical-align: middle;
            padding: 0.25rem 0.4rem;
        }

        .costing-label-col {
            width: 235px !important;
            min-width: 235px !important;
            background-color: #f8fafc;
            font-weight: 600;
            position: sticky;
            left: 0;
            z-index: 2;
            font-size: 0.82rem;
            vertical-align: middle;
        }

        .costing-header-col,
        .costing-table th:not(.costing-label-col),
        .costing-table td:not(.costing-label-col) {
            min-width: 210px !important;
            box-sizing: border-box;
        }

        .costing-table input.form-control,
        .costing-table select.form-select {
            font-size: 0.82rem;
            padding: 0.2rem 0.4rem;
            height: 29px;
        }

        .costing-table textarea.auto-expand {
            min-height: 29px !important;
            height: 29px;
            resize: none !important;
            overflow: hidden !important;
            line-height: 1.35 !important;
            padding: 0.25rem 0.4rem !important;
            font-size: 0.82rem !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            white-space: pre-wrap !important;
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            background-color: #fff;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .costing-table textarea.auto-expand:focus {
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .type-suggestions-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            min-width: 260px;
            max-height: 220px;
            overflow-y: auto;
            z-index: 1060;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            margin-top: 2px;
        }
        .type-suggestion-item {
            padding: 6px 10px;
            font-size: 0.8rem;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
            transition: background-color 0.15s ease;
            line-height: 1.3;
        }
        .type-suggestion-item:last-child {
            border-bottom: none;
        }
        .type-suggestion-item:hover,
        .type-suggestion-item.active {
            background-color: #e0f2fe;
            color: #0369a1;
        }
        .type-suggestion-item .item-brand {
            font-size: 0.7rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
        }
        .type-suggestion-item .item-name {
            font-weight: 500;
            color: #1e293b;
        }
        .type-suggestion-item:hover .item-name,
        .type-suggestion-item.active .item-name {
            color: #0369a1;
        }

        .costing-calc-row {
            background-color: #f0fdf4 !important;
            font-weight: 600;
        }

        .costing-inc-row {
            background-color: #fefce8 !important;
            font-weight: 600;
        }

        .costing-grand-row {
            background-color: #ede9fe !important;
            font-weight: 700;
            font-size: 0.875rem;
        }
    </style>
@endpush

@section('content')

    <form method="POST" action="{{ route('costing.update', $costing) }}" id="costingForm">
        @csrf
        @method('PUT')

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('costing.show', $costing) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <h4 class="fw-bold mb-0">Edit Costing: {{ $costing->costing_number }}</h4>
                </div>
                <p class="text-muted small mb-0 mt-1">Update vehicle options, prices, and accessories.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('costing.show', $costing) }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                <button type="submit" class="btn btn-primary btn-sm px-3">
                    <i class="bi bi-check-lg me-1"></i>Save Changes
                </button>
            </div>
        </div>

        {{-- Master Info Card --}}
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-info-circle me-2 text-primary"></i>Project & Customer
                    Details</h6>
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-start">
                    {{-- Section 1: Customer / Project (Left) --}}
                    <div class="col-lg-4 col-md-5">
                        <label class="form-label small fw-semibold">Nama Customer / Project <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-building"></i></span>
                            <input type="text" name="customer_name" id="customerNameInput"
                                class="form-control form-control-sm @error('customer_name') is-invalid @enderror"
                                placeholder="Masukkan nama customer / project..."
                                value="{{ old('customer_name', $costing->customer_name) }}" required autocomplete="off">
                        </div>
                        <div class="text-muted small mt-1">Ketik nama customer atau instansi project.</div>
                        @error('customer_name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Section 2: Contract Details - SMD, Masa Sewa, Delivery Date (Middle) --}}
                    <div class="col-lg-5 col-md-7 ps-lg-4" style="border-left: 1px solid #e9ecef;">
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label small fw-semibold mb-1">SMD</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                    <select name="smd" class="form-select form-select-sm">
                                        <option value="">Select SMD</option>
                                        @foreach ($smdList as $smd)
                                            <option value="{{ $smd }}" {{ old('smd', $costing->smd) === $smd ? 'selected' : '' }}>{{ $smd }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold mb-1">Masa Sewa</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-calendar3-range"></i></span>
                                    <input type="text" name="masa_sewa" list="masaSewaOptions" class="form-control form-control-sm"
                                        placeholder="e.g. 12 Bulan" value="{{ old('masa_sewa', $costing->masa_sewa) }}">
                                    <datalist id="masaSewaOptions">
                                        <option value="12 Bulan">
                                        <option value="24 Bulan">
                                        <option value="36 Bulan">
                                        <option value="48 Bulan">
                                        <option value="60 Bulan">
                                    </datalist>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-semibold mb-1">Request Delivery Date</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                    <input type="date" name="request_delivery_date" class="form-control form-control-sm"
                                        value="{{ old('request_delivery_date', $costing->request_delivery_date?->format('Y-m-d')) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section 3: Internal Notes / Reference (Right) --}}
                    <div class="col-lg-3 col-md-12 ps-lg-4" style="border-left: 1px solid #e9ecef;">
                        <label class="form-label small fw-semibold mb-1">Internal Notes / Reference</label>
                        <textarea name="notes" rows="4" class="form-control form-control-sm"
                            placeholder="Optional remarks or procurement notes...">{{ old('notes', $costing->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Comparison Sheet Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-grid-3x3-gap me-2 text-primary"></i>Vehicle Comparison Grid</h6>
                    <div class="form-check form-switch mb-0 d-flex align-items-center gap-2 ps-0 border-start ps-3">
                        <input class="form-check-input ms-0 mt-0" type="checkbox" role="switch" name="is_tender" id="isTenderSwitch" value="1" {{ old('is_tender', $costing->isTender()) ? 'checked' : '' }} style="cursor: pointer; width: 2.2em; height: 1.2em;">
                        <label class="form-check-label fw-bold small text-dark d-flex align-items-center gap-1" for="isTenderSwitch" style="cursor: pointer;">
                            <span class="badge bg-warning text-dark border border-warning-subtle px-2 py-1"><i class="bi bi-award me-1"></i>Tender FPH</span>
                            <span class="text-muted fw-normal small d-none d-md-inline">(Memerlukan Approval Manager)</span>
                        </label>
                    </div>

                    {{-- 3 Checkmarks for Same Brand, Same Type, Same BBN --}}
                    <div class="d-flex align-items-center gap-3 border-start ps-3 flex-wrap">
                        <div class="form-check mb-0 d-flex align-items-center gap-1">
                            <input class="form-check-input mt-0" type="checkbox" id="syncSameBrand" style="cursor: pointer;">
                            <label class="form-check-label small fw-semibold text-dark" for="syncSameBrand" style="cursor: pointer; user-select: none;">
                                Same Brand
                            </label>
                        </div>
                        <div class="form-check mb-0 d-flex align-items-center gap-1">
                            <input class="form-check-input mt-0" type="checkbox" id="syncSameType" style="cursor: pointer;">
                            <label class="form-check-label small fw-semibold text-dark" for="syncSameType" style="cursor: pointer; user-select: none;">
                                Same Type
                            </label>
                        </div>
                        <div class="form-check mb-0 d-flex align-items-center gap-1">
                            <input class="form-check-input mt-0" type="checkbox" id="syncSameBbn" style="cursor: pointer;">
                            <label class="form-check-label small fw-semibold text-dark" for="syncSameBbn" style="cursor: pointer; user-select: none;">
                                Same BBN
                            </label>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshCatalogBtn" onclick="refreshCostingCatalog(this)" title="Sync latest vehicle catalog, BBN & suppliers from Odoo without losing entered data">
                        <i class="bi bi-arrow-clockwise me-1"></i><span>Sync from Odoo</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addVehicleBtn">
                        <i class="bi bi-plus-circle me-1"></i>Add Vehicle Column (+)
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 costing-table" id="costingGrid">
                        <thead>
                            <tr class="table-light" id="vehicleHeaderRow">
                                <th class="costing-label-col py-3">Vehicle Details</th>
                                {{-- Dynamically populated columns --}}
                            </tr>
                        </thead>
                        <tbody>
                            <tr id="rowBrand">
                                <td class="costing-label-col">Brand :</td>
                            </tr>
                            <tr id="rowType">
                                <td class="costing-label-col">Type Kendaraan :</td>
                            </tr>
                            <tr id="rowBbn">
                                <td class="costing-label-col">BBN :</td>
                            </tr>
                            <tr id="rowPemakaian">
                                <td class="costing-label-col">Pemakaian :</td>
                            </tr>
                            <tr id="rowOtr">
                                <td class="costing-label-col">Harga OTR :</td>
                            </tr>
                            <tr id="rowDiskon">
                                <td class="costing-label-col">Diskon :</td>
                            </tr>
                            <tr id="rowNett" class="costing-calc-row">
                                <td class="costing-label-col text-success fw-bold">Harga Nett :</td>
                            </tr>
                            <tr id="rowEstimasi">
                                <td class="costing-label-col">Estimasi kenaikan harga :</td>
                            </tr>
                            <tr id="rowAfterInc" class="costing-inc-row">
                                <td class="costing-label-col text-dark fw-bold text-nowrap">Harga setelah estimasi kenaikan
                                    :</td>
                            </tr>

                            {{-- Section: Accessories Header --}}
                            <tr class="table-light">
                                <td class="fw-bold text-primary py-2 px-3 border-top border-bottom bg-light"
                                    id="accHeaderCell" colspan="3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span><i class="bi bi-wrench-adjustable me-1"></i>Tambahan (Accessories & Upgrades)</span>
                                        <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2"
                                            onclick="addAccessoryRow(''); return false;">
                                            <i class="bi bi-plus me-1"></i>Add Row (+)
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            {{-- Dynamic Accessories Rows Container --}}

                            <tr id="rowTotalAcc" class="table-secondary fw-bold">
                                <td class="costing-label-col">Total Acc :</td>
                            </tr>

                            <tr id="rowGrandTotal" class="costing-grand-row">
                                <td class="costing-label-col text-primary">Harga Unit + Aksesoris :</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light py-2 text-muted small d-flex justify-content-between align-items-center">
                <span><i class="bi bi-info-circle me-1"></i>Calculations update live automatically. You can add or remove
                    vehicle columns and accessory rows dynamically.</span>
                <button type="submit" class="btn btn-primary btn-sm px-4">Update Costing</button>
            </div>
        </div>
    </form>

    <datalist id="globalBrandList">
        @foreach ($brands as $b)
            <option value="{{ $b }}"></option>
        @endforeach
    </datalist>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        function onSupplierSelected(sel) {
            if (sel && sel.value) {
                const inp = document.getElementById('customerNameInput');
                if (inp) inp.value = sel.value;
            }
        }

        let vehicleCount = 0;
        let accessoryCount = 0;
        let bbnOptions = @json($bbnList);
        let odooBrands = @json($brands);
        let odooProducts = @json($products);
        const existingVehicles = @json($costing->vehicles);
        const existingAccRows = @json($accRows);

        // Seamless live refresh of Odoo vehicle catalog, BBN & suppliers without reloading or losing typed form data
        async function refreshCostingCatalog(btn) {
            if (!btn) return;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Syncing Odoo...`;

            try {
                const response = await fetch('{{ route('costing.api.refresh', [], false) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Network error syncing catalog from Odoo');
                }

                const data = await response.json();
                if (data.success) {
                    // 1. Update vehicle brands & products
                    odooBrands = data.brands || [];
                    odooProducts = data.products || [];

                    const dl = document.getElementById('globalBrandList');
                    if (dl) {
                        dl.innerHTML = '';
                        odooBrands.forEach(b => {
                            const opt = document.createElement('option');
                            opt.value = b;
                            dl.appendChild(opt);
                        });
                    }

                    // 2. Update BBN Options & existing vehicle BBN dropdowns (preserve current selections)
                    if (data.bbn && data.bbn.length) {
                        bbnOptions = data.bbn;
                        document.querySelectorAll('tr#rowBbn select').forEach(sel => {
                            const curVal = sel.value;
                            let html = '<option value="">-- BBN --</option>';
                            bbnOptions.forEach(b => {
                                const label = b.name || b.display_name || b.kode;
                                const isSel = (b.name === curVal || b.kode === curVal || b.display_name === curVal) ? 'selected' : '';
                                html += `<option value="${label}" ${isSel}>${label}</option>`;
                            });
                            sel.innerHTML = html;
                        });
                    }

                    // 3. Update Supplier Dropdown (preserve current selection)
                    if (data.vendors && data.vendors.length) {
                        const supSel = document.getElementById('supplierSelect');
                        if (supSel) {
                            const curSup = supSel.value;
                            let supHtml = '<option value="">— Pilih supplier —</option>';
                            data.vendors.forEach(v => {
                                const optVal = v.name || '';
                                const optLabel = optVal + (v.city ? ' - ' + v.city : '');
                                const isSel = (optVal === curSup) ? 'selected' : '';
                                supHtml += `<option value="${optVal}" ${isSel}>${optLabel}</option>`;
                            });
                            supSel.innerHTML = supHtml;
                        }
                    }

                    // Success feedback
                    btn.className = 'btn btn-sm btn-success text-white';
                    btn.innerHTML = `<i class="bi bi-check-lg me-1"></i>Synced (${data.counts.brands} brands, ${data.counts.products} types, ${data.counts.bbn} BBN, ${data.counts.suppliers} suppliers)`;

                    setTimeout(() => {
                        btn.className = 'btn btn-sm btn-outline-secondary';
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                    }, 3500);
                } else {
                    throw new Error(data.message || 'Sync failed');
                }
            } catch (err) {
                console.error('Error syncing Odoo catalog:', err);
                btn.className = 'btn btn-sm btn-outline-danger';
                btn.innerHTML = `<i class="bi bi-exclamation-triangle me-1"></i>Sync Failed`;
                setTimeout(() => {
                    btn.className = 'btn btn-sm btn-outline-secondary';
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }, 3000);
            }
        }

        function formatRupiah(num) {
            if (isNaN(num) || num === null || num === '') num = 0;
            return 'Rp ' + Math.round(num).toLocaleString('id-ID');
        }

        function cleanNumber(val) {
            if (val === undefined || val === null || val === '') return '';
            const num = parseFloat(val);
            if (isNaN(num)) return '';
            return (num % 1 === 0) ? Math.round(num).toString() : num.toString();
        }

        function getFirstVehicleIndex() {
            const firstTh = document.querySelector('#vehicleHeaderRow th.costing-header-col');
            if (!firstTh) return null;
            const match = firstTh.id.match(/^colHeader_(\d+)$/);
            return match ? parseInt(match[1]) : null;
        }

        function getAllVehicleIndices() {
            const indices = [];
            document.querySelectorAll('#vehicleHeaderRow th.costing-header-col').forEach(th => {
                const m = th.id.match(/^colHeader_(\d+)$/);
                if (m) indices.push(parseInt(m[1]));
            });
            return indices;
        }

        function syncBrandToAll() {
            const isChecked = document.getElementById('syncSameBrand')?.checked;
            if (!isChecked) return;
            const firstIdx = getFirstVehicleIndex();
            if (firstIdx === null) return;
            const firstVal = document.getElementById(`inputBrand_${firstIdx}`)?.value || '';

            getAllVehicleIndices().forEach(idx => {
                if (idx !== firstIdx) {
                    const input = document.getElementById(`inputBrand_${idx}`);
                    if (input) {
                        input.value = firstVal;
                    }
                }
            });
        }

        function syncTypeToAll() {
            const isChecked = document.getElementById('syncSameType')?.checked;
            if (!isChecked) return;
            const firstIdx = getFirstVehicleIndex();
            if (firstIdx === null) return;
            const firstTypeInp = document.getElementById(`inputType_${firstIdx}`);
            const firstProdIdInp = document.getElementById(`inputOdooProductId_${firstIdx}`);
            if (!firstTypeInp) return;

            const firstVal = firstTypeInp.value || '';
            const firstProdId = firstProdIdInp ? firstProdIdInp.value : '';

            getAllVehicleIndices().forEach(idx => {
                if (idx !== firstIdx) {
                    const inp = document.getElementById(`inputType_${idx}`);
                    const prodInp = document.getElementById(`inputOdooProductId_${idx}`);
                    if (inp) {
                        inp.value = firstVal;
                        autoResize(inp);
                    }
                    if (prodInp) {
                        prodInp.value = firstProdId;
                    }
                }
            });
        }

        function syncBbnToAll() {
            const isChecked = document.getElementById('syncSameBbn')?.checked;
            if (!isChecked) return;
            const firstIdx = getFirstVehicleIndex();
            if (firstIdx === null) return;
            const firstSelect = document.querySelector(`#cellBbn_${firstIdx} select`);
            if (!firstSelect) return;
            const firstVal = firstSelect.value || '';

            getAllVehicleIndices().forEach(idx => {
                if (idx !== firstIdx) {
                    const sel = document.querySelector(`#cellBbn_${idx} select`);
                    if (sel) {
                        sel.value = firstVal;
                    }
                }
            });
        }

        function onBbnChanged(vIndex) {
            const firstIdx = getFirstVehicleIndex();
            if (vIndex === firstIdx && document.getElementById('syncSameBbn')?.checked) {
                syncBbnToAll();
            }
        }

        function addVehicleColumn(data = {}) {
            const vIndex = vehicleCount;
            vehicleCount++;

            // If sync checkmarks are active and this is not the first column, auto-populate from Vehicle #1
            const firstIdx = getFirstVehicleIndex();
            let brand = data.brand || '';
            let vType = data.vehicle_type || '';
            let odooProdId = data.odoo_product_id || '';
            let bbn = data.bbn || '';

            if (firstIdx !== null && vIndex !== firstIdx) {
                if (!brand && document.getElementById('syncSameBrand')?.checked) {
                    brand = document.getElementById(`inputBrand_${firstIdx}`)?.value || '';
                }
                if (!vType && document.getElementById('syncSameType')?.checked) {
                    vType = document.getElementById(`inputType_${firstIdx}`)?.value || '';
                    odooProdId = document.getElementById(`inputOdooProductId_${firstIdx}`)?.value || '';
                }
                if (!bbn && document.getElementById('syncSameBbn')?.checked) {
                    const firstBbnSel = document.querySelector(`#cellBbn_${firstIdx} select`);
                    if (firstBbnSel) bbn = firstBbnSel.value || '';
                }
            }
            const pemakaian = data.pemakaian || '';
            const otr = cleanNumber(data.harga_otr);
            const diskon = cleanNumber(data.diskon);
            const estimasi = cleanNumber(data.estimasi_kenaikan);

            const th = document.createElement('th');
            th.className = 'costing-header-col text-center py-2';
            th.id = `colHeader_${vIndex}`;
            th.innerHTML = `
                <div class="d-flex align-items-center justify-content-between bg-white p-2 rounded border shadow-sm">
                    <span class="fw-bold text-primary small vehicle-col-title"><i class="bi bi-car-front me-1"></i>Vehicle #${vIndex + 1}</span>
                    <button type="button" class="btn btn-xs btn-outline-danger py-0 px-2 remove-col-btn" title="Remove Vehicle (-)" onclick="removeVehicleColumn(${vIndex})">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                </div>
            `;
            document.getElementById('vehicleHeaderRow').appendChild(th);

            const tdBrand = document.createElement('td');
            tdBrand.id = `cellBrand_${vIndex}`;
            tdBrand.innerHTML = `<input type="text" name="vehicles[${vIndex}][brand]" id="inputBrand_${vIndex}" list="globalBrandList" class="form-control form-control-sm" placeholder="Choose brand from Odoo or type..." value="${brand}" oninput="onBrandChanged(${vIndex})">`;
            document.getElementById('rowBrand').appendChild(tdBrand);

            const tdType = document.createElement('td');
            tdType.id = `cellType_${vIndex}`;
            tdType.innerHTML = `
                <div class="position-relative w-100">
                    <textarea name="vehicles[${vIndex}][vehicle_type]" id="inputType_${vIndex}"
                        class="form-control form-control-sm auto-expand" placeholder="Type kendaraan..."
                        rows="1" autocomplete="off"
                        oninput="handleTypeInput(${vIndex})"
                        onfocus="showTypeSuggestions(${vIndex})"
                        onkeydown="handleTypeKeyDown(event, ${vIndex})">${vType}</textarea>
                    <input type="hidden" name="vehicles[${vIndex}][odoo_product_id]" id="inputOdooProductId_${vIndex}" value="${odooProdId || data.odoo_product_id || ''}">
                    <div id="typeSuggestions_${vIndex}" class="type-suggestions-dropdown shadow rounded border bg-white" style="display: none;"></div>
                </div>
            `;
            document.getElementById('rowType').appendChild(tdType);

            const tdBbn = document.createElement('td');
            tdBbn.id = `cellBbn_${vIndex}`;
            let bbnOptsHtml = `<option value="">-- BBN --</option>`;
            bbnOptions.forEach(b => {
                const label = b.name || b.display_name || b.kode;
                const isSel = (b.name === bbn || b.kode === bbn || b.display_name === bbn) ? 'selected' : '';
                bbnOptsHtml += `<option value="${label}" ${isSel}>${label}</option>`;
            });
            tdBbn.innerHTML = `<select name="vehicles[${vIndex}][bbn]" class="form-select form-select-sm" onchange="onBbnChanged(${vIndex})">${bbnOptsHtml}</select>`;
            document.getElementById('rowBbn').appendChild(tdBbn);

            const tdPemakaian = document.createElement('td');
            tdPemakaian.id = `cellPemakaian_${vIndex}`;
            tdPemakaian.innerHTML = `
                <textarea name="vehicles[${vIndex}][pemakaian]" id="inputPemakaian_${vIndex}"
                    class="form-control form-control-sm auto-expand" placeholder="e.g. Jabodetabek"
                    rows="1" oninput="autoResize(this)"
                    onkeydown="if(event.key==='Enter' && !event.shiftKey){ event.preventDefault(); this.blur(); }">${pemakaian}</textarea>
            `;
            document.getElementById('rowPemakaian').appendChild(tdPemakaian);

            autoResize(tdType.querySelector('textarea'));
            autoResize(tdPemakaian.querySelector('textarea'));

            const tdOtr = document.createElement('td');
            tdOtr.id = `cellOtr_${vIndex}`;
            tdOtr.innerHTML = `
                <input type="number" step="any" name="vehicles[${vIndex}][harga_otr]" class="form-control form-control-sm text-end num-input"
                    id="inputOtr_${vIndex}" value="${otr}" placeholder="0" oninput="calculateColumn(${vIndex})" title="${otr ? formatRupiah(otr) : ''}">
            `;
            document.getElementById('rowOtr').appendChild(tdOtr);

            const tdDiskon = document.createElement('td');
            tdDiskon.id = `cellDiskon_${vIndex}`;
            tdDiskon.innerHTML = `
                <input type="number" step="any" name="vehicles[${vIndex}][diskon]" class="form-control form-control-sm text-end num-input"
                    id="inputDiskon_${vIndex}" value="${diskon}" placeholder="0" oninput="calculateColumn(${vIndex})" title="${diskon ? formatRupiah(diskon) : ''}">
            `;
            document.getElementById('rowDiskon').appendChild(tdDiskon);

            const tdNett = document.createElement('td');
            tdNett.id = `cellNett_${vIndex}`;
            tdNett.className = 'fw-bold text-success';
            tdNett.innerHTML = `<div class="text-end small" id="valNett_${vIndex}">Rp 0</div>`;
            document.getElementById('rowNett').appendChild(tdNett);

            const tdEstimasi = document.createElement('td');
            tdEstimasi.id = `cellEstimasi_${vIndex}`;
            tdEstimasi.innerHTML = `
                <input type="number" step="any" name="vehicles[${vIndex}][estimasi_kenaikan]" class="form-control form-control-sm text-end num-input"
                    id="inputEstimasi_${vIndex}" value="${estimasi}" placeholder="0" oninput="calculateColumn(${vIndex})" title="${estimasi ? formatRupiah(estimasi) : ''}">
            `;
            document.getElementById('rowEstimasi').appendChild(tdEstimasi);

            const tdAfterInc = document.createElement('td');
            tdAfterInc.id = `cellAfterInc_${vIndex}`;
            tdAfterInc.className = 'fw-bold';
            tdAfterInc.innerHTML = `<div class="text-end small" id="valAfterInc_${vIndex}">Rp 0</div>`;
            document.getElementById('rowAfterInc').appendChild(tdAfterInc);

            document.querySelectorAll('tr[id^="accRow_"]').forEach(accRow => {
                const aIdx = accRow.getAttribute('data-acc-index');
                const tdAcc = document.createElement('td');
                tdAcc.id = `cellAcc_${aIdx}_${vIndex}`;
                const defAmt = accRow.getAttribute('data-default-amount') || 0;
                const cleanDef = cleanNumber(defAmt);
                tdAcc.innerHTML = `
                    <input type="number" step="any" name="accessories[${aIdx}][amounts][${vIndex}]" class="form-control form-control-sm text-end num-input"
                        id="inputAcc_${aIdx}_${vIndex}" value="${cleanDef}" placeholder="0" oninput="calculateColumn(${vIndex})" title="${defAmt ? formatRupiah(defAmt) : ''}">
                `;
                accRow.appendChild(tdAcc);
            });

            const tdTotalAcc = document.createElement('td');
            tdTotalAcc.id = `cellTotalAcc_${vIndex}`;
            tdTotalAcc.className = 'fw-bold';
            tdTotalAcc.innerHTML = `<div class="text-end small" id="valTotalAcc_${vIndex}">Rp 0</div>`;
            document.getElementById('rowTotalAcc').appendChild(tdTotalAcc);

            const tdGrand = document.createElement('td');
            tdGrand.id = `cellGrand_${vIndex}`;
            tdGrand.className = 'fw-bold text-primary';
            tdGrand.innerHTML = `<div class="text-end small" id="valGrand_${vIndex}">Rp 0</div>`;
            document.getElementById('rowGrandTotal').appendChild(tdGrand);

            updateRemoveColButtonsState();
            renumberVehicleColumns();
            updateAccHeaderColspan();
            calculateColumn(vIndex);
        }

        // Auto-resize textarea to expand height downward when text wraps
        function autoResize(el) {
            if (!el) return;
            el.style.height = 'auto';
            const newHeight = Math.max(29, el.scrollHeight);
            el.style.height = newHeight + 'px';
        }

        let activeTypeIndex = {};

        function getFilteredProducts(vIndex, query = '') {
            const brandInput = document.getElementById(`inputBrand_${vIndex}`);
            const selectedBrand = brandInput ? brandInput.value.trim().toLowerCase() : '';
            const q = (query || '').trim().toLowerCase();

            return odooProducts.filter(p => {
                const pBrand = (Array.isArray(p.vehicle_brand_id) && p.vehicle_brand_id[1]) ? p.vehicle_brand_id[1].toLowerCase() : '';
                const pName = (p.name || '').toLowerCase();
                const pDisp = (p.display_name || '').toLowerCase();

                if (selectedBrand && !pBrand.includes(selectedBrand)) {
                    return false;
                }
                if (q && !pName.includes(q) && !pDisp.includes(q)) {
                    return false;
                }
                return true;
            }).slice(0, 30);
        }

        function showTypeSuggestions(vIndex) {
            const textarea = document.getElementById(`inputType_${vIndex}`);
            const dropdown = document.getElementById(`typeSuggestions_${vIndex}`);
            if (!textarea || !dropdown) return;

            const matches = getFilteredProducts(vIndex, textarea.value);
            dropdown.innerHTML = '';

            if (matches.length === 0) {
                dropdown.style.display = 'none';
                return;
            }

            activeTypeIndex[vIndex] = -1;

            matches.forEach((p, idx) => {
                const item = document.createElement('div');
                item.className = 'type-suggestion-item';
                item.dataset.index = idx;

                const pBrand = (Array.isArray(p.vehicle_brand_id) && p.vehicle_brand_id[1]) ? p.vehicle_brand_id[1] : '';

                if (pBrand) {
                    const brandDiv = document.createElement('div');
                    brandDiv.className = 'item-brand';
                    brandDiv.textContent = pBrand;
                    item.appendChild(brandDiv);
                }

                const nameDiv = document.createElement('div');
                nameDiv.className = 'item-name';
                nameDiv.textContent = p.name || p.display_name || '';
                item.appendChild(nameDiv);

                item.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    selectTypeSuggestion(vIndex, p);
                });

                dropdown.appendChild(item);
            });

            dropdown.style.display = 'block';
        }

        function handleTypeInput(vIndex) {
            const textarea = document.getElementById(`inputType_${vIndex}`);
            if (textarea) {
                autoResize(textarea);
            }
            showTypeSuggestions(vIndex);
            const firstIdx = getFirstVehicleIndex();
            if (vIndex === firstIdx && document.getElementById('syncSameType')?.checked) {
                syncTypeToAll();
            }
        }

        function selectTypeSuggestion(vIndex, product) {
            const textarea = document.getElementById(`inputType_${vIndex}`);
            const hiddenId = document.getElementById(`inputOdooProductId_${vIndex}`);
            const brandInput = document.getElementById(`inputBrand_${vIndex}`);
            const dropdown = document.getElementById(`typeSuggestions_${vIndex}`);

            if (textarea) {
                textarea.value = product.name || product.display_name || '';
                autoResize(textarea);
            }
            if (hiddenId) {
                hiddenId.value = product.id || '';
            }
            if (brandInput && !brandInput.value.trim()) {
                const pBrand = (Array.isArray(product.vehicle_brand_id) && product.vehicle_brand_id[1]) ? product.vehicle_brand_id[1] : '';
                if (pBrand) {
                    brandInput.value = pBrand.split(' - ')[0] || pBrand;
                }
            }
            if (dropdown) {
                dropdown.style.display = 'none';
            }

            const firstIdx = getFirstVehicleIndex();
            if (vIndex === firstIdx) {
                if (document.getElementById('syncSameBrand')?.checked && brandInput && brandInput.value) {
                    syncBrandToAll();
                }
                if (document.getElementById('syncSameType')?.checked) {
                    syncTypeToAll();
                }
            }
        }

        function handleTypeKeyDown(e, vIndex) {
            const dropdown = document.getElementById(`typeSuggestions_${vIndex}`);
            if (!dropdown || dropdown.style.display === 'none') {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    e.target.blur();
                }
                return;
            }

            const items = dropdown.querySelectorAll('.type-suggestion-item');
            if (!items.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                let cur = activeTypeIndex[vIndex] ?? -1;
                cur = (cur + 1) % items.length;
                activeTypeIndex[vIndex] = cur;
                highlightSuggestion(items, cur);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                let cur = activeTypeIndex[vIndex] ?? -1;
                cur = (cur - 1 + items.length) % items.length;
                activeTypeIndex[vIndex] = cur;
                highlightSuggestion(items, cur);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                const cur = activeTypeIndex[vIndex] ?? -1;
                if (cur >= 0 && items[cur]) {
                    items[cur].dispatchEvent(new MouseEvent('mousedown', { bubbles: true, cancelable: true }));
                } else {
                    dropdown.style.display = 'none';
                }
            } else if (e.key === 'Escape') {
                dropdown.style.display = 'none';
            }
        }

        function highlightSuggestion(items, index) {
            items.forEach((it, i) => {
                if (i === index) {
                    it.classList.add('active');
                    it.scrollIntoView({ block: 'nearest' });
                } else {
                    it.classList.remove('active');
                }
            });
        }

        function onBrandChanged(vIndex) {
            const dropdown = document.getElementById(`typeSuggestions_${vIndex}`);
            if (dropdown && dropdown.style.display !== 'none') {
                showTypeSuggestions(vIndex);
            }
            const firstIdx = getFirstVehicleIndex();
            if (vIndex === firstIdx && document.getElementById('syncSameBrand')?.checked) {
                syncBrandToAll();
            }
        }

        function removeVehicleColumn(vIndex) {
            const activeCols = document.querySelectorAll('th[id^="colHeader_"]');
            if (activeCols.length <= 1) {
                alert('A costing sheet requires at least one vehicle column.');
                return;
            }

            if (!confirm('Remove this vehicle column?')) return;

            const idsToRemove = [
                `colHeader_${vIndex}`,
                `cellBrand_${vIndex}`,
                `cellType_${vIndex}`,
                `cellBbn_${vIndex}`,
                `cellPemakaian_${vIndex}`,
                `cellOtr_${vIndex}`,
                `cellDiskon_${vIndex}`,
                `cellNett_${vIndex}`,
                `cellEstimasi_${vIndex}`,
                `cellAfterInc_${vIndex}`,
                `cellTotalAcc_${vIndex}`,
                `cellGrand_${vIndex}`
            ];

            idsToRemove.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.remove();
            });

            document.querySelectorAll(`td[id^="cellAcc_"][id$="_${vIndex}"]`).forEach(el => el.remove());
            updateRemoveColButtonsState();
            renumberVehicleColumns();
            updateAccHeaderColspan();
        }

        function updateAccHeaderColspan() {
            const accHdr = document.getElementById('accHeaderCell');
            const headerRow = document.getElementById('vehicleHeaderRow');
            if (!accHdr || !headerRow) return;

            const totalCols = headerRow.querySelectorAll('th').length;
            accHdr.colSpan = totalCols;
            accHdr.setAttribute('colspan', totalCols);

            // Force Chromium/Blink table layout engine to recalculate column span across all columns
            accHdr.style.display = 'none';
            void accHdr.offsetHeight;
            accHdr.style.display = '';
        }

        function renumberVehicleColumns() {
            const activeHeaders = document.querySelectorAll('th[id^="colHeader_"]');
            activeHeaders.forEach((th, idx) => {
                const titleSpan = th.querySelector('.vehicle-col-title');
                if (titleSpan) {
                    titleSpan.innerHTML = `<i class="bi bi-car-front me-1"></i>Vehicle #${idx + 1}`;
                }
            });
        }

        function updateRemoveColButtonsState() {
            const btns = document.querySelectorAll('.remove-col-btn');
            btns.forEach(btn => {
                btn.disabled = btns.length <= 1;
            });
        }

        function addAccessoryRow(name = '', defaultAmount = 0, amountsPerVeh = {}) {
            const aIndex = accessoryCount;
            accessoryCount++;

            const tr = document.createElement('tr');
            tr.id = `accRow_${aIndex}`;
            tr.setAttribute('data-acc-index', aIndex);
            tr.setAttribute('data-default-amount', defaultAmount);

            const tdLabel = document.createElement('td');
            tdLabel.className = 'costing-label-col';
            tdLabel.innerHTML = `
                <div class="d-flex align-items-start gap-1">
                    <button type="button" class="btn btn-xs btn-outline-danger p-0 px-1 mt-1" title="Remove Row (-)" onclick="removeAccessoryRow(${aIndex})">
                        <i class="bi bi-dash"></i>
                    </button>
                    <textarea name="accessories[${aIndex}][name]" id="inputAccName_${aIndex}"
                        class="form-control form-control-sm auto-expand py-1 fw-semibold"
                        placeholder="Accessory / Upgrade name..." rows="1" style="font-size:0.8rem"
                        oninput="autoResize(this)"
                        onkeydown="if(event.key==='Enter' && !event.shiftKey){ event.preventDefault(); this.blur(); }">${name}</textarea>
                </div>
            `;
            tr.appendChild(tdLabel);
            autoResize(tdLabel.querySelector('textarea'));

            document.querySelectorAll('th[id^="colHeader_"]').forEach((col, colIdx) => {
                const vIndex = col.id.replace('colHeader_', '');
                const tdAcc = document.createElement('td');
                tdAcc.id = `cellAcc_${aIndex}_${vIndex}`;
                const rawVal = (amountsPerVeh[colIdx] !== undefined) ? amountsPerVeh[colIdx] : defaultAmount;
                const val = cleanNumber(rawVal);
                tdAcc.innerHTML = `
                    <input type="number" step="any" name="accessories[${aIndex}][amounts][${vIndex}]" class="form-control form-control-sm text-end num-input"
                        id="inputAcc_${aIndex}_${vIndex}" value="${val}" placeholder="0" oninput="calculateColumn(${vIndex})" title="${val ? formatRupiah(val) : ''}">
                `;
                tr.appendChild(tdAcc);
            });

            const rowTotalAcc = document.getElementById('rowTotalAcc');
            rowTotalAcc.parentNode.insertBefore(tr, rowTotalAcc);

            document.querySelectorAll('th[id^="colHeader_"]').forEach(col => {
                const vIndex = col.id.replace('colHeader_', '');
                calculateColumn(vIndex);
            });

            updateAccHeaderColspan();
        }

        function removeAccessoryRow(aIndex) {
            const tr = document.getElementById(`accRow_${aIndex}`);
            if (tr) {
                tr.remove();
                document.querySelectorAll('th[id^="colHeader_"]').forEach(col => {
                    const vIndex = col.id.replace('colHeader_', '');
                    calculateColumn(vIndex);
                });
            }
        }

        function calculateColumn(vIndex) {
            const otrInput = document.getElementById(`inputOtr_${vIndex}`);
            const diskonInput = document.getElementById(`inputDiskon_${vIndex}`);
            const estimasiInput = document.getElementById(`inputEstimasi_${vIndex}`);

            if (!otrInput) return;

            const otr = parseFloat(otrInput.value) || 0;
            const diskon = parseFloat(diskonInput ? diskonInput.value : 0) || 0;
            const estimasi = parseFloat(estimasiInput ? estimasiInput.value : 0) || 0;

            if (otrInput) otrInput.title = otr ? formatRupiah(otr) : '';
            if (diskonInput) diskonInput.title = diskon ? formatRupiah(diskon) : '';
            if (estimasiInput) estimasiInput.title = estimasi ? formatRupiah(estimasi) : '';

            const nett = Math.max(0, otr - diskon);
            const valNett = document.getElementById(`valNett_${vIndex}`);
            if (valNett) valNett.innerText = formatRupiah(nett);

            const afterInc = nett + estimasi;
            const valAfterInc = document.getElementById(`valAfterInc_${vIndex}`);
            if (valAfterInc) valAfterInc.innerText = formatRupiah(afterInc);

            let totalAcc = 0;
            document.querySelectorAll(`input[id^="inputAcc_"][id$="_${vIndex}"]`).forEach(accInp => {
                const amt = parseFloat(accInp.value) || 0;
                totalAcc += amt;
                accInp.title = amt ? formatRupiah(amt) : '';
            });

            const valTotalAcc = document.getElementById(`valTotalAcc_${vIndex}`);
            if (valTotalAcc) valTotalAcc.innerText = formatRupiah(totalAcc);

            const grandTotal = afterInc + totalAcc;
            const valGrand = document.getElementById(`valGrand_${vIndex}`);
            if (valGrand) valGrand.innerText = formatRupiah(grandTotal);
        }

        // Populate existing data
        document.addEventListener('DOMContentLoaded', function () {
            if (existingVehicles && existingVehicles.length > 0) {
                existingVehicles.forEach(v => {
                    addVehicleColumn(v);
                });
            } else {
                addVehicleColumn();
            }

            if (existingAccRows && Object.keys(existingAccRows).length > 0) {
                Object.values(existingAccRows).forEach(accData => {
                    const amountsPerVeh = {};
                    existingVehicles.forEach((v, idx) => {
                        amountsPerVeh[idx] = accData.amounts[v.id] ?? 0;
                    });
                    addAccessoryRow(accData.name, 0, amountsPerVeh);
                });
            } else {
                addAccessoryRow('', '');
            }



            document.getElementById('addVehicleBtn').addEventListener('click', function () {
                addVehicleColumn();
            });

            // Listeners for Same Brand, Same Type, Same BBN checkmarks
            document.getElementById('syncSameBrand')?.addEventListener('change', function() {
                if (this.checked) syncBrandToAll();
            });
            document.getElementById('syncSameType')?.addEventListener('change', function() {
                if (this.checked) syncTypeToAll();
            });
            document.getElementById('syncSameBbn')?.addEventListener('change', function() {
                if (this.checked) syncBbnToAll();
            });

            updateAccHeaderColspan();

            // Close suggestion dropdowns on click outside
            document.addEventListener('click', function (e) {
                if (!e.target.closest('.position-relative')) {
                    document.querySelectorAll('.type-suggestions-dropdown').forEach(d => d.style.display = 'none');
                }
            });

            // Adjust heights on window resize
            window.addEventListener('resize', function () {
                document.querySelectorAll('textarea.auto-expand').forEach(autoResize);
            });

            // Trigger initial auto-resize across all loaded elements
            setTimeout(() => {
                document.querySelectorAll('textarea.auto-expand').forEach(autoResize);
            }, 50);
        });
    </script>
@endpush