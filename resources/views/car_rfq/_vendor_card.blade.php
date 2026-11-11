<div class="col-md-4 vendor-card" data-idx="{{ $idx }}">
    <div class="card h-100 shadow-sm border-0">
        <div class="card-header bg-light py-2 px-3 d-flex align-items-center justify-content-between">
            <span class="fw-bold text-dark vendor-card-title">Dealer {{ is_numeric($idx) ? (int)$idx + 1 : 1 }}</span>
            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 remove-vendor-btn" title="Remove Dealer" style="{{ ($canDelete ?? false) ? '' : 'display:none;' }}">
                <i class="bi bi-trash"></i>
            </button>
        </div>
        <div class="card-body p-3">
            {{-- Search Supplier in Odoo for Auto-fill --}}
            <div class="mb-2">
                <label class="form-label small fw-semibold mb-1 text-primary">
                    <i class="bi bi-search me-1"></i>Cari Supplier Odoo <span class="text-muted fw-normal">(auto-isi)</span>
                </label>
                <select class="form-select form-select-sm odoo-dealer-select">
                    <option value="">— Cari / pilih supplier dari Odoo —</option>
                </select>
            </div>

            {{-- Dealer Name --}}
            <div class="mb-2">
                <label class="form-label small fw-semibold mb-1">Dealer / Supplier Name <span class="text-danger">*</span></label>
                <input type="text" name="vendors[{{ $idx }}][name]" class="form-control form-control-sm fw-semibold dealer-name-input" placeholder="Nama dealer / supplier..." value="{{ $vendor['name'] ?? '' }}" required>
            </div>

            {{-- PIC & Phone --}}
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="form-label small mb-1 text-muted">PIC Name</label>
                    <input type="text" name="vendors[{{ $idx }}][pic]" class="form-control form-control-sm dealer-pic-input" placeholder="e.g. Pa Stanley" value="{{ $vendor['pic'] ?? '' }}">
                </div>
                <div class="col-6">
                    <label class="form-label small mb-1 text-muted">Phone / WhatsApp</label>
                    <input type="text" name="vendors[{{ $idx }}][phone]" class="form-control form-control-sm dealer-phone-input" placeholder="e.g. 0812-..." value="{{ $vendor['phone'] ?? '' }}">
                </div>
            </div>

            <hr class="my-2 text-muted">

            {{-- Pricing, Discount & Cashback Section --}}
            <div class="mb-2">
                <label class="form-label small fw-semibold mb-1">Harga OTR</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">Rp</span>
                    <input type="number" step="any" name="vendors[{{ $idx }}][unit_price]" class="form-control form-control-sm text-end fw-bold unit-price-input calc-trigger" value="{{ $vendor['unit_price'] ?? 0 }}">
                </div>
            </div>

            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="form-label small fw-semibold mb-1 text-success">Diskon (Unit)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Rp</span>
                        <input type="number" step="any" name="vendors[{{ $idx }}][discount]" class="form-control form-control-sm text-end discount-input calc-trigger" placeholder="0" value="{{ $vendor['discount'] ?? 0 }}">
                    </div>
                </div>
                <div class="col-6">
                    <label class="form-label small fw-semibold mb-1 text-primary">Cashback (Unit)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Rp</span>
                        <input type="number" step="any" name="vendors[{{ $idx }}][cashback]" class="form-control form-control-sm text-end cashback-input calc-trigger" placeholder="0" value="{{ $vendor['cashback'] ?? 0 }}">
                    </div>
                </div>
            </div>

            <div class="mb-2">
                <label class="form-label small fw-semibold mb-1 text-dark">Total Benefit (Diskon + Cashback)</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-success-subtle text-success">Rp</span>
                    <input type="number" step="any" name="vendors[{{ $idx }}][total_benefit]" class="form-control form-control-sm text-end fw-bold total-benefit-input bg-light" readonly value="{{ $vendor['total_benefit'] ?? 0 }}">
                </div>
            </div>

            <hr class="my-2 text-muted">

            {{-- Availability (Ready vs Indent) --}}
            <div class="mb-2">
                <label class="form-label small fw-semibold mb-1">Availability</label>
                <div class="d-flex gap-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="vendors[{{ $idx }}][availability]" id="availReady_{{ $idx }}" value="ready" {{ ($vendor['availability'] ?? 'ready') === 'ready' ? 'checked' : '' }}>
                        <label class="form-check-label small fw-semibold text-success" for="availReady_{{ $idx }}">
                            <i class="bi bi-check-circle me-1"></i>Ready
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="vendors[{{ $idx }}][availability]" id="availIndent_{{ $idx }}" value="indent" {{ ($vendor['availability'] ?? '') === 'indent' ? 'checked' : '' }}>
                        <label class="form-check-label small fw-semibold text-warning" for="availIndent_{{ $idx }}">
                            <i class="bi bi-clock-history me-1"></i>Indent
                        </label>
                    </div>
                </div>
            </div>

            <div class="mb-2 indent-duration-row" style="{{ ($vendor['availability'] ?? 'ready') === 'indent' ? '' : 'display:none;' }}">
                <label class="form-label small mb-1 text-muted">Indent Duration</label>
                <input type="text" name="vendors[{{ $idx }}][indent_duration]" class="form-control form-control-sm" placeholder="e.g. 2 Minggu, Ready 18 Des" value="{{ $vendor['indent_duration'] ?? '' }}">
            </div>

            {{-- Dealer Notes --}}
            <div class="mb-0">
                <label class="form-label small mb-1 text-muted">Notes / Bonus Accessories</label>
                <textarea name="vendors[{{ $idx }}][notes]" rows="2" class="form-control form-control-sm dealer-notes-input" placeholder="e.g. Free Kaca&#10;Free Karpet, Apar...">{{ $vendor['notes'] ?? '' }}</textarea>
            </div>
        </div>
    </div>
</div>

