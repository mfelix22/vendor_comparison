@extends('layouts.app')

@section('title', 'Tender Costing Approvals')

@section('content')

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-check2-square me-2 text-primary"></i>Tender Costing Approvals
            </h4>
            <p class="text-muted small mb-0">Review, approve, or reject vehicle costing sheets marked as Tender FPH.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('costing.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-table me-1"></i>Costing List
            </a>
            <a href="{{ route('costing.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm">
                <i class="bi bi-plus-lg me-1"></i>New Costing Sheet
            </a>
        </div>
    </div>

    {{-- Filter Tabs & Search --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-white border-bottom p-0">
            <ul class="nav nav-tabs card-header-tabs mx-3 mt-2 border-0">
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'pending' ? 'active fw-bold text-warning border-bottom border-warning border-3' : 'text-muted' }}"
                        href="{{ route('costing.approvals', ['status' => 'pending', 'search' => request('search')]) }}">
                        <i class="bi bi-clock-history me-1"></i>Pending Review
                        <span class="badge {{ $status === 'pending' ? 'bg-warning text-dark' : 'bg-light text-secondary border' }} rounded-pill ms-1">
                            {{ $counts['pending'] }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'approved' ? 'active fw-bold text-success border-bottom border-success border-3' : 'text-muted' }}"
                        href="{{ route('costing.approvals', ['status' => 'approved', 'search' => request('search')]) }}">
                        <i class="bi bi-check-circle me-1"></i>Approved
                        <span class="badge {{ $status === 'approved' ? 'bg-success text-white' : 'bg-light text-secondary border' }} rounded-pill ms-1">
                            {{ $counts['approved'] }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'rejected' ? 'active fw-bold text-danger border-bottom border-danger border-3' : 'text-muted' }}"
                        href="{{ route('costing.approvals', ['status' => 'rejected', 'search' => request('search')]) }}">
                        <i class="bi bi-x-circle me-1"></i>Rejected
                        <span class="badge {{ $status === 'rejected' ? 'bg-danger text-white' : 'bg-light text-secondary border' }} rounded-pill ms-1">
                            {{ $counts['rejected'] }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'all' ? 'active fw-bold text-primary border-bottom border-primary border-3' : 'text-muted' }}"
                        href="{{ route('costing.approvals', ['status' => 'all', 'search' => request('search')]) }}">
                        <i class="bi bi-collection me-1"></i>All Tender Costings
                        <span class="badge {{ $status === 'all' ? 'bg-primary text-white' : 'bg-light text-secondary border' }} rounded-pill ms-1">
                            {{ $counts['all'] }}
                        </span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body p-3">
            <form method="GET" action="{{ route('costing.approvals') }}" class="row g-2 align-items-center">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0"
                            placeholder="Search by Costing No or Customer / Project..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-secondary">Search</button>
                    @if (request('search'))
                        <a href="{{ route('costing.approvals', ['status' => $status]) }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Approvals Table --}}
    @if ($costings->isEmpty())
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="bi bi-check2-circle display-4 text-muted mb-3 d-block"></i>
                <h5 class="fw-semibold text-secondary">No Tender Costings Found</h5>
                <p class="text-muted small mb-0">
                    @if ($status === 'pending')
                        No tender costings currently awaiting manager approval.
                    @elseif ($status === 'approved')
                        No approved tender costings yet.
                    @elseif ($status === 'rejected')
                        No rejected tender costings.
                    @else
                        No tender costings recorded yet.
                    @endif
                </p>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 py-3">Costing No</th>
                                <th>Customer / Project</th>
                                <th class="text-center">Vehicles</th>
                                <th class="text-end">Price Range</th>
                                <th>Submitted By</th>
                                <th>Status</th>
                                <th class="text-center pe-3" style="width: 220px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($costings as $costing)
                                @php
                                    $minPrice = $costing->vehicles->min('grand_total') ?? 0;
                                    $maxPrice = $costing->vehicles->max('grand_total') ?? 0;
                                    $isMgrOrAdmin = Auth::check() && (Auth::user()->isManager() || Auth::user()->isAdmin());
                                @endphp
                                <tr>
                                    <td class="ps-3 fw-bold text-primary">
                                        <a href="{{ route('costing.show', $costing) }}" class="text-decoration-none">
                                            {{ $costing->costing_number }}
                                        </a>
                                        <div class="small text-muted">{{ $costing->created_at->format('d/m/Y') }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $costing->customer_name }}</div>
                                        @if ($costing->notes)
                                            <div class="text-muted small text-truncate" style="max-width: 280px;">
                                                {{ $costing->notes }}
                                            </div>
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
                                        <div class="small fw-semibold">{{ $costing->creator->name ?? '—' }}</div>
                                        <div class="text-muted small">{{ $costing->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td>
                                        @if ($costing->isPendingApproval())
                                            <span class="badge bg-warning text-dark border border-warning-subtle px-2 py-1">
                                                <i class="bi bi-clock me-1"></i>Pending Review
                                            </span>
                                        @elseif ($costing->isApproved())
                                            <div>
                                                <span class="badge bg-success text-white px-2 py-1">
                                                    <i class="bi bi-check-circle me-1"></i>Approved
                                                </span>
                                                <div class="text-muted small mt-1" style="font-size: 0.72rem;">
                                                    By {{ $costing->approver->name ?? 'Manager' }}
                                                    @if ($costing->approved_at)
                                                        &middot; {{ $costing->approved_at->format('d/m/Y') }}
                                                    @endif
                                                </div>
                                            </div>
                                        @elseif ($costing->isRejected())
                                            <div>
                                                <span class="badge bg-danger text-white px-2 py-1"
                                                    title="{{ $costing->rejection_reason }}">
                                                    <i class="bi bi-x-circle me-1"></i>Rejected
                                                </span>
                                                @if ($costing->rejection_reason)
                                                    <div class="text-danger small mt-1 text-truncate" style="max-width: 180px; font-size: 0.72rem;" title="{{ $costing->rejection_reason }}">
                                                        "{{ $costing->rejection_reason }}"
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center pe-3">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <a href="{{ route('costing.show', $costing) }}" class="btn btn-outline-secondary btn-sm" title="View Excel Grid">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            @if ($isMgrOrAdmin)
                                                @if ($costing->isPendingApproval())
                                                    <form method="POST" action="{{ route('costing.approve', $costing) }}" class="d-inline"
                                                        onsubmit="return confirm('Approve tender costing {{ $costing->costing_number }}?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm" title="Approve Costing">
                                                            <i class="bi bi-check-lg me-1"></i>Approve
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                                        onclick="openRejectModal({{ $costing->id }}, '{{ $costing->costing_number }}')"
                                                        title="Reject Costing">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                @elseif ($costing->isRejected())
                                                    <form method="POST" action="{{ route('costing.approve', $costing) }}" class="d-inline"
                                                        onsubmit="return confirm('Re-approve tender costing {{ $costing->costing_number }}?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-success btn-sm" title="Re-approve Costing">
                                                            <i class="bi bi-arrow-repeat me-1"></i>Re-approve
                                                        </button>
                                                    </form>
                                                @endif
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

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="rejectForm" method="POST" action="">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h6 class="modal-title fw-bold" id="rejectModalLabel">
                            <i class="bi bi-x-circle me-2"></i>Reject Tender Costing
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="small text-muted mb-3">
                            Rejecting Costing <span class="fw-bold text-dark" id="rejectCostingNumber"></span>. Please provide a clear reason for the procurement staff.
                        </p>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" id="rejectionReasonInput" class="form-control form-control-sm" rows="3"
                                placeholder="Explain why this tender costing was rejected (e.g. margin too low, inaccurate BBN region, etc.)..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-sm px-3">
                            <i class="bi bi-x-lg me-1"></i>Confirm Rejection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function openRejectModal(costingId, costingNumber) {
        document.getElementById('rejectCostingNumber').textContent = costingNumber;
        document.getElementById('rejectForm').action = '/costing/' + costingId + '/reject';
        document.getElementById('rejectionReasonInput').value = '';
        const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
        modal.show();
    }
</script>
@endpush
