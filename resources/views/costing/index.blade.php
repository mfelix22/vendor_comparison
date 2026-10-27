@extends('layouts.app')

@section('title', 'Costing Portal')

@section('content')

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-calculator me-2 text-primary"></i>Costing Portal</h4>
            <p class="text-muted small mb-0">Create, compare, and estimate vehicle prices side-by-side with accessories.</p>
        </div>
        <div>
            <a href="{{ route('costing.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm">
                <i class="bi bi-plus-lg me-1"></i>New Costing Sheet
            </a>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('costing.index') }}" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0"
                            placeholder="Search by Costing No, Customer / Instansi..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-secondary">Search</button>
                    @if (request('search'))
                        <a href="{{ route('costing.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Costings Table --}}
    @if ($costings->isEmpty())
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="bi bi-file-earmark-spreadsheet display-4 text-muted mb-3 d-block"></i>
                <h5 class="fw-semibold text-secondary">No Costing Sheets Found</h5>
                <p class="text-muted small mb-3">Get started by creating your first vehicle costing comparison sheet.</p>
                <a href="{{ route('costing.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Create First Costing
                </a>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="costingTable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 py-3">Costing No</th>
                                <th>Customer / Instansi</th>
                                <th class="text-center">FPH Type</th>
                                <th class="text-center">Vehicles Compared</th>
                                <th class="text-end">Price Range (Unit + Acc)</th>
                                <th>Created By</th>
                                <th>Created Date</th>
                                <th class="text-center pe-3" style="width: 110px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($costings as $costing)
                                @php
                                    $minPrice = $costing->vehicles->min('grand_total') ?? 0;
                                    $maxPrice = $costing->vehicles->max('grand_total') ?? 0;
                                @endphp
                                <tr data-href="{{ route('costing.show', $costing) }}" style="cursor: pointer;">
                                    <td class="ps-3 fw-bold text-primary">
                                        <a href="{{ route('costing.show', $costing) }}" class="text-decoration-none">
                                            {{ $costing->costing_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $costing->customer_name }}</div>
                                        @if ($costing->notes)
                                            <div class="text-muted small text-truncate" style="max-width: 320px;">
                                                {{ $costing->notes }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($costing->isDraft())
                                            <div>
                                                <span class="badge bg-secondary text-white border px-2 py-1">
                                                    <i class="bi bi-file-earmark me-1"></i>Draft
                                                </span>
                                            </div>
                                        @elseif ($costing->isTender())
                                            <div>
                                                <span class="badge bg-warning text-dark border border-warning-subtle px-2 py-1">
                                                    <i class="bi bi-award me-1"></i>Tender
                                                </span>
                                            </div>
                                            <div class="mt-1">
                                                @if ($costing->isPendingApproval())
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 0.72rem;">
                                                        <i class="bi bi-clock me-1"></i>Pending
                                                    </span>
                                                @elseif ($costing->isApproved())
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.72rem;">
                                                        <i class="bi bi-check-circle me-1"></i>Approved
                                                    </span>
                                                @elseif ($costing->isRejected())
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.72rem;" title="{{ $costing->rejection_reason }}">
                                                        <i class="bi bi-x-circle me-1"></i>Rejected
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="badge bg-light text-secondary border px-2 py-1">
                                                <i class="bi bi-shop me-1"></i>Retail
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-car-front me-1"></i>{{ $costing->vehicles->count() }} unit(s)
                                        </span>
                                    </td>
                                    <td class="text-end fw-semibold">
                                        @if ($minPrice === $maxPrice)
                                            Rp {{ number_format($minPrice, 0, ',', '.') }}
                                        @else
                                            <div class="small text-muted">Rp {{ number_format($minPrice, 0, ',', '.') }}</div>
                                            <div class="text-dark">Rp {{ number_format($maxPrice, 0, ',', '.') }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="small">{{ $costing->creator->name ?? '—' }}</span>
                                    </td>
                                    <td class="text-muted small">
                                        {{ $costing->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="text-center pe-3">
                                        <div class="btn-group btn-group-sm">
                                            {{-- Excel Export Button (Locked if Pending or Rejected) --}}
                                            @if (!$costing->isPendingApproval() && !$costing->isRejected())
                                                <a href="{{ route('costing.export', $costing) }}" class="btn btn-outline-success" title="Download Excel (.xlsx)">
                                                    <i class="bi bi-file-earmark-excel"></i>
                                                </a>
                                            @else
                                                <button type="button" class="btn btn-outline-secondary text-muted" disabled 
                                                    title="{{ $costing->isPendingApproval() ? 'Cannot download Excel while awaiting Manager approval' : 'Cannot download rejected costing' }}" 
                                                    style="cursor: not-allowed; opacity: 0.55;">
                                                    <i class="bi bi-file-earmark-excel"></i>
                                                </button>
                                            @endif

                                            {{-- Edit Button (Draft or Retail is always editable; Tender is locked when Pending or Approved, unlocked when Rejected) --}}
                                            @php
                                                $canEdit = $costing->isDraft() || !$costing->isTender() || $costing->isRejected();
                                                $lockReason = 'Edit';
                                                if ($costing->isPendingApproval()) {
                                                    $lockReason = 'Awaiting Manager Approval — Editing is locked';
                                                } elseif ($costing->isTender() && $costing->isApproved()) {
                                                    $lockReason = 'Tender FPH Approved — Editing is locked';
                                                }
                                            @endphp
                                            @if ($canEdit)
                                                <a href="{{ route('costing.edit', $costing) }}" class="btn btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @else
                                                <button type="button" class="btn btn-outline-secondary text-muted" disabled title="{{ $lockReason }}" style="cursor: not-allowed; opacity: 0.55;">
                                                    <i class="bi bi-lock-fill"></i>
                                                </button>
                                            @endif
                                            @if (Auth::user()?->isAdmin())
                                                <form method="POST" action="{{ route('costing.destroy', $costing) }}" class="d-inline"
                                                    onsubmit="return confirm('Delete Costing {{ $costing->costing_number }}?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($costings->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $costings->links() }}
                </div>
            @endif
        </div>
    @endif

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const costingTableBody = document.querySelector('#costingTable tbody');
        if (costingTableBody) {
            costingTableBody.addEventListener('click', function(e) {
                // Ignore clicks on explicit links, buttons, or form controls
                if (e.target.closest('a, button, input, select, textarea, form')) {
                    return;
                }
                // Ignore if user is selecting text (e.g., copying costing number)
                const selection = window.getSelection();
                if (selection && selection.toString().trim().length > 0) {
                    return;
                }
                const row = e.target.closest('tr');
                if (!row || !costingTableBody.contains(row)) return;

                if (row.dataset.href) {
                    if (e.ctrlKey || e.metaKey) {
                        window.open(row.dataset.href, '_blank');
                    } else {
                        window.location.href = row.dataset.href;
                    }
                }
            });

            costingTableBody.addEventListener('auxclick', function(e) {
                if (e.button === 1) { // Middle click
                    if (e.target.closest('a, button, input, select, textarea, form')) {
                        return;
                    }
                    const row = e.target.closest('tr');
                    if (!row || !costingTableBody.contains(row)) return;

                    if (row.dataset.href) {
                        window.open(row.dataset.href, '_blank');
                    }
                }
            });
        }
    });
</script>
@endpush
