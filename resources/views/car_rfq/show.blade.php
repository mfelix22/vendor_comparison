@extends('layouts.app')

@section('title', 'Car Vendor Comparison Matrix')

@section('content')

@php
    $isEdit = isset($comparison) && $comparison !== null;
    $existing = $existingComparison ?? null;
    $isRejectedOrCancelledPrefill = !$isEdit && $existing && ($existing->isRejected() || $existing->isCancelled());
    $prefillSource = $isEdit ? $comparison : ($isRejectedOrCancelledPrefill ? $existing : null);
    $canShowForm = $isEdit || !$existing || $existing->isRejected() || $existing->isCancelled();

    $poId = $isEdit ? $comparison->po_id : ($rfq['id'] ?? 0);
    $poName = $isEdit ? $comparison->po_name : ($rfq['name'] ?? '');
    $poVendor = $isEdit ? $comparison->po_vendor : (is_array($rfq['partner_id'] ?? null) ? $rfq['partner_id'][1] : '');
    
    // Initial Car Specifications (Prefills from rejected/cancelled comparison or Odoo)
    $odooCarModel = $rfq['car_line']['clean_product_name'] ?? $rfq['car_line']['product_clean_name'] ?? $rfq['car_line']['name'] ?? '';
    $defaultCarModel = $prefillSource ? ($prefillSource->car_model ?: $odooCarModel) : $odooCarModel;
    $defaultCarModel = preg_replace('/^\[[^\]]*\]\s*/', '', $defaultCarModel);
    $defaultQty = $prefillSource ? ($prefillSource->qty ?: (int)($rfq['car_line']['product_qty'] ?? 1)) : (int)($rfq['car_line']['product_qty'] ?? 1);
    $defaultUom = $prefillSource ? ($prefillSource->uom ?: (is_array($rfq['car_line']['product_uom'] ?? null) ? $rfq['car_line']['product_uom'][1] : 'unit')) : (is_array($rfq['car_line']['product_uom'] ?? null) ? $rfq['car_line']['product_uom'][1] : 'unit');
    $defaultPricelist = $prefillSource ? ($prefillSource->pricelist_ori ?: (float)($rfq['car_line']['price_unit'] ?? 0)) : (float)($rfq['car_line']['price_unit'] ?? 0);
    $defaultWarna = $prefillSource ? ($prefillSource->warna ?: ($rfq['parsed_warna'] ?? '')) : ($rfq['parsed_warna'] ?? '');
    $defaultBbn = $prefillSource ? ($prefillSource->bbn ?: ($rfq['parsed_bbn'] ?? '')) : ($rfq['parsed_bbn'] ?? '');
    $defaultVinCode = $prefillSource ? ($prefillSource->vin_code ?: ($rfq['parsed_vin_code'] ?? '')) : ($rfq['parsed_vin_code'] ?? '');
    
    $initialVendors = $prefillSource ? ($prefillSource->vendors ?? []) : [];
    if (empty($initialVendors)) {
        // Default to 3 empty dealer slots
        $initialVendors = [
            ['name' => $poVendor, 'pic' => ($rfq['vendor_contact_name'] ?? ''), 'phone' => ($rfq['vendor_phone'] ?? ''), 'unit_price' => $defaultPricelist, 'discount' => $rfq['car_line']['fix_discount'] ?? 0, 'cashback' => 0, 'total_benefit' => $rfq['car_line']['fix_discount'] ?? 0, 'availability' => 'ready', 'indent_duration' => '', 'notes' => ''],
            ['name' => '', 'pic' => '', 'phone' => '', 'unit_price' => 0, 'discount' => 0, 'cashback' => 0, 'total_benefit' => 0, 'availability' => 'ready', 'indent_duration' => '', 'notes' => ''],
            ['name' => '', 'pic' => '', 'phone' => '', 'unit_price' => 0, 'discount' => 0, 'cashback' => 0, 'total_benefit' => 0, 'availability' => 'ready', 'indent_duration' => '', 'notes' => ''],
        ];
    }
@endphp

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <a href="{{ route('car_rfq.index') }}" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i>Back to Car RFQ List
        </a>
        <h4 class="fw-bold mb-0 mt-1">
            <i class="bi bi-car-front-fill text-primary me-2"></i>
            {{ $isEdit ? 'Edit Car Vendor Comparison' : 'New Car Vendor Comparison (Unit Baru)' }}
        </h4>
        <div class="text-muted small">PO Reference: <span class="fw-semibold text-dark">{{ $poName }}</span> &middot; Odoo Vendor: <span class="fw-semibold text-dark">{{ $poVendor ?: '—' }}</span></div>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if (!$isEdit && $poId)
            <a href="{{ route('car_rfq.show', ['id' => $poId, 'refresh' => 1]) }}" class="btn btn-sm btn-outline-secondary" title="Re-fetch latest BBN, Warna, VIN & Vendors from Odoo">
                <i class="bi bi-arrow-clockwise me-1"></i>Refresh from Odoo
            </a>
        @endif
        @if ($isEdit)
            <span class="badge {{ $comparison->statusBadgeClass() }} fs-6">{{ $comparison->statusLabel() }}</span>
        @endif
    </div>
</div>

@if (!empty($error))
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $error }}
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if ($existing && !$isEdit)
    <div class="alert d-flex align-items-start gap-3 mb-4 {{ $existing->isApproved() ? 'alert-success' : ($existing->isRejected() ? 'alert-danger' : ($existing->isCancelled() ? 'alert-warning' : 'alert-info')) }}">
        <i class="bi {{ $existing->isApproved() ? 'bi-patch-check-fill' : ($existing->isRejected() ? 'bi-x-circle-fill' : ($existing->isCancelled() ? 'bi-slash-circle-fill' : 'bi-hourglass-split')) }} fs-4 flex-shrink-0"></i>
        <div>
            <strong>
                @if ($existing->isRejected())
                    Previous Car Comparison was rejected. Your previous entries have been pre-filled below so you can adjust and resubmit.
                @elseif ($existing->isCancelled())
                    Previous Car Comparison was cancelled. Your previous entries have been pre-filled below so you can adjust and submit a new one.
                @else
                    Car Comparison already submitted for this RFQ.
                @endif
            </strong>
            <span class="badge {{ $existing->statusBadgeClass() }} ms-1">{{ $existing->statusLabel() }}</span>
            @if ($existing->isRejected() && $existing->rejection_reason)
                <div class="mt-1 small fst-italic">"{{ $existing->rejection_reason }}"</div>
            @elseif ($existing->isCancelled() && $existing->cancel_reason)
                <div class="mt-1 small fst-italic">"{{ $existing->cancel_reason }}"</div>
            @endif
            <div class="mt-2">
                <a href="{{ route('car_comparisons.show', $existing) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-eye me-1"></i>View Approval Details
                </a>
            </div>
        </div>
    </div>
@endif

@if ($canShowForm)
<form id="carComparisonForm" method="POST" action="{{ $isEdit ? route('car_comparisons.update', $comparison) : route('car_comparisons.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <input type="hidden" name="po_id" value="{{ $poId }}">
    <input type="hidden" name="po_name" value="{{ $poName }}">
    <input type="hidden" name="po_vendor" value="{{ $poVendor }}">

    {{-- 1. VEHICLE SPECIFICATIONS CARD --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-primary">
                <i class="bi bi-card-checklist me-2"></i>Vehicle Specifications (Unit Baru)
            </h6>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Auto-extracted from Odoo</span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Car Model / Description <span class="text-danger">*</span></label>
                    <input type="text" name="car_model" id="carModelInput" class="form-control form-control-sm fw-semibold" value="{{ old('car_model', $defaultCarModel) }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Quantity <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <input type="number" name="qty" id="carQtyInput" class="form-control form-control-sm text-center fw-bold" value="{{ old('qty', $defaultQty) }}" min="1" required>
                        <input type="text" name="uom" id="carUomInput" class="form-control form-control-sm text-center" value="{{ old('uom', $defaultUom) }}" style="max-width:70px">
                    </div>
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Pricelist Original (Unit Price) <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Rp</span>
                        <input type="number" step="any" name="pricelist_ori" id="pricelistOriInput" class="form-control form-control-sm fw-bold text-end" value="{{ old('pricelist_ori', $defaultPricelist) }}" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Warna (Color)</label>
                    <input type="text" name="warna" id="carWarnaInput" class="form-control form-control-sm" placeholder="e.g. Putih, Hitam Metalik" value="{{ old('warna', $defaultWarna) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">BBN (Wilayah)</label>
                    <input type="text" name="bbn" id="carBbnInput" class="form-control form-control-sm" placeholder="e.g. Jakarta, Cirebon, Surabaya" value="{{ old('bbn', $defaultBbn) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Vin Code / Year</label>
                    <input type="text" name="vin_code" id="carVinInput" class="form-control form-control-sm" placeholder="e.g. 2026" value="{{ old('vin_code', $defaultVinCode) }}">
                </div>
            </div>
        </div>
    </div>

    {{-- ODOO PURCHASE HISTORY CARD (Benchmark from Previous POs) --}}
    @if (!empty($history) && !empty($rfq))
        @include('car_rfq._purchase_history_card')
    @endif

    {{-- 2. DEALER COMPARISON MATRIX CARDS --}}
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="fw-bold text-primary mb-0">
            <i class="bi bi-shop me-2"></i>Dealer / Supplier Quotations (<span id="vendorCountBadge">{{ count($initialVendors) }}</span> Vendors)
        </h6>
        <div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addVendorBtn">
                <i class="bi bi-plus-circle me-1"></i>Add Dealer (Max 10)
            </button>
        </div>
    </div>

    <div class="row g-3" id="vendorCardsContainer">
        @foreach ($initialVendors as $idx => $v)
            @include('car_rfq._vendor_card', ['idx' => $idx, 'vendor' => $v, 'canDelete' => count($initialVendors) > 3])
        @endforeach
    </div>

    {{-- 3. LIVE MATRIX SUMMARY TABLE --}}
    <div class="card shadow-sm border-0 my-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="fw-bold mb-0 text-primary">
                <i class="bi bi-table me-2"></i>Side-by-Side Comparison Summary
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle text-center mb-0" id="matrixSummaryTable">
                    <thead class="table-light">
                        <tr>
                            <th class="text-start ps-3" style="min-width:200px">Comparison Parameter</th>
                            <th id="thPricelist">Pricelist Original</th>
                            {{-- Dynamic Dealer Headers populated by JS --}}
                        </tr>
                    </thead>
                    <tbody id="matrixSummaryBody">
                        {{-- Populated by JS --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 4. RECOMMENDATION & JUSTIFICATION --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="fw-bold mb-0 text-primary">
                <i class="bi bi-award me-2"></i>Recommendation & Justification
            </h6>
        </div>
        <div class="card-body">
            {{-- Live recommendation alert banner --}}
            <div id="recommendationAlert" class="mb-3" style="display:none;"></div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Recommended Dealer <span class="text-danger">*</span></label>
                    <select name="selected_vendor" id="selectedVendorSelect" class="form-select form-select-sm fw-bold text-success border-success" required>
                        <option value="">— Select Recommended Dealer —</option>
                        @foreach ($initialVendors as $v)
                            @if (!empty($v['name']))
                                <option value="{{ $v['name'] }}" {{ old('selected_vendor', $prefillSource ? $prefillSource->selected_vendor : '') === $v['name'] ? 'selected' : '' }}>
                                    {{ $v['name'] }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    <div class="form-text small">Select the dealer chosen for this purchase order.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Customer Reference / Justification Notes</label>
                    <textarea name="notes" id="notesInput" class="form-control form-control-sm" rows="3" placeholder="e.g. Requisition number, customer name, reason for dealer selection...">{{ old('notes', $prefillSource ? $prefillSource->notes : '') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. SUBMIT ACTIONS --}}
    <div class="d-flex align-items-center justify-content-between mb-5">
        <a href="{{ route('car_rfq.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-x-circle me-1"></i>Cancel
        </a>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="openCarClvpPreview()">
                <i class="bi bi-eye me-1"></i>Preview CLVP
            </button>
            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                <i class="bi bi-send-check me-1"></i>
                @if ($isEdit)
                    Update Comparison
                @elseif (Auth::user()->isSupervisor() || Auth::user()->isManager() || Auth::user()->isAdmin())
                    Submit for Manager Approval
                @else
                    Submit for Supervisor Approval
                @endif
            </button>
        </div>
    </div>
</form>
@endif

{{-- PREVIEW CLVP MODAL --}}
<div class="modal fade" id="carClvpPreviewModal" tabindex="-1" aria-labelledby="carClvpPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 1140px;">
        <div class="modal-content">
            <div class="modal-header py-2 bg-light">
                <h6 class="modal-title fw-bold" id="carClvpPreviewModalLabel">
                    <i class="bi bi-file-earmark-spreadsheet me-2 text-primary"></i>Preview Dokumen CLVP CAR
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 bg-light" id="carClvpPreviewBody">
                <p class="text-muted text-center py-4">Generating preview…</p>
            </div>
            <div class="modal-footer py-2">
                <span class="text-muted small me-auto">
                    <i class="bi bi-info-circle me-1"></i>Live preview generated from your current form inputs (unsaved draft).
                </span>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- TEMPLATE FOR NEW VENDOR CARD (Used by JS) --}}
<template id="vendorCardTemplate">
    @include('car_rfq._vendor_card', [
        'idx' => '__INDEX__',
        'vendor' => [
            'name' => '', 'pic' => '', 'phone' => '', 'unit_price' => 0, 'discount' => 0, 'cashback' => 0, 'total_benefit' => 0, 'availability' => 'ready', 'indent_duration' => '', 'notes' => ''
        ],
        'canDelete' => true
    ])
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (!document.getElementById('carComparisonForm')) return;
    const ODOO_VENDORS = @json($vendors);
    const LOCAL_VENDORS = @json($masterSuppliers ?? []);
    const ALL_VENDORS = [...ODOO_VENDORS, ...LOCAL_VENDORS];
    const vendorLookup = {};
    ALL_VENDORS.forEach(v => {
        vendorLookup[v.id] = v;
    });

    const container = document.getElementById('vendorCardsContainer');
    const addBtn = document.getElementById('addVendorBtn');
    const cardTemplate = document.getElementById('vendorCardTemplate');
    const selectedVendorSelect = document.getElementById('selectedVendorSelect');
    const qtyInput = document.getElementById('carQtyInput');
    const pricelistInput = document.getElementById('pricelistOriInput');
    const vendorCountBadge = document.getElementById('vendorCountBadge');

    // Auto-save key for localStorage
    const draftKey = "car_clvp_draft_{{ $poId }}";

    function getVendorCards() {
        return container.querySelectorAll('.vendor-card');
    }

    function initDealerOdooSelect(card) {
        const selectEl = card.querySelector('.odoo-dealer-select');
        if (!selectEl) return;
        if (selectEl.tomselect) return;

        // Populate options
        if (selectEl.options.length <= 1) {
            ALL_VENDORS.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v.id;
                opt.textContent = v.name + (v.city ? ' - ' + v.city : '') + (v.source === 'local' ? ' [Local]' : '');
                selectEl.appendChild(opt);
            });
        }

        if (typeof TomSelect !== 'undefined') {
            const ts = new TomSelect(selectEl, {
                maxOptions: 250,
                searchField: ['text'],
                placeholder: '— Cari / pilih supplier dari Odoo —',
                onChange(val) {
                    if (!val) return;
                    const v = vendorLookup[val];
                    if (!v) return;

                    const idx = card.dataset.idx;
                    // Check for duplicate in other cards
                    const isDup = Array.from(document.querySelectorAll('.dealer-name-input'))
                        .some(inp => {
                            const otherCard = inp.closest('.vendor-card');
                            return otherCard && otherCard.dataset.idx !== idx &&
                                inp.value.trim().toLowerCase() === v.name.trim().toLowerCase();
                        });
                    if (isDup) {
                        alert(`"${v.name}" sudah dipilih di dealer lain. Pilih dealer yang berbeda.`);
                        ts.clear(true);
                        return;
                    }

                    const nameInp = card.querySelector('.dealer-name-input');
                    const phoneInp = card.querySelector('.dealer-phone-input');
                    const picInp = card.querySelector('.dealer-pic-input');

                    if (nameInp) {
                        nameInp.value = v.name;
                        nameInp.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    if (phoneInp) {
                        phoneInp.value = v.contact_phone || v.phone || v.mobile || '';
                        phoneInp.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    if (picInp) {
                        picInp.value = v.contact_name || '';
                        picInp.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }
            });
        }
    }

    function updateVendorIndices() {
        const cards = getVendorCards();
        vendorCountBadge.textContent = cards.length;
        
        cards.forEach((card, idx) => {
            card.dataset.idx = idx;
            const title = card.querySelector('.vendor-card-title');
            if (title) title.textContent = `Dealer ${idx + 1}`;
            
            // Re-index all input names inside this card
            card.querySelectorAll('input, select, textarea').forEach(input => {
                if (input.name) {
                    input.name = input.name.replace(/vendors\[\d+\]/, `vendors[${idx}]`);
                }
            });

            // Delete button visibility (min 3 vendors)
            const delBtn = card.querySelector('.remove-vendor-btn');
            if (delBtn) {
                delBtn.style.display = cards.length > 3 ? '' : 'none';
            }
        });

        // Add button limit (max 10)
        addBtn.disabled = cards.length >= 10;
        
        refreshDropdown();
        recalculateAll();
    }

    addBtn.addEventListener('click', function() {
        const cards = getVendorCards();
        if (cards.length >= 10) return;

        const newIdx = cards.length;
        let html = cardTemplate.innerHTML.replace(/__INDEX__/g, newIdx);
        const temp = document.createElement('div');
        temp.innerHTML = html;
        const newCard = temp.firstElementChild;
        container.appendChild(newCard);

        attachCardEvents(newCard);
        updateVendorIndices();
    });

    function attachCardEvents(card) {
        initDealerOdooSelect(card);

        // Remove button
        const delBtn = card.querySelector('.remove-vendor-btn');
        if (delBtn) {
            delBtn.addEventListener('click', function() {
                if (getVendorCards().length > 3) {
                    const selectEl = card.querySelector('.odoo-dealer-select');
                    if (selectEl && selectEl.tomselect) selectEl.tomselect.destroy();
                    card.remove();
                    updateVendorIndices();
                }
            });
        }

        // Dealer name change
        const nameInput = card.querySelector('.dealer-name-input');
        if (nameInput) {
            nameInput.addEventListener('input', function() {
                refreshDropdown();
                recalculateAll();
            });
        }

        // Availability radio
        card.querySelectorAll('input[type="radio"][name$="[availability]"]').forEach(r => {
            r.addEventListener('change', function() {
                const indentRow = card.querySelector('.indent-duration-row');
                if (indentRow) {
                    indentRow.style.display = this.value === 'indent' ? '' : 'none';
                }
                recalculateAll();
            });
        });

        // Numerical input recalculation
        card.querySelectorAll('.calc-trigger').forEach(inp => {
            inp.addEventListener('input', recalculateAll);
        });
    }

    // Attach events to initial cards
    getVendorCards().forEach(card => attachCardEvents(card));

    qtyInput.addEventListener('input', recalculateAll);
    pricelistInput.addEventListener('input', recalculateAll);

    function refreshDropdown() {
        const currentVal = selectedVendorSelect.value;
        selectedVendorSelect.innerHTML = '<option value="">— Select Recommended Dealer —</option>';
        
        getVendorCards().forEach(card => {
            const nameInput = card.querySelector('.dealer-name-input');
            const name = nameInput ? nameInput.value.trim() : '';
            if (name) {
                const opt = document.createElement('option');
                opt.value = name;
                opt.textContent = name;
                if (name === currentVal) opt.selected = true;
                selectedVendorSelect.appendChild(opt);
            }
        });
    }

    function fmt(n) {
        return 'Rp\u00a0' + Math.round(n).toLocaleString('id-ID');
    }

    function recalculateAll() {
        const qty = parseFloat(qtyInput.value) || 1;
        const pricelist = parseFloat(pricelistInput.value) || 0;
        const cards = getVendorCards();

        const summaryData = [];

        cards.forEach((card, idx) => {
            const name = card.querySelector('.dealer-name-input')?.value.trim() || `Dealer ${idx + 1}`;
            const unitPriceInp = card.querySelector('.unit-price-input');
            const discountInp = card.querySelector('.discount-input');
            const cashbackInp = card.querySelector('.cashback-input');
            const totalBenefitInp = card.querySelector('.total-benefit-input');
            const avail = card.querySelector('input[type="radio"][name$="[availability]"]:checked')?.value || 'ready';

            let unitPrice = parseFloat(unitPriceInp?.value) || 0;
            let discount = parseFloat(discountInp?.value) || 0;
            let cashback = parseFloat(cashbackInp?.value) || 0;

            const totalBenefit = discount + cashback;
            if (totalBenefitInp) {
                totalBenefitInp.value = totalBenefit;
            }

            // Net Unit Cost = Harga OTR - Diskon (Cashback is informational only)
            const netUnitCost = Math.max(0, unitPrice - discount);
            const totalOrderCost = netUnitCost * qty;

            summaryData.push({
                idx,
                name,
                unitPrice,
                discount,
                cashback,
                totalBenefit,
                netUnitCost,
                totalOrderCost,
                avail,
            });
        });

        renderSummaryTable(pricelist, qty, summaryData);
        updateRecommendation(summaryData);
        saveDraft();
    }

    function renderSummaryTable(pricelist, qty, dealers) {
        const thead = document.querySelector('#matrixSummaryTable thead tr');
        const tbody = document.getElementById('matrixSummaryBody');

        // Rebuild dynamic header columns
        thead.innerHTML = '<th class="text-start ps-3" style="min-width:200px">Parameter</th><th style="min-width:140px">Pricelist Original</th>';
        dealers.forEach(d => {
            const th = document.createElement('th');
            th.style.minWidth = '160px';
            th.innerHTML = `<strong>${d.name}</strong><br><span class="badge ${d.avail === 'ready' ? 'bg-success' : 'bg-warning text-dark'}" style="font-size:.7rem">${d.avail.toUpperCase()}</span>`;
            thead.appendChild(th);
        });

        // Parameters rows
        const rows = [
            { label: 'Harga OTR', format: d => fmt(d.unitPrice), class: 'fw-bold' },
            { label: 'Diskon (Unit)', format: d => d.discount > 0 ? `<span class="text-success fw-bold">-${fmt(d.discount)}</span>` : '—' },
            { label: 'Cashback (Unit) <span class="text-muted fw-normal small">(Info)</span>', format: d => d.cashback > 0 ? `<span class="text-primary fw-bold">${fmt(d.cashback)}</span>` : '—' },
            { label: 'Total Benefit (Diskon + Cashback)', format: d => `<span class="badge bg-success-subtle text-success border border-success-subtle">${fmt(d.totalBenefit)}</span>` },
            { label: 'Net Price / Unit (After Diskon)', format: d => `<span class="fw-bold">${fmt(d.netUnitCost)}</span>` },
            { label: `Total Order Cost (${qty} Units)`, format: d => `<span class="fw-bold text-dark fs-6">${fmt(d.totalOrderCost)}</span>`, class: 'table-light' },
        ];

        let html = '';
        rows.forEach(r => {
            html += `<tr class="${r.class || ''}"><td class="text-start ps-3 fw-semibold">${r.label}</td><td>${r.label === 'Harga OTR' || r.label === 'Net Price / Unit (After Diskon)' ? fmt(pricelist) : '—'}</td>`;
            dealers.forEach(d => {
                html += `<td>${r.format(d)}</td>`;
            });
            html += '</tr>';
        });

        tbody.innerHTML = html;
    }

    function updateRecommendation(dealers) {
        const banner = document.getElementById('recommendationAlert');
        const valid = dealers.filter(d => d.totalOrderCost > 0);

        if (valid.length === 0) {
            banner.style.display = 'none';
            return;
        }

        // Sort by lowest Total Order Cost (highest benefit)
        const sorted = [...valid].sort((a, b) => a.totalOrderCost - b.totalOrderCost);
        const best = sorted[0];

        let html = `<div class="alert alert-success d-flex align-items-center gap-3 py-2 px-3 shadow-sm border-0">
            <i class="bi bi-trophy-fill text-warning fs-3"></i>
            <div>
                <strong>Recommended Dealer: ${best.name}</strong> 
                <span class="badge bg-success ms-1">Best Total Cost: ${fmt(best.totalOrderCost)}</span>
                <span class="badge bg-primary ms-1">Total Benefit: ${fmt(best.totalBenefit)}/unit</span>`;

        if (sorted.length > 1) {
            const diff = sorted[1].totalOrderCost - best.totalOrderCost;
            html += `<div class="small text-muted mt-1">Saves ${fmt(diff)} compared to next best dealer (${sorted[1].name}).</div>`;
        }

        html += `</div></div>`;
        banner.innerHTML = html;
        banner.style.display = '';

        // Auto-select if nothing selected yet
        if (!selectedVendorSelect.value && best.name) {
            selectedVendorSelect.value = best.name;
        }
    }

    function saveDraft() {
        // Collect form data for draft storage
        try {
            const formData = new FormData(document.getElementById('carComparisonForm'));
            const data = {};
            formData.forEach((val, key) => { data[key] = val; });
            localStorage.setItem(draftKey, JSON.stringify(data));
        } catch (e) {}
    }

    // Run initial calculation
    updateVendorIndices();

    // ── Live CLVP Preview Modal Functions ──
    window.openCarClvpPreview = function() {
        const body = document.getElementById('carClvpPreviewBody');
        if (!body) return;
        body.innerHTML = buildCarClvpPreviewHtml();
        const modal = new bootstrap.Modal(document.getElementById('carClvpPreviewModal'));
        modal.show();
    };

    function fmtJutaJs(val, prefix) {
        val = parseFloat(val) || 0;
        if (val <= 0) return '—';
        if (val >= 1000000) {
            const j = val / 1000000;
            const str = Number.isInteger(j) ? j : j.toFixed(1).replace('.', ',');
            return `${prefix} ${str} Juta`;
        }
        return `${prefix} ${Math.round(val).toLocaleString('id-ID')}`;
    }

    function buildCarClvpPreviewHtml() {
        const carModel = (document.getElementById('carModelInput')?.value || '').trim() || '—';
        const qty = document.getElementById('carQtyInput')?.value || '1';
        const uom = (document.getElementById('carUomInput')?.value || 'unit').trim();
        const bbn = (document.getElementById('carBbnInput')?.value || '').trim() || '—';
        const vin = (document.getElementById('carVinInput')?.value || '').trim() || '—';
        const warna = (document.getElementById('carWarnaInput')?.value || '').trim() || '—';
        const selectedVendor = (document.getElementById('selectedVendorSelect')?.value || '').trim();
        const notes = ((document.getElementById('notesInput') || document.getElementById('comparisonNotes'))?.value || '').trim();
        const poName = @json($poName);
        const dateStr = @json(now()->format('d/m/y'));
        const supervisorName = @json($isEdit ? $comparison->prepared_by_name : Auth::user()->name);

        const cards = document.querySelectorAll('.vendor-card');
        const vendors = [];
        cards.forEach((card, i) => {
            const name = (card.querySelector('.dealer-name-input')?.value || '').trim() || `Dealer ${i + 1}`;
            const pic = (card.querySelector('.dealer-pic-input')?.value || '').trim();
            const phone = (card.querySelector('.dealer-phone-input')?.value || '').trim();
            const unitPrice = parseFloat(card.querySelector('.unit-price-input')?.value) || 0;
            const discount = parseFloat(card.querySelector('.discount-input')?.value) || 0;
            const cashback = parseFloat(card.querySelector('.cashback-input')?.value) || 0;
            const totalBenefit = parseFloat(card.querySelector('.total-benefit-input')?.value) || (discount + cashback);
            const avail = card.querySelector('input[type="radio"][name$="[availability]"]:checked')?.value || 'ready';
            const indentDuration = (card.querySelector('input[name$="[indent_duration]"]')?.value || '').trim();
            const dealerNotes = (card.querySelector('[name$="[notes]"]')?.value || '').trim();

            vendors.push({
                name, pic, phone, unitPrice, discount, cashback, totalBenefit,
                isReady: avail === 'ready',
                isIndent: avail === 'indent',
                indentDuration,
                dealerNotes
            });
        });

        const hasFourthCol = vendors.length < 4;

        let headerThs = '';
        vendors.forEach(v => {
            const isRec = (v.name === selectedVendor);
            headerThs += `
                <th style="border:1px solid #000; width:135px; padding:6px 6px; text-align:center; vertical-align:top; font-size:10.5px; font-weight:bold; color:#000; ${isRec ? 'background:#f0fff4;' : ''}">
                    <div style="font-weight:bold; font-size:10.5px; line-height:1.25; margin-bottom:3px; word-break:break-word;">${v.name}</div>
                    ${v.pic ? `<div style="font-size:9.5px; font-weight:normal; margin-bottom:1px; color:#000;">${v.pic}</div>` : ''}
                    ${v.phone ? `<div style="font-size:9.5px; font-weight:normal; color:#000;">${v.phone}</div>` : ''}
                </th>
            `;
        });

        if (hasFourthCol) {
            headerThs += `
                <th style="border:1px solid #000; width:125px; padding:6px 6px; text-align:center; vertical-align:middle; font-size:10px; font-weight:bold; color:#000;">
                    PIC : TELP :
                </th>
            `;
        }

        // Row 1: Unit prices
        let r1Dealers = '';
        vendors.forEach(v => {
            const isRec = (v.name === selectedVendor);
            r1Dealers += `
                <td style="border:1px solid #000; padding:6px 6px; text-align:center; vertical-align:middle; font-weight:bold; color:#000; white-space:nowrap; ${isRec ? 'background:#f0fff4;' : ''}">
                    Rp. ${Math.round(v.unitPrice).toLocaleString('id-ID')}/unit
                </td>
            `;
        });
        if (hasFourthCol) r1Dealers += `<td style="border:1px solid #000;"></td>`;

        // Row 2: Blank Spacer
        let r2Dealers = '';
        vendors.forEach(v => {
            const isRec = (v.name === selectedVendor);
            r2Dealers += `<td style="border:1px solid #000; ${isRec ? 'background:#f0fff4;' : ''}"></td>`;
        });
        if (hasFourthCol) r2Dealers += `<td style="border:1px solid #000;"></td>`;

        // Row 3: BBN
        let r3Dealers = r2Dealers;

        // Row 4: VC
        let r4Dealers = r2Dealers;

        // Row 5: Warna & Discount
        let r5Dealers = '';
        vendors.forEach(v => {
            const isRec = (v.name === selectedVendor);
            r5Dealers += `
                <td style="border:1px solid #000; padding:5px 6px; text-align:center; vertical-align:middle; font-size:11px; font-weight:normal; color:#000; white-space:nowrap; ${isRec ? 'background:#f0fff4;' : ''}">
                    ${fmtJutaJs(v.discount, 'Disc.')}
                </td>
            `;
        });
        if (hasFourthCol) r5Dealers += `<td style="border:1px solid #000;"></td>`;

        // Row 6: Cashback
        let r6Dealers = '';
        vendors.forEach(v => {
            const isRec = (v.name === selectedVendor);
            r6Dealers += `
                <td style="border:1px solid #000; padding:5px 6px; text-align:center; vertical-align:middle; font-size:11px; font-weight:normal; color:#000; white-space:nowrap; ${isRec ? 'background:#f0fff4;' : ''}">
                    ${fmtJutaJs(v.cashback, 'Cashback')}
                </td>
            `;
        });
        if (hasFourthCol) r6Dealers += `<td style="border:1px solid #000;"></td>`;

        // Row 7: Total Disc + Cashback
        let r7Dealers = '';
        vendors.forEach(v => {
            const isRec = (v.name === selectedVendor);
            r7Dealers += `
                <td style="border:1px solid #000; padding:5px 6px; text-align:center; vertical-align:middle; font-size:10.5px; font-weight:bold; color:#000; white-space:normal; line-height:1.25; ${isRec ? 'background:#f0fff4;' : ''}">
                    ${fmtJutaJs(v.totalBenefit, 'Total Disc+Cashback')}
                </td>
            `;
        });
        if (hasFourthCol) r7Dealers += `<td style="border:1px solid #000;"></td>`;

        // Row 8: TOTAL ( (Harga OTR - Diskon) * Qty; Cashback is info only )
        const qtyNum = parseFloat(qty) || 1;
        let rTotalDealers = '';
        vendors.forEach(v => {
            const isRec = (v.name === selectedVendor);
            const netUnit = v.unitPrice > 0 ? Math.max(0, v.unitPrice - v.discount) : 0;
            const netTotal = netUnit * qtyNum;
            rTotalDealers += `
                <td style="border:1px solid #000; padding:5px 6px; text-align:center; vertical-align:middle; font-size:11px; font-weight:bold; color:#000; white-space:nowrap; ${isRec ? 'background:#f0fff4;' : ''}">
                    ${netTotal > 0 ? 'Rp. ' + Math.round(netTotal).toLocaleString('id-ID') : '—'}
                </td>
            `;
        });
        if (hasFourthCol) rTotalDealers += `<td style="border:1px solid #000;"></td>`;

        // Row Notes / Bonus Accessories per dealer
        const hasAnyDealerNotes = vendors.some(v => v.dealerNotes);
        let rNotesDealers = '';
        vendors.forEach(v => {
            const isRec = (v.name === selectedVendor);
            const safeDealerNote = (v.dealerNotes || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\r?\n/g, '<br>');
            rNotesDealers += `
                <td style="border:1px solid #000; padding:4px 6px; text-align:center; vertical-align:middle; font-size:9.5px; color:#000; white-space:normal; word-break:break-word; line-height:1.35; ${isRec ? 'background:#f0fff4;' : ''}">
                    ${safeDealerNote}
                </td>
            `;
        });
        if (hasFourthCol) rNotesDealers += `<td style="border:1px solid #000;"></td>`;

        // Row 9: Ready
        let r9Dealers = '';
        vendors.forEach(v => {
            const isRec = (v.name === selectedVendor);
            r9Dealers += `
                <td style="border:1px solid #000; padding:4px 10px; vertical-align:middle; ${isRec ? 'background:#f0fff4;' : ''}">
                    <div style="display:inline-flex; align-items:center; gap:6px;">
                        <span style="display:inline-block; width:13px; height:13px; border:1.5px solid #000; text-align:center; line-height:11px; font-size:10px; font-weight:bold; flex-shrink:0;">
                            ${v.isReady ? 'V' : ''}
                        </span>
                        <span style="font-size:10.5px; font-weight:normal; color:#000;">Ready</span>
                    </div>
                </td>
            `;
        });
        if (hasFourthCol) r9Dealers += `<td style="border:1px solid #000;"></td>`;

        // Row 10: Indent / Kosong
        let r10Dealers = '';
        vendors.forEach(v => {
            const isRec = (v.name === selectedVendor);
            r10Dealers += `
                <td style="border:1px solid #000; padding:4px 10px; vertical-align:middle; ${isRec ? 'background:#f0fff4;' : ''}">
                    <div style="display:inline-flex; align-items:center; gap:6px; flex-wrap:nowrap;">
                        <span style="display:inline-block; width:13px; height:13px; border:1.5px solid #000; text-align:center; line-height:11px; font-size:10px; font-weight:bold; flex-shrink:0;">
                            ${v.isIndent ? 'V' : ''}
                        </span>
                        <span style="font-size:10.5px; font-weight:normal; color:#000;">Indent / Kosong</span>
                        ${v.isIndent && v.indentDuration ? `<span style="font-size:9px; color:#555; margin-left:2px;">(${v.indentDuration})</span>` : ''}
                    </div>
                </td>
            `;
        });
        if (hasFourthCol) r10Dealers += `<td style="border:1px solid #000;"></td>`;

        // Shared 1-box empty spacer row (5 fixed cols + vendor cols)
        const blankSpacerRow = `
            <tr style="height:26px;">
                <td style="border:1px solid #000; padding:4px 4px;">&nbsp;</td>
                <td style="border:1px solid #000;"></td>
                <td style="border:1px solid #000;"></td>
                <td style="border:1px solid #000;"></td>
                <td style="border:1px solid #000;"></td>
                ${r2Dealers}
            </tr>
        `;

        return `
            <div class="bg-white border p-4 shadow-sm mx-auto" style="font-family: Arial, sans-serif; font-size:11px; color:#000; max-width:1080px;">
                <div class="text-center mb-2">
                    <div style="font-size:14px; font-weight:bold; letter-spacing:0.5px;">
                        COMPARISON LOCAL VENDOR PRICE CAR ( CLVP CAR )
                    </div>
                </div>

                <div class="d-flex align-items-center mb-3 gap-4">
                    <div style="min-width:140px;">
                        <img src="/logo.png" style="height:46px; max-width:140px; object-fit:contain;" alt="HARENT">
                    </div>
                    <div class="d-flex gap-4 align-items-center flex-wrap" style="font-size:11px;">
                        <span style="display:inline-flex; align-items:center; gap:4px;">
                            <span style="display:inline-block; width:13px; height:13px; border:1.5px solid #000; text-align:center; line-height:11px; font-size:10px; font-weight:bold;">V</span>
                            Unit Baru
                        </span>
                        <span style="display:inline-flex; align-items:center; gap:4px;">
                            <span style="display:inline-block; width:13px; height:13px; border:1.5px solid #000; text-align:center; line-height:11px; font-size:10px; font-weight:bold;">&nbsp;</span>
                            Aksesoris Mobil
                        </span>
                        <span style="display:inline-flex; align-items:center; gap:4px;">
                            <span style="display:inline-block; width:13px; height:13px; border:1.5px solid #000; text-align:center; line-height:11px; font-size:10px; font-weight:bold;">&nbsp;</span>
                            Sparepart
                        </span>
                        <span style="display:inline-flex; align-items:center; gap:4px;">
                            <span style="display:inline-block; width:13px; height:13px; border:1.5px solid #000; text-align:center; line-height:11px; font-size:10px; font-weight:bold;">&nbsp;</span>
                            Umum
                        </span>
                    </div>
                </div>

                <table style="width:100%; border-collapse:collapse; border:1px solid #000; font-size:11px; color:#000;">
                    <thead>
                        <tr>
                            <th rowspan="2" style="border:1px solid #000; width:40px; text-align:center; vertical-align:middle; font-weight:bold; color:#000;">No</th>
                            <th rowspan="2" style="border:1px solid #000; width:260px; text-align:center; vertical-align:middle; font-weight:bold; color:#000;">Nama Barang</th>
                            <th rowspan="2" style="border:1px solid #000; width:70px; text-align:center; vertical-align:middle; font-weight:bold; color:#000;">Kode<br>Barang</th>
                            <th rowspan="2" style="border:1px solid #000; width:45px; text-align:center; vertical-align:middle; font-weight:bold; color:#000;">QTY</th>
                            <th rowspan="2" style="border:1px solid #000; width:45px; text-align:center; vertical-align:middle; font-weight:bold; color:#000;">UoM</th>
                            <th colspan="${vendors.length + (hasFourthCol ? 1 : 0)}" style="border:1px solid #000; text-align:center; vertical-align:middle; padding:5px 0; font-weight:bold; color:#000;">
                                Supplier / Vendor
                            </th>
                        </tr>
                        <tr>
                            ${headerThs}
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Row 1: Vehicle Item Details + Unit Prices -->
                        <tr style="height:34px;">
                            <td style="border:1px solid #000; padding:6px 4px; text-align:center; vertical-align:middle; font-weight:bold;">1</td>
                            <td style="border:1px solid #000; padding:6px 10px; vertical-align:middle; font-weight:bold; font-size:11px; word-break:break-word;">${carModel}</td>
                            <td style="border:1px solid #000; padding:6px 4px; text-align:center; vertical-align:middle; color:#888; font-size:10px;">—</td>
                            <td style="border:1px solid #000; padding:6px 4px; text-align:center; vertical-align:middle; font-weight:bold;">${qty}</td>
                            <td style="border:1px solid #000; padding:6px 4px; text-align:center; vertical-align:middle;">${uom}</td>
                            ${r1Dealers}
                        </tr>

                        <!-- Row 2: BLANK SPACER ROW -->
                        ${blankSpacerRow}

                        <!-- Row 3: BBN -->
                        <tr style="height:28px;">
                            <td style="border:1px solid #000; padding:5px 4px; text-align:center; vertical-align:middle;"></td>
                            <td style="border:1px solid #000; padding:5px 10px; vertical-align:middle; font-size:11px;">BBN : ${bbn}</td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            ${r3Dealers}
                        </tr>

                        <!-- Row 4: VC -->
                        <tr style="height:28px;">
                            <td style="border:1px solid #000; padding:5px 4px; text-align:center; vertical-align:middle;"></td>
                            <td style="border:1px solid #000; padding:5px 10px; vertical-align:middle; font-size:11px;">VC : ${vin}</td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            ${r4Dealers}
                        </tr>

                        <!-- Row 5: Warna & Discount -->
                        <tr style="height:28px;">
                            <td style="border:1px solid #000; padding:5px 4px; text-align:center; vertical-align:middle;"></td>
                            <td style="border:1px solid #000; padding:5px 10px; vertical-align:middle; font-size:11px;">Warna : ${warna}</td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            ${r5Dealers}
                        </tr>

                        <!-- Row 6: Cashback -->
                        <tr style="height:28px;">
                            <td style="border:1px solid #000; padding:5px 4px; text-align:center; vertical-align:middle;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            ${r6Dealers}
                        </tr>

                        <!-- Row 7: Total Disc + Cashback -->
                        <tr style="height:28px;">
                            <td style="border:1px solid #000; padding:5px 4px; text-align:center; vertical-align:middle;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            ${r7Dealers}
                        </tr>

                        <!-- Spacer (1 box) between Total Disc+Cashback and TOTAL -->
                        ${blankSpacerRow}

                        <!-- Row 8: TOTAL (Net Price * Qty) -->
                        <tr style="height:28px; font-weight:bold; background:#f9f9f9;">
                            <td colspan="5" style="border:1px solid #000; padding:5px 8px; text-align:right; vertical-align:middle; font-weight:bold; color:#000;">TOTAL</td>
                            ${rTotalDealers}
                        </tr>

                        <!-- Spacer (1 box) between TOTAL and Bonus / Notes (or Ready) -->
                        ${blankSpacerRow}

                        ${hasAnyDealerNotes ? `
                        <tr style="height:26px;">
                            <td style="border:1px solid #000; padding:4px 4px;"></td>
                            <td style="border:1px solid #000; padding:4px 10px; font-size:10px; font-style:italic; color:#333; vertical-align:middle;">Bonus / Notes :</td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            ${rNotesDealers}
                        </tr>
                        ${blankSpacerRow}` : ''}

                        <!-- Row 9: Ready -->
                        <tr style="height:28px;">
                            <td style="border:1px solid #000; padding:4px 4px;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            ${r9Dealers}
                        </tr>

                        <!-- Row 10: Indent / Kosong -->
                        <tr style="height:28px;">
                            <td style="border:1px solid #000; padding:4px 4px;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            ${r10Dealers}
                        </tr>
                    </tbody>
                </table>

                <div class="mt-3 d-flex justify-content-between align-items-start" style="font-size:11px;">
                    <div>
                        <div class="fw-bold">No. CLVP : (Draft Preview)</div>
                        <div class="text-muted" style="margin-top:2px;">Ref PO: ${poName}</div>
                        ${notes ? `<div class="text-muted mt-2" style="max-width:580px;">${notes}</div>` : ''}
                    </div>
                    <div style="text-align:right;">
                        <div>Tgl ${dateStr}</div>
                        <div>Dibuat oleh,</div>
                        <div style="height:48px;"></div>
                        <div>(${supervisorName})</div>
                        <div style="font-size:8px; color:#666; margin-top:4px;">SDP/FR/PCH/12, Rev.02</div>
                    </div>
                </div>
            </div>
        `;
    }
});
</script>

@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
@endpush

