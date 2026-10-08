@extends('layouts.app')

@section('title', 'Edit Costing ' . $costing->costing_number)

@push('styles')
<style>
    .costing-table th, .costing-table td {
        vertical-align: middle;
        padding: 0.5rem 0.65rem;
    }
    .costing-header-col {
        min-width: 250px;
        max-width: 320px;
    }
    .costing-label-col {
        width: 220px;
        min-width: 200px;
        background-color: #f8fafc;
        font-weight: 600;
        position: sticky;
        left: 0;
        z-index: 2;
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
        font-size: 0.95rem;
    }
    .formatted-display {
        font-size: 0.75rem;
        color: #4b5563;
        font-weight: 500;
        margin-top: 0.15rem;
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
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-info-circle me-2 text-primary"></i>Project & Customer Details</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-7">
                    <label class="form-label small fw-semibold">Customer / Instansi / Project <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-building"></i></span>
                        <input type="text" name="customer_name" id="customerNameInput" list="customerList"
                            class="form-control form-control-sm @error('customer_name') is-invalid @enderror"
                            placeholder="e.g. Sekretariat Jendral Biro Perencanaan Kementrian Perhubungan RI"
                            value="{{ old('customer_name', $costing->customer_name) }}" required autocomplete="off">
                        <datalist id="customerList"></datalist>
                    </div>
                    @error('customer_name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Internal Notes / Reference</label>
                    <input type="text" name="notes" class="form-control form-control-sm"
                        placeholder="Optional remarks or procurement notes..." value="{{ old('notes', $costing->notes) }}">
                </div>
            </div>
        </div>
    </div>

    {{-- Comparison Sheet Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-grid-3x3-gap me-2 text-primary"></i>Vehicle Comparison Grid</h6>
            <div>
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
                            <td class="costing-label-col text-dark fw-bold">Harga setelah estimasi kenaikan :</td>
                        </tr>

                        {{-- Section: Accessories Header --}}
                        <tr class="table-light">
                            <td class="costing-label-col fw-bold text-primary py-2" colspan="100%">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span><i class="bi bi-wrench-adjustable me-1"></i>Tambahan (Accessories & Upgrades) :</span>
                                    <div class="d-flex gap-2">
                                        <div class="dropdown">
                                            <button class="btn btn-xs btn-outline-secondary dropdown-toggle py-0 px-2" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-bookmark-plus me-1"></i>Add from Template
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm" id="presetMenu">
                                                @foreach ($templates as $tmpl)
                                                    <li>
                                                        <a class="dropdown-item small" href="#" onclick="addAccessoryRow('{{ addslashes($tmpl->name) }}', {{ $tmpl->default_amount }}); return false;">
                                                            {{ $tmpl->name }} (Rp {{ number_format($tmpl->default_amount, 0, ',', '.') }})
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <button type="button" class="btn btn-xs btn-primary py-0 px-2" onclick="addAccessoryRow(''); return false;">
                                            <i class="bi bi-plus me-1"></i>Add Custom Row (+)
                                        </button>
                                    </div>
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
            <span><i class="bi bi-info-circle me-1"></i>Calculations update live automatically. You can add or remove vehicle columns and accessory rows dynamically.</span>
            <button type="submit" class="btn btn-primary btn-sm px-4">Update Costing</button>
        </div>
    </div>
</form>

<datalist id="brandList">
    @foreach ($brands as $b)
        <option value="{{ $b }}"></option>
    @endforeach
</datalist>

<datalist id="carProductList">
    @foreach ($products as $p)
        <option value="{{ $p['name'] }}" data-brand="{{ is_array($p['vehicle_brand_id'] ?? null) ? $p['vehicle_brand_id'][1] : '' }}" data-id="{{ $p['id'] }}">
            {{ $p['display_name'] }}
        </option>
    @endforeach
</datalist>

@endsection

@push('scripts')
<script>
    let vehicleCount = 0;
    let accessoryCount = 0;
    const bbnOptions = @json($bbnList);
    const existingVehicles = @json($costing->vehicles);
    const existingAccRows = @json($accRows);

    function formatRupiah(num) {
        if (isNaN(num) || num === null) num = 0;
        return 'Rp ' + Math.round(num).toLocaleString('id-ID');
    }

    function addVehicleColumn(data = {}) {
        const vIndex = vehicleCount;
        vehicleCount++;

        const brand = data.brand || '';
        const vType = data.vehicle_type || '';
        const bbn = data.bbn || 'JKT';
        const pemakaian = data.pemakaian || 'Jabodetabek';
        const otr = data.harga_otr || 0;
        const diskon = data.diskon || 0;
        const estimasi = data.estimasi_kenaikan || 0;

        const th = document.createElement('th');
        th.className = 'costing-header-col text-center py-2';
        th.id = `colHeader_${vIndex}`;
        th.innerHTML = `
            <div class="d-flex align-items-center justify-content-between bg-white p-2 rounded border shadow-sm">
                <span class="fw-bold text-primary small"><i class="bi bi-car-front me-1"></i>Vehicle #${vIndex + 1}</span>
                <button type="button" class="btn btn-xs btn-outline-danger py-0 px-2 remove-col-btn" title="Remove Vehicle (-)" onclick="removeVehicleColumn(${vIndex})">
                    <i class="bi bi-dash-lg"></i>
                </button>
            </div>
        `;
        document.getElementById('vehicleHeaderRow').appendChild(th);

        const tdBrand = document.createElement('td');
        tdBrand.id = `cellBrand_${vIndex}`;
        tdBrand.innerHTML = `<input type="text" name="vehicles[${vIndex}][brand]" list="brandList" class="form-control form-control-sm" placeholder="e.g. Chery..." value="${brand}">`;
        document.getElementById('rowBrand').appendChild(tdBrand);

        const tdType = document.createElement('td');
        tdType.id = `cellType_${vIndex}`;
        tdType.innerHTML = `<input type="text" name="vehicles[${vIndex}][vehicle_type]" list="carProductList" class="form-control form-control-sm" placeholder="e.g. E5..." value="${vType}">`;
        document.getElementById('rowType').appendChild(tdType);

        const tdBbn = document.createElement('td');
        tdBbn.id = `cellBbn_${vIndex}`;
        let bbnOptsHtml = `<option value="">-- Select BBN --</option>`;
        bbnOptions.forEach(b => {
            const isSel = (b.kode === bbn || b.display_name === bbn) ? 'selected' : '';
            bbnOptsHtml += `<option value="${b.kode}" ${isSel}>${b.display_name}</option>`;
        });
        tdBbn.innerHTML = `<select name="vehicles[${vIndex}][bbn]" class="form-select form-select-sm">${bbnOptsHtml}</select>`;
        document.getElementById('rowBbn').appendChild(tdBbn);

        const tdPemakaian = document.createElement('td');
        tdPemakaian.id = `cellPemakaian_${vIndex}`;
        tdPemakaian.innerHTML = `<input type="text" name="vehicles[${vIndex}][pemakaian]" class="form-control form-control-sm" placeholder="e.g. Jabodetabek" value="${pemakaian}">`;
        document.getElementById('rowPemakaian').appendChild(tdPemakaian);

        const tdOtr = document.createElement('td');
        tdOtr.id = `cellOtr_${vIndex}`;
        tdOtr.innerHTML = `
            <input type="number" step="any" name="vehicles[${vIndex}][harga_otr]" class="form-control form-control-sm num-input"
                id="inputOtr_${vIndex}" value="${otr}" placeholder="0" oninput="calculateColumn(${vIndex})">
            <div class="formatted-display" id="dispOtr_${vIndex}"></div>
        `;
        document.getElementById('rowOtr').appendChild(tdOtr);

        const tdDiskon = document.createElement('td');
        tdDiskon.id = `cellDiskon_${vIndex}`;
        tdDiskon.innerHTML = `
            <input type="number" step="any" name="vehicles[${vIndex}][diskon]" class="form-control form-control-sm num-input"
                id="inputDiskon_${vIndex}" value="${diskon}" placeholder="0" oninput="calculateColumn(${vIndex})">
            <div class="formatted-display" id="dispDiskon_${vIndex}"></div>
        `;
        document.getElementById('rowDiskon').appendChild(tdDiskon);

        const tdNett = document.createElement('td');
        tdNett.id = `cellNett_${vIndex}`;
        tdNett.className = 'fw-bold text-success';
        tdNett.innerHTML = `<span id="valNett_${vIndex}">Rp 0</span>`;
        document.getElementById('rowNett').appendChild(tdNett);

        const tdEstimasi = document.createElement('td');
        tdEstimasi.id = `cellEstimasi_${vIndex}`;
        tdEstimasi.innerHTML = `
            <input type="number" step="any" name="vehicles[${vIndex}][estimasi_kenaikan]" class="form-control form-control-sm num-input"
                id="inputEstimasi_${vIndex}" value="${estimasi}" placeholder="0" oninput="calculateColumn(${vIndex})">
            <div class="formatted-display" id="dispEstimasi_${vIndex}"></div>
        `;
        document.getElementById('rowEstimasi').appendChild(tdEstimasi);

        const tdAfterInc = document.createElement('td');
        tdAfterInc.id = `cellAfterInc_${vIndex}`;
        tdAfterInc.className = 'fw-bold';
        tdAfterInc.innerHTML = `<span id="valAfterInc_${vIndex}">Rp 0</span>`;
        document.getElementById('rowAfterInc').appendChild(tdAfterInc);

        document.querySelectorAll('tr[id^="accRow_"]').forEach(accRow => {
            const aIdx = accRow.getAttribute('data-acc-index');
            const tdAcc = document.createElement('td');
            tdAcc.id = `cellAcc_${aIdx}_${vIndex}`;
            const defAmt = accRow.getAttribute('data-default-amount') || 0;
            tdAcc.innerHTML = `
                <input type="number" step="any" name="accessories[${aIdx}][amounts][${vIndex}]" class="form-control form-control-sm num-input"
                    id="inputAcc_${aIdx}_${vIndex}" value="${defAmt}" placeholder="0" oninput="calculateColumn(${vIndex})">
                <div class="formatted-display" id="dispAcc_${aIdx}_${vIndex}"></div>
            `;
            accRow.appendChild(tdAcc);
        });

        const tdTotalAcc = document.createElement('td');
        tdTotalAcc.id = `cellTotalAcc_${vIndex}`;
        tdTotalAcc.className = 'fw-bold';
        tdTotalAcc.innerHTML = `<span id="valTotalAcc_${vIndex}">Rp 0</span>`;
        document.getElementById('rowTotalAcc').appendChild(tdTotalAcc);

        const tdGrand = document.createElement('td');
        tdGrand.id = `cellGrand_${vIndex}`;
        tdGrand.className = 'fw-bold text-primary fs-6';
        tdGrand.innerHTML = `<span id="valGrand_${vIndex}">Rp 0</span>`;
        document.getElementById('rowGrandTotal').appendChild(tdGrand);

        updateRemoveColButtonsState();
        calculateColumn(vIndex);
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
            <div class="d-flex align-items-center gap-1">
                <button type="button" class="btn btn-xs btn-outline-danger p-0 px-1" title="Remove Row (-)" onclick="removeAccessoryRow(${aIndex})">
                    <i class="bi bi-dash"></i>
                </button>
                <input type="text" name="accessories[${aIndex}][name]" class="form-control form-control-sm py-1 fw-semibold"
                    placeholder="Accessory Name..." value="${name}" required style="font-size:0.8rem">
            </div>
        `;
        tr.appendChild(tdLabel);

        document.querySelectorAll('th[id^="colHeader_"]').forEach((col, colIdx) => {
            const vIndex = col.id.replace('colHeader_', '');
            const tdAcc = document.createElement('td');
            tdAcc.id = `cellAcc_${aIndex}_${vIndex}`;
            const val = (amountsPerVeh[colIdx] !== undefined) ? amountsPerVeh[colIdx] : defaultAmount;
            tdAcc.innerHTML = `
                <input type="number" step="any" name="accessories[${aIndex}][amounts][${vIndex}]" class="form-control form-control-sm num-input"
                    id="inputAcc_${aIndex}_${vIndex}" value="${val}" placeholder="0" oninput="calculateColumn(${vIndex})">
                <div class="formatted-display" id="dispAcc_${aIndex}_${vIndex}">${formatRupiah(val)}</div>
            `;
            tr.appendChild(tdAcc);
        });

        const rowTotalAcc = document.getElementById('rowTotalAcc');
        rowTotalAcc.parentNode.insertBefore(tr, rowTotalAcc);

        document.querySelectorAll('th[id^="colHeader_"]').forEach(col => {
            const vIndex = col.id.replace('colHeader_', '');
            calculateColumn(vIndex);
        });
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

        const dispOtr = document.getElementById(`dispOtr_${vIndex}`);
        if (dispOtr) dispOtr.innerText = formatRupiah(otr);

        const dispDiskon = document.getElementById(`dispDiskon_${vIndex}`);
        if (dispDiskon) dispDiskon.innerText = formatRupiah(diskon);

        const dispEstimasi = document.getElementById(`dispEstimasi_${vIndex}`);
        if (dispEstimasi) dispEstimasi.innerText = formatRupiah(estimasi);

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
            const aIdx = accInp.id.replace('inputAcc_', '').replace(`_${vIndex}`, '');
            const dispAcc = document.getElementById(`dispAcc_${aIdx}_${vIndex}`);
            if (dispAcc) dispAcc.innerText = formatRupiah(amt);
        });

        const valTotalAcc = document.getElementById(`valTotalAcc_${vIndex}`);
        if (valTotalAcc) valTotalAcc.innerText = formatRupiah(totalAcc);

        const grandTotal = afterInc + totalAcc;
        const valGrand = document.getElementById(`valGrand_${vIndex}`);
        if (valGrand) valGrand.innerText = formatRupiah(grandTotal);
    }

    // Populate existing data
    document.addEventListener('DOMContentLoaded', function() {
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
        }

        document.getElementById('addVehicleBtn').addEventListener('click', function() {
            addVehicleColumn();
        });
    });
</script>
@endpush
