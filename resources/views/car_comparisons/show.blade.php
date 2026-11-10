@extends('layouts.app')

@section('title', 'CLVP CAR – ' . $comparison->po_name)

@section('content')

    {{-- Breadcrumb (Matching RFQ General Picture 2) --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('car_comparisons.index') }}">Approvals</a></li>
            <li class="breadcrumb-item active">{{ $comparison->po_name }}</li>
        </ol>
    </nav>

    {{-- Prominent Rejection Notice (Matching RFQ General) --}}
    @if ($comparison->isRejected())
        <div class="alert alert-danger d-flex gap-3 align-items-start mb-4">
            <i class="bi bi-x-octagon-fill fs-4 flex-shrink-0 mt-1"></i>
            <div>
                <h6 class="mb-1 fw-bold">This Car Comparison was rejected</h6>
                <div class="mb-1">
                    Rejected by <strong>{{ $comparison->rejectedBy->name ?? 'Manager' }}</strong>
                    on <strong>{{ $comparison->rejected_at?->format('d M Y H:i') }}</strong>
                </div>
                <div class="fst-italic">"{{ $comparison->rejection_reason }}"</div>
                @if ($comparison->isEditableBy(Auth::user()))
                    <div class="mt-2">
                        <a href="{{ route('car_comparisons.edit', $comparison) }}" class="btn btn-sm btn-danger">
                            <i class="bi bi-pencil me-1"></i>Edit & Resubmit Car Comparison
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Prominent Cancellation Notice --}}
    @if ($comparison->isCancelled())
        <div class="alert alert-warning d-flex gap-3 align-items-start mb-4">
            <i class="bi bi-slash-circle-fill fs-4 flex-shrink-0 mt-1"></i>
            <div>
                <h6 class="mb-1 fw-bold">This Car Comparison was cancelled</h6>
                <div class="mb-1">
                    Cancelled by <strong>{{ $comparison->cancelledBy->name ?? 'User' }}</strong>
                    on <strong>{{ $comparison->cancelled_at?->format('d M Y H:i') }}</strong>
                </div>
                @if ($comparison->cancel_reason)
                    <div class="fst-italic">"{{ $comparison->cancel_reason }}"</div>
                @endif
            </div>
        </div>
    @endif

    {{-- Alert Messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Tabs & Clean Inline Action Bar (Matching RFQ General Picture 2) --}}
    <div class="d-flex justify-content-between align-items-end mb-4 border-bottom flex-wrap gap-2">
        <ul class="nav nav-tabs border-bottom-0 mb-0" id="carClvpTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabApproval">
                    <i class="bi bi-diagram-3 me-1"></i>Approval Workflow
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabClvp">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Dokumen CLVP CAR
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabLog">
                    <i class="bi bi-journal-text me-1"></i>Audit Log
                    <span class="badge bg-secondary ms-1">{{ $comparison->logs->count() }}</span>
                </button>
            </li>
        </ul>

        <div class="btn-group mb-2">
            @if ($prevId)
                <a href="{{ route('car_comparisons.show', ['comparison' => $prevId] + ($statusFilter ? ['status' => $statusFilter] : [])) }}"
                    class="btn btn-sm btn-outline-secondary fw-bold fs-5 px-3" style="line-height: 1;" title="Previous Comparison (← key)">
                    <i class="bi bi-chevron-left"></i>
                </a>
            @else
                <button class="btn btn-sm btn-outline-secondary fw-bold fs-5 px-3 disabled" style="line-height: 1;">
                    <i class="bi bi-chevron-left"></i>
                </button>
            @endif

            @if ($nextId)
                <a href="{{ route('car_comparisons.show', ['comparison' => $nextId] + ($statusFilter ? ['status' => $statusFilter] : [])) }}"
                    class="btn btn-sm btn-outline-secondary fw-bold fs-5 px-3" style="line-height: 1;" title="Next Comparison (→ key)">
                    <i class="bi bi-chevron-right"></i>
                </a>
            @else
                <button class="btn btn-sm btn-outline-secondary fw-bold fs-5 px-3 disabled" style="line-height: 1;">
                    <i class="bi bi-chevron-right"></i>
                </button>
            @endif
        </div>
    </div>

    <div class="tab-content" id="carClvpTabContent">

        {{-- ════════════════════════════════════════════════════════ --}}
        {{-- TAB 1: APPROVAL WORKFLOW (EXACT MATCH RFQ GENERAL)        --}}
        {{-- ════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade show active" id="tabApproval">
            <div class="row g-4">

                {{-- ─── Left column: Approval Status Card (col-lg-4) ─────────────────── --}}
                <div class="col-lg-4">
                    <div class="card mb-3">
                        <div class="card-header py-2">
                            <h6 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Approval Status</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    @if ($comparison->comparison_code)
                                        <div class="fw-bold text-primary">{{ $comparison->comparison_code }}</div>
                                    @endif
                                    <span class="fw-semibold small text-muted">{{ $comparison->po_name }}</span>
                                </div>
                                <span class="badge {{ $comparison->statusBadgeClass() }} fs-6">
                                    {{ $comparison->statusLabel() }}
                                </span>
                            </div>

                            {{-- Edit button for creator/supervisor while pending --}}
                            @if ($comparison->isEditableBy(Auth::user()))
                                <div class="mb-3">
                                    <a href="{{ route('car_comparisons.edit', $comparison) }}" class="btn btn-warning btn-sm w-100">
                                        <i class="bi bi-pencil-square me-1"></i>Edit Comparison
                                    </a>
                                </div>
                            @endif

                            {{-- Timeline Stepper: 3-Tier when submitted by Staff (with Car Access), 2-Tier when submitted directly by SPV --}}
                            <ul class="list-unstyled mb-0">
                                {{-- Step 1: Prepared & Submitted --}}
                                <li class="d-flex gap-3 mb-3">
                                    <div class="text-center" style="width:28px">
                                        <span class="badge bg-success rounded-circle p-2">
                                            <i class="bi bi-check-lg"></i>
                                        </span>
                                        <div style="width:2px;height:30px;background:#e5e7eb;margin:4px auto"></div>
                                    </div>
                                    <div>
                                        <div class="fw-semibold small">Prepared &amp; Submitted</div>
                                        <div class="text-muted small">{{ $comparison->prepared_by_name }}</div>
                                        <div class="text-muted small">{{ ($comparison->supervisor_submitted_at ?? $comparison->created_at)->format('d M Y H:i') }}</div>
                                        @if ($comparison->notes)
                                            <div class="mt-1 p-2 bg-light rounded small fst-italic">
                                                {{ $comparison->notes }}
                                            </div>
                                        @endif
                                    </div>
                                </li>

                                {{-- Optional Step 2: Supervisor Approval (when submitted by Staff) --}}
                                @if ($comparison->requiresSupervisorApproval())
                                    <li class="d-flex gap-3 mb-3">
                                        <div class="text-center" style="width:28px">
                                            @if ($comparison->supervisor_approved_at)
                                                <span class="badge bg-success rounded-circle p-2"><i class="bi bi-check-lg"></i></span>
                                            @elseif ($comparison->isRejected() && $comparison->rejectedBy?->isSupervisor())
                                                <span class="badge bg-danger rounded-circle p-2"><i class="bi bi-x-lg"></i></span>
                                            @elseif ($comparison->isPendingSupervisor())
                                                <span class="badge bg-warning rounded-circle p-2"><i class="bi bi-hourglass-split"></i></span>
                                            @else
                                                <span class="badge bg-secondary rounded-circle p-2"><i class="bi bi-dash"></i></span>
                                            @endif
                                            <div style="width:2px;height:30px;background:#e5e7eb;margin:4px auto"></div>
                                        </div>
                                        <div>
                                            <div class="fw-semibold small">Supervisor Approval</div>
                                            @if ($comparison->supervisor_approved_at)
                                                <div class="text-muted small">{{ $comparison->supervisor->name ?? 'Purchasing Supervisor' }}</div>
                                                <div class="text-muted small">{{ $comparison->supervisor_approved_at->format('d M Y H:i') }}</div>
                                                @if ($comparison->supervisor_notes)
                                                    <div class="mt-1 p-2 bg-light rounded small fst-italic">{{ $comparison->supervisor_notes }}</div>
                                                @endif
                                            @elseif ($comparison->isPendingSupervisor())
                                                <div class="text-warning small">
                                                    Waiting for {{ $comparison->supervisor->name ?? 'Supervisor' }} approval…
                                                </div>
                                            @elseif ($comparison->isRejected() && $comparison->rejectedBy?->isSupervisor())
                                                <div class="text-danger small fw-semibold">Rejected by {{ $comparison->rejectedBy->name }}</div>
                                                <div class="text-muted small">{{ $comparison->rejected_at?->format('d M Y H:i') }}</div>
                                                @if ($comparison->rejection_reason)
                                                    <div class="mt-1 p-2 bg-danger-subtle text-danger rounded small">
                                                        <strong>Reason:</strong> {{ $comparison->rejection_reason }}
                                                    </div>
                                                @endif
                                            @elseif ($comparison->isApproved() && $comparison->isBypassed())
                                                <div class="text-muted small fst-italic">Skipped — Manager bypass</div>
                                            @else
                                                <div class="text-muted small">—</div>
                                            @endif
                                        </div>
                                    </li>
                                @endif

                                {{-- Final Step: Manager Approval --}}
                                <li class="d-flex gap-3">
                                    <div class="text-center" style="width:28px">
                                        @if ($comparison->manager_approved_at)
                                            <span class="badge bg-success rounded-circle p-2"><i class="bi bi-check-lg"></i></span>
                                        @elseif ($comparison->isRejected() && (!$comparison->requiresSupervisorApproval() || !$comparison->rejectedBy?->isSupervisor()))
                                            <span class="badge bg-danger rounded-circle p-2"><i class="bi bi-x-lg"></i></span>
                                        @elseif ($comparison->isCancelled())
                                            <span class="badge bg-secondary rounded-circle p-2"><i class="bi bi-dash"></i></span>
                                        @elseif ($comparison->isPendingManager())
                                            <span class="badge bg-warning rounded-circle p-2"><i class="bi bi-hourglass-split"></i></span>
                                        @else
                                            <span class="badge bg-secondary rounded-circle p-2"><i class="bi bi-dash"></i></span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-semibold small">Manager Approval</div>
                                        @if ($comparison->manager_approved_at)
                                            <div class="text-muted small">{{ $comparison->manager->name ?? 'Purchasing Manager' }}</div>
                                            <div class="text-muted small">{{ $comparison->manager_approved_at->format('d M Y H:i') }}</div>
                                            @if ($comparison->isBypassed())
                                                <div class="mt-1">
                                                    <span class="badge bg-warning text-dark">Manager bypass</span>
                                                </div>
                                                <div class="mt-1 p-2 bg-light rounded small fst-italic">{{ $comparison->bypass_reason }}</div>
                                            @elseif ($comparison->manager_notes)
                                                <div class="mt-1 p-2 bg-light rounded small fst-italic">{{ $comparison->manager_notes }}</div>
                                            @endif
                                        @elseif ($comparison->isRejected() && (!$comparison->requiresSupervisorApproval() || !$comparison->rejectedBy?->isSupervisor()))
                                            <div class="text-danger small fw-semibold">Rejected by {{ $comparison->rejectedBy->name ?? 'Manager' }}</div>
                                            <div class="text-muted small">{{ $comparison->rejected_at?->format('d M Y H:i') }}</div>
                                            @if ($comparison->rejection_reason)
                                                <div class="mt-1 p-2 bg-danger-subtle text-danger rounded small">
                                                    <strong>Reason:</strong> {{ $comparison->rejection_reason }}
                                                </div>
                                            @endif
                                        @elseif ($comparison->isCancelled())
                                            <div class="text-secondary small fw-semibold">Cancelled by {{ $comparison->cancelledBy->name ?? 'User' }}</div>
                                            <div class="text-muted small">{{ $comparison->cancelled_at?->format('d M Y H:i') }}</div>
                                            @if ($comparison->cancel_reason)
                                                <div class="mt-1 p-2 bg-light rounded small fst-italic">{{ $comparison->cancel_reason }}</div>
                                            @endif
                                        @elseif ($comparison->isPendingManager())
                                            <div class="text-warning small">Waiting for manager approval…</div>
                                        @else
                                            <div class="text-muted small">—</div>
                                        @endif
                                    </div>
                                </li>
                            </ul>

                            {{-- Supervisor Action Form (when submitted by Staff and pending SPV) --}}
                            @if (Auth::user()?->isSupervisor() && $comparison->isPendingSupervisor())
                                <div class="mt-4 pt-3 border-top">
                                    <label class="form-label fw-bold small text-info"><i class="bi bi-check2-circle me-1"></i>Supervisor Approval Action</label>
                                    <form method="POST" action="{{ route('car_comparisons.approve', $comparison) }}" class="mb-2">
                                        @csrf
                                        <input type="text" name="notes" class="form-control form-control-sm mb-2" placeholder="Optional supervisor review notes…">
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-info text-white btn-sm flex-grow-1 fw-bold">
                                                <i class="bi bi-check-circle me-1"></i>Approve (Send to Manager)
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                                <i class="bi bi-x-circle me-1"></i>Reject
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endif

                            {{-- Manager Action Form (when pending Manager approval) --}}
                            @if ((Auth::user()?->isManager() || Auth::user()?->isAdmin()) && $comparison->isPendingManager())
                                <div class="mt-4 pt-3 border-top">
                                    <label class="form-label fw-bold small text-primary"><i class="bi bi-shield-check me-1"></i>Manager Approval Action</label>
                                    <form method="POST" action="{{ route('car_comparisons.approve', $comparison) }}" class="mb-2">
                                        @csrf
                                        <input type="text" name="notes" class="form-control form-control-sm mb-2" placeholder="Optional approval remarks…">
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-success btn-sm flex-grow-1 fw-bold">
                                                <i class="bi bi-check-circle me-1"></i>Approve Comparison
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                                <i class="bi bi-x-circle me-1"></i>Reject
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endif

                            {{-- Manager Bypass Card (skip Supervisor when pending_supervisor) --}}
                            @if ($comparison->canBypassApprove(Auth::user()))
                                <div class="card border-danger mt-4">
                                    <div class="card-header py-2" style="background:#fef2f2; border-color:#ef4444;">
                                        <h6 class="mb-0 text-danger small fw-bold"><i class="bi bi-skip-forward me-2"></i>Bypass Approval</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <p class="text-muted small mb-2">
                                            Supervisor review is still pending. As Manager, you may bypass Supervisor approval and give final approval directly.
                                        </p>
                                        <form method="POST" action="{{ route('car_comparisons.bypass-approve', $comparison) }}" class="mb-2">
                                            @csrf
                                            <label class="form-label fw-semibold small text-danger">Reason for Bypass <span class="text-danger">*</span></label>
                                            <textarea name="bypass_reason" class="form-control form-control-sm mb-2" rows="2" placeholder="e.g. Supervisor is on leave…" required></textarea>
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-success btn-sm flex-grow-1 fw-bold"
                                                    onclick="return confirm('Are you sure you want to bypass Supervisor approval and give final approval?')">
                                                    <i class="bi bi-check-circle me-1"></i>Bypass &amp; Final Approve
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                                    <i class="bi bi-x-circle me-1"></i>Reject
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif

                            {{-- Cancel Action --}}
                            @if ($comparison->isCancellableBy(Auth::user()))
                                <div class="mt-3 text-end">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                        <i class="bi bi-slash-circle me-1"></i>Cancel Comparison
                                    </button>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>

                {{-- ─── Right column: PO Summary & Quotation Comparison (col-lg-8) ────────── --}}
                <div class="col-lg-8">
                    @php
                        $vendors = $comparison->vendors ?? [];

                        // Formatter helper for millions
                        $fmtJuta = function($val, $prefix = '') {
                            if ($val <= 0) return '—';
                            if ($val >= 1000000) {
                                $juta = $val / 1000000;
                                $formatted = (floor($juta) == $juta) ? number_format($juta, 0) : rtrim(rtrim(number_format($juta, 2, '.', ''), '0'), '.');
                                return $prefix . ' ' . $formatted . ' Juta';
                            }
                            return $prefix . ' Rp ' . number_format($val, 0, ',', '.');
                        };

                        // Calculate system best vendor (cheapest net price)
                        $systemBestName = null;
                        $lowestNet = PHP_FLOAT_MAX;
                        foreach ($vendors as $v) {
                            $offered = (float)($v['unit_price'] ?? $v['offered_price'] ?? $v['net_price'] ?? $comparison->pricelist_ori ?? 0);
                            $disc = (float)($v['discount'] ?? 0);
                            $cb = (float)($v['cashback'] ?? 0);
                            $totBen = (float)($v['total_benefit'] ?? ($disc + $cb));
                            $net = (float)($v['net_price'] ?? ($offered > 0 ? $offered - $totBen : 0));
                            if ($net > 0 && $net < $lowestNet) {
                                $lowestNet = $net;
                                $systemBestName = $v['name'] ?? null;
                            }
                        }
                        $choiceMatchesBest = $systemBestName && $comparison->selected_vendor === $systemBestName;

                        // Calculate selected subtotal
                        $selectedSubtotal = 0;
                        foreach ($vendors as $v) {
                            if (($v['name'] ?? '') === $comparison->selected_vendor) {
                                $offered = (float)($v['unit_price'] ?? $v['offered_price'] ?? $v['net_price'] ?? $comparison->pricelist_ori ?? 0);
                                $disc = (float)($v['discount'] ?? 0);
                                $cb = (float)($v['cashback'] ?? 0);
                                $totBen = (float)($v['total_benefit'] ?? ($disc + $cb));
                                $net = (float)($v['net_price'] ?? ($offered > 0 ? $offered - $totBen : 0));
                                $selectedSubtotal = (float)($v['subtotal'] ?? ($net * $comparison->qty));
                                break;
                            }
                        }
                        if ($selectedSubtotal <= 0) {
                            $selectedSubtotal = (float)($comparison->pricelist_ori * $comparison->qty);
                        }
                    @endphp

                    {{-- PO Summary Card (Matching RFQ General Picture 2) --}}
                    <div class="card mb-4">
                        <div class="card-header py-2">
                            <h6 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>{{ $comparison->po_name }}</h6>
                        </div>
                        <div class="card-body py-2">
                            <div class="row g-2 small">
                                <div class="col-6 col-md-3">
                                    <div class="text-muted">Vendor (Current PO)</div>
                                    <div class="fw-semibold text-truncate" title="{{ $comparison->po_vendor }}">
                                        {{ $comparison->po_vendor ?: '—' }}
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="text-muted">PO Reference</div>
                                    <div class="fw-semibold">{{ $comparison->po_name }}</div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="text-muted d-flex align-items-center gap-1">
                                        <i class="bi bi-person-check-fill text-primary"></i> Staff's Choice
                                    </div>
                                    <div class="fw-semibold text-primary text-truncate" title="{{ $comparison->selected_vendor }}">
                                        {{ $comparison->selected_vendor ?: '—' }}
                                    </div>
                                    @if ($systemBestName)
                                        @if ($choiceMatchesBest)
                                            <div class="text-success small mt-1">
                                                <i class="bi bi-patch-check-fill me-1"></i>Matches best price
                                            </div>
                                        @else
                                            <div class="text-warning small mt-1">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i>Not cheapest
                                            </div>
                                        @endif
                                    @endif
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="text-muted d-flex align-items-center gap-1">
                                        <i class="bi bi-cpu-fill text-success"></i> System Recommendation
                                    </div>
                                    @if ($systemBestName)
                                        <div class="fw-semibold text-truncate {{ $choiceMatchesBest ? 'text-success' : 'text-danger' }}" title="{{ $systemBestName }}">
                                            {{ $systemBestName }}
                                        </div>
                                        @if (!$choiceMatchesBest)
                                            <div class="text-danger small mt-1">
                                                <i class="bi bi-arrow-left-circle-fill me-1"></i>Cheaper option
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-muted">—</div>
                                    @endif
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="text-muted">Total Subtotal</div>
                                    <div class="fw-semibold">
                                        IDR {{ number_format($selectedSubtotal, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>

                            @if ($comparison->notes)
                                <div class="mt-2 p-2 bg-light rounded small">
                                    <strong>Notes:</strong> {{ $comparison->notes }}
                                </div>
                            @endif

                            {{-- Vendors Selected for Comparison (Matching RFQ General Picture 2) --}}
                            @if (!empty($vendors))
                                <div class="mt-2">
                                    <div class="text-muted small fw-semibold mb-1">Vendors Selected for Comparison ({{ count($vendors) }}):</div>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($vendors as $v)
                                            @php
                                                $isStaffChoice = ($v['name'] ?? '') === $comparison->selected_vendor;
                                                $isSysBest = ($v['name'] ?? '') === $systemBestName;
                                            @endphp
                                            <span class="badge d-inline-flex align-items-center gap-1
                                                {{ $isStaffChoice && $isSysBest ? 'bg-success' : ($isStaffChoice ? 'bg-primary' : ($isSysBest ? 'bg-warning text-dark' : 'bg-light text-dark border')) }}">
                                                @if ($isStaffChoice)
                                                    <i class="bi bi-person-check-fill" title="Staff's choice"></i>
                                                @endif
                                                @if ($isSysBest && !$isStaffChoice)
                                                    <i class="bi bi-cpu-fill" title="System recommendation"></i>
                                                @endif
                                                {{ $v['name'] ?? '—' }}
                                            </span>
                                        @endforeach
                                    </div>
                                    <div class="text-muted small mt-1 d-flex gap-3 flex-wrap">
                                        <span><i class="bi bi-person-check-fill text-primary me-1"></i>Blue = Staff's choice</span>
                                        <span><i class="bi bi-cpu-fill text-warning me-1"></i>Yellow = System best price</span>
                                        <span><i class="bi bi-check-circle-fill text-success me-1"></i>Green = Both match</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Legend --}}
                    <div class="d-flex gap-3 mb-3 flex-wrap align-items-center">
                        <span class="fw-semibold small">Legend:</span>
                        <span class="badge price-best px-2 py-1">&#9733; Best Price</span>
                        <span class="badge price-current px-2 py-1">&#9830; Current PO Vendor</span>
                        <span class="badge price-worst px-2 py-1">&#9660; Highest Price</span>
                    </div>

                    {{-- Odoo Purchase History Card (Benchmark from Previous POs) --}}
                    @if (!empty($history) && !empty($rfq))
                        @include('car_rfq._purchase_history_card')
                    @endif

                    {{-- Vehicle Unit & Quotations Comparison Table (Scoped for RFQ Car) --}}
                    <div class="card mb-4 border shadow-sm">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <span class="badge bg-dark me-2">UNIT BARU</span>
                                <strong>{{ $comparison->car_model }}</strong>
                                <span class="text-muted ms-2">{{ $comparison->qty }} {{ strtoupper($comparison->uom) }}</span>
                            </div>
                            <div class="small">
                                <span class="text-muted">Pricelist Original:</span>
                                <strong class="text-primary">IDR {{ number_format($comparison->pricelist_ori, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                        <div class="card-body p-3 bg-white">
                            <div class="d-flex gap-2 mb-3 flex-wrap small">
                                <span class="badge bg-light text-dark border">Color: {{ $comparison->warna ?: '—' }}</span>
                                <span class="badge bg-light text-dark border">BBN: {{ $comparison->bbn ?: '—' }}</span>
                                <span class="badge bg-light text-dark border">VIN: {{ $comparison->vin_code ?: '—' }}</span>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0 align-middle small">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th style="width:26%">Dealer / Vendor</th>
                                            <th style="width:14%">Offered Price</th>
                                            <th style="width:10%">Discount</th>
                                            <th style="width:10%">Cashback</th>
                                            <th style="width:11%">Total Benefit</th>
                                            <th style="width:13%">Net Price/Unit</th>
                                            <th style="width:8%">Status</th>
                                            <th style="width:8%">Delivery</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($vendors as $v)
                                            @php
                                                $vName = $v['name'] ?? '—';
                                                $isStaff = ($vName === $comparison->selected_vendor);
                                                $isBest = ($vName === $systemBestName);
                                                $offered = (float)($v['unit_price'] ?? $v['offered_price'] ?? $v['net_price'] ?? $comparison->pricelist_ori ?? 0);
                                                $disc = (float)($v['discount'] ?? 0);
                                                $cb = (float)($v['cashback'] ?? 0);
                                                $totBen = (float)($v['total_benefit'] ?? ($disc + $cb));
                                                $net = (float)($v['net_price'] ?? ($offered > 0 ? $offered - $totBen : 0));
                                                $subtotal = (float)($v['subtotal'] ?? ($net * $comparison->qty));
                                                $avail = strtolower($v['availability'] ?? $v['ready_indent'] ?? '');
                                                $isReady = ($avail === 'ready' || !empty($v['ready']));
                                                $vPic = $v['pic'] ?? $v['sales_person'] ?? $v['contact'] ?? null;
                                                $vPhone = $v['phone'] ?? null;
                                            @endphp
                                            <tr class="{{ $isStaff ? 'table-success fw-semibold' : '' }}">
                                                <td>
                                                    <div class="d-flex align-items-center gap-1">
                                                        @if ($isStaff)
                                                            <i class="bi bi-person-check-fill text-primary" title="Staff's choice"></i>
                                                        @endif
                                                        @if ($isBest)
                                                            <i class="bi bi-cpu-fill text-success" title="System recommendation"></i>
                                                        @endif
                                                        <span>{{ $vName }}</span>
                                                    </div>
                                                    @if ($vPic || $vPhone)
                                                        <div class="text-muted" style="font-size:9px;">
                                                            {{ $vPic ?: '' }} {{ $vPhone ? '('.$vPhone.')' : '' }}
                                                        </div>
                                                    @endif
                                                    @if (!empty($v['bonus']))
                                                        <div class="text-primary" style="font-size:9px;">
                                                            <i class="bi bi-gift me-1"></i>{{ $v['bonus'] }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    IDR {{ number_format($offered, 0, ',', '.') }}
                                                </td>
                                                <td class="text-center text-danger">
                                                    {{ $fmtJuta($disc, 'Dis.') }}
                                                </td>
                                                <td class="text-center text-info">
                                                    {{ $fmtJuta($cb, 'CB') }}
                                                </td>
                                                <td class="text-center text-success fw-bold">
                                                    {{ $fmtJuta($totBen, '') }}
                                                </td>
                                                <td class="text-end fw-bold">
                                                    IDR {{ number_format($net, 0, ',', '.') }}
                                                </td>
                                                <td class="text-center">
                                                    @if ($isReady)
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Ready</span>
                                                    @else
                                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Indent</span>
                                                    @endif
                                                </td>
                                                <td class="text-center text-muted">
                                                    {{ $v['delivery_days'] ?? $v['indent_duration'] ?? '—' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>{{-- end right column --}}
            </div>{{-- end row --}}
        </div>{{-- end tabApproval --}}

        {{-- ════════════════════════════════════════════════════════ --}}
        {{-- TAB 2: DOKUMEN CLVP (EXACT MATCH OFFICIAL USER SCAN)      --}}
        {{-- ════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="tabClvp" role="tabpanel">

            {{-- Top Actions inside Tab (Matching RFQ General line 881-889) --}}
            <div class="d-flex justify-content-end mb-3 gap-2" style="max-width:1080px; margin:0 auto;">
                <a href="{{ route('car_comparisons.pdf', $comparison) }}" target="_blank" class="btn btn-danger btn-sm">
                    <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF
                </a>
                <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i>Print
                </button>
            </div>

            {{-- ── CLVP Document (Replicating Comparisson PO 270 - Jalan Baru Jalan.pdf) ── --}}
            <div id="clvpDocument" class="bg-white border p-4 shadow-sm mx-auto"
                style="font-family: Arial, sans-serif; font-size:11px; color:#000; max-width:1080px;">

                {{-- Document Header --}}
                <div class="text-center mb-2">
                    <div style="font-size:14px; font-weight:bold; letter-spacing:0.5px;">
                        COMPARISON LOCAL VENDOR PRICE CAR ( CLVP CAR )
                    </div>
                </div>

                <div class="d-flex align-items-center mb-3 gap-4">
                    {{-- Logo --}}
                    <div style="min-width:140px;">
                        <img src="{{ asset('logo.png') }}" style="height:46px; max-width:140px; object-fit:contain;" alt="HARENT">
                    </div>

                    {{-- Category checkboxes matching standard form --}}
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

                {{-- CLVP Table (Replicating exact columns & rows of CamScanner paper form) --}}
                @php
                    $vendorCount = count($vendors);
                    $hasFourthCol = ($vendorCount <= 3); // Pre-printed forms have 4 vendor columns ending with PIC:TELP
                    $totalCols = $vendorCount + ($hasFourthCol ? 1 : 0);
                @endphp

                <table style="width:100%; table-layout:fixed; border-collapse:collapse; font-size:11px; border:1px solid #000;">
                    <colgroup>
                        <col style="width:32px;">
                        <col style="width:260px;">
                        <col style="width:48px;">
                        <col style="width:38px;">
                        <col style="width:38px;">
                        <col style="width:124px;">
                        @foreach ($vendors as $v)
                            <col style="width: calc((100% - 540px) / {{ max(1, $totalCols) }});">
                        @endforeach
                        @if ($hasFourthCol)
                            <col style="width: calc((100% - 540px) / {{ max(1, $totalCols) }});">
                        @endif
                    </colgroup>
                    <thead>
                        {{-- Row 1: fixed headers + "Supplier / Vendor" spanning vendor cols --}}
                        <tr style="height:28px;">
                            <th rowspan="2" style="border:1px solid #000; padding:6px 4px; text-align:center; vertical-align:middle;">No</th>
                            <th rowspan="2" style="border:1px solid #000; padding:6px 10px; text-align:center; vertical-align:middle;">Nama Barang</th>
                            <th rowspan="2" style="border:1px solid #000; padding:6px 4px; text-align:center; vertical-align:middle;">Kode Barang</th>
                            <th rowspan="2" style="border:1px solid #000; padding:6px 4px; text-align:center; vertical-align:middle;">QTY</th>
                            <th rowspan="2" style="border:1px solid #000; padding:6px 4px; text-align:center; vertical-align:middle;">UoM</th>
                            <th rowspan="2" style="border:1px solid #000; padding:6px 4px; text-align:center; vertical-align:middle;">PricelistOriginal</th>
                            @if ($totalCols > 0)
                                <th colspan="{{ $totalCols }}" style="border:1px solid #000; padding:6px 4px; text-align:center; vertical-align:middle; background:#f0f0f0;">
                                    Supplier / Vendor
                                </th>
                            @endif
                        </tr>
                        {{-- Row 2: individual dealer names + PIC + TELP --}}
                        <tr style="height:62px;">
                            @foreach ($vendors as $v)
                                @php
                                    $isRec = ($v['name'] ?? '') === $comparison->selected_vendor;
                                    $vPic = $v['pic'] ?? $v['sales_person'] ?? $v['contact'] ?? null;
                                    $vPhone = $v['phone'] ?? null;
                                @endphp
                                <th style="border:1px solid #000; padding:6px 8px; text-align:center; vertical-align:middle; {{ $isRec ? 'background:#d4edda;' : '' }}">
                                    <div style="font-weight:bold; font-size:11px; line-height:1.25;">{{ $v['name'] ?? '—' }}</div>
                                    @if($vPic)<div style="font-weight:normal; font-size:10px; margin-top:3px; color:#333;">{{ $vPic }}</div>@endif
                                    @if($vPhone)<div style="font-weight:normal; font-size:10px; color:#333;">{{ $vPhone }}</div>@endif
                                </th>
                            @endforeach
                            @if ($hasFourthCol)
                                <th style="border:1px solid #000; padding:6px 8px; text-align:center; vertical-align:middle; font-weight:bold; font-size:10.5px;">
                                    PIC : TELP :
                                </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Row 1: Vehicle Model & Offered Unit Price --}}
                        <tr style="height:36px;">
                            <td style="border:1px solid #000; padding:6px 4px; text-align:center; vertical-align:middle;">1</td>
                            <td style="border:1px solid #000; padding:6px 10px; vertical-align:middle; font-weight:bold; line-height:1.35;">
                                {{ $comparison->car_model }}
                            </td>
                            <td style="border:1px solid #000; padding:6px 4px; text-align:center; vertical-align:middle; color:#888; font-size:10px;">
                                —
                            </td>
                            <td style="border:1px solid #000; padding:6px 4px; text-align:center; vertical-align:middle; font-weight:bold;">
                                {{ $comparison->qty }}
                            </td>
                            <td style="border:1px solid #000; padding:6px 4px; text-align:center; vertical-align:middle;">
                                {{ $comparison->uom }}
                            </td>
                            <td style="border:1px solid #000; padding:6px 6px; text-align:center; vertical-align:middle; font-weight:bold; white-space:nowrap;">
                                Rp. {{ number_format($comparison->pricelist_ori, 0, ',', '.') }}/unit
                            </td>
                            @foreach ($vendors as $v)
                                @php
                                    $isRec = ($v['name'] ?? '') === $comparison->selected_vendor;
                                    $offered = (float)($v['unit_price'] ?? $v['offered_price'] ?? $v['net_price'] ?? $comparison->pricelist_ori ?? 0);
                                @endphp
                                <td style="border:1px solid #000; padding:6px 6px; text-align:center; vertical-align:middle; font-weight:bold; color:#000; white-space:nowrap; {{ $isRec ? 'background:#f0fff4;' : '' }}">
                                    Rp. {{ number_format($offered, 0, ',', '.') }}/unit
                                </td>
                            @endforeach
                            @if ($hasFourthCol)
                                <td style="border:1px solid #000;"></td>
                            @endif
                        </tr>

                        {{-- Row Spacer: Blank row between Nama Barang and BBN (matching official form scan) --}}
                        <tr style="height:26px;">
                            <td style="border:1px solid #000; padding:4px 4px;">&nbsp;</td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            @foreach ($vendors as $v)
                                @php $isRec = ($v['name'] ?? '') === $comparison->selected_vendor; @endphp
                                <td style="border:1px solid #000; {{ $isRec ? 'background:#f0fff4;' : '' }}"></td>
                            @endforeach
                            @if ($hasFourthCol)
                                <td style="border:1px solid #000;"></td>
                            @endif
                        </tr>

                        {{-- Row 2: BBN (Vendor cells are completely BLANK like user's scan!) --}}
                        <tr style="height:28px;">
                            <td style="border:1px solid #000; padding:5px 4px; text-align:center; vertical-align:middle;"></td>
                            <td style="border:1px solid #000; padding:5px 10px; vertical-align:middle; font-size:11px;">
                                BBN : {{ $comparison->bbn ?: '—' }}
                            </td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            @foreach ($vendors as $v)
                                @php $isRec = ($v['name'] ?? '') === $comparison->selected_vendor; @endphp
                                <td style="border:1px solid #000; {{ $isRec ? 'background:#f0fff4;' : '' }}"></td>
                            @endforeach
                            @if ($hasFourthCol)
                                <td style="border:1px solid #000;"></td>
                            @endif
                        </tr>

                        {{-- Row 3: VC (Vendor cells are completely BLANK like user's scan!) --}}
                        <tr style="height:28px;">
                            <td style="border:1px solid #000; padding:5px 4px; text-align:center; vertical-align:middle;"></td>
                            <td style="border:1px solid #000; padding:5px 10px; vertical-align:middle; font-size:11px;">
                                VC : {{ $comparison->vin_code ?: '—' }}
                            </td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            @foreach ($vendors as $v)
                                @php $isRec = ($v['name'] ?? '') === $comparison->selected_vendor; @endphp
                                <td style="border:1px solid #000; {{ $isRec ? 'background:#f0fff4;' : '' }}"></td>
                            @endforeach
                            @if ($hasFourthCol)
                                <td style="border:1px solid #000;"></td>
                            @endif
                        </tr>

                        {{-- Row 4: Warna & Dis. (Dis sits on Row 4, aligned with Warna!) --}}
                        <tr style="height:28px;">
                            <td style="border:1px solid #000; padding:5px 4px; text-align:center; vertical-align:middle;"></td>
                            <td style="border:1px solid #000; padding:5px 10px; vertical-align:middle; font-size:11px;">
                                Warna : {{ $comparison->warna ?: '—' }}
                            </td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            @foreach ($vendors as $v)
                                @php
                                    $isRec = ($v['name'] ?? '') === $comparison->selected_vendor;
                                    $disc = (float)($v['discount'] ?? 0);
                                @endphp
                                <td style="border:1px solid #000; padding:5px 6px; text-align:center; vertical-align:middle; font-size:11px; font-weight:normal; color:#000; white-space:nowrap; {{ $isRec ? 'background:#f0fff4;' : '' }}">
                                    {{ $fmtJuta($disc, 'Dis.') }}
                                </td>
                            @endforeach
                            @if ($hasFourthCol)
                                <td style="border:1px solid #000;"></td>
                            @endif
                        </tr>

                        {{-- Row 5: Cashback --}}
                        <tr style="height:28px;">
                            <td style="border:1px solid #000; padding:5px 4px; text-align:center; vertical-align:middle;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            @foreach ($vendors as $v)
                                @php
                                    $isRec = ($v['name'] ?? '') === $comparison->selected_vendor;
                                    $cb = (float)($v['cashback'] ?? 0);
                                @endphp
                                <td style="border:1px solid #000; padding:5px 6px; text-align:center; vertical-align:middle; font-size:11px; font-weight:normal; color:#000; white-space:nowrap; {{ $isRec ? 'background:#f0fff4;' : '' }}">
                                    {{ $fmtJuta($cb, 'Cashback') }}
                                </td>
                            @endforeach
                            @if ($hasFourthCol)
                                <td style="border:1px solid #000;"></td>
                            @endif
                        </tr>

                        {{-- Row 6: Total Benefit --}}
                        <tr style="height:28px;">
                            <td style="border:1px solid #000; padding:5px 4px; text-align:center; vertical-align:middle;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            @foreach ($vendors as $v)
                                @php
                                    $isRec = ($v['name'] ?? '') === $comparison->selected_vendor;
                                    $disc = (float)($v['discount'] ?? 0);
                                    $cb = (float)($v['cashback'] ?? 0);
                                    $totBen = (float)($v['total_benefit'] ?? ($disc + $cb));
                                @endphp
                                <td style="border:1px solid #000; padding:5px 6px; text-align:center; vertical-align:middle; font-size:11px; font-weight:bold; color:#000; white-space:nowrap; {{ $isRec ? 'background:#f0fff4;' : '' }}">
                                    {{ $fmtJuta($totBen, 'Total') }}
                                </td>
                            @endforeach
                            @if ($hasFourthCol)
                                <td style="border:1px solid #000;"></td>
                            @endif
                        </tr>

                        {{-- Row 7: BLANK SPACER ROW (Between Total Benefit and Ready/Indent!) --}}
                        <tr style="height:26px;">
                            <td style="border:1px solid #000; padding:4px 4px;">&nbsp;</td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            @foreach ($vendors as $v)
                                @php $isRec = ($v['name'] ?? '') === $comparison->selected_vendor; @endphp
                                <td style="border:1px solid #000; {{ $isRec ? 'background:#f0fff4;' : '' }}"></td>
                            @endforeach
                            @if ($hasFourthCol)
                                <td style="border:1px solid #000;"></td>
                            @endif
                        </tr>

                        {{-- Row 8: Dedicated READY ROW with Square Box --}}
                        <tr style="height:28px;">
                            <td style="border:1px solid #000; padding:4px 4px;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            @foreach ($vendors as $v)
                                @php
                                    $isRec = ($v['name'] ?? '') === $comparison->selected_vendor;
                                    $avail = strtolower($v['availability'] ?? $v['ready_indent'] ?? '');
                                    $isReady = ($avail === 'ready' || !empty($v['ready']));
                                @endphp
                                <td style="border:1px solid #000; padding:4px 10px; vertical-align:middle; {{ $isRec ? 'background:#f0fff4;' : '' }}">
                                    <div style="display:inline-flex; align-items:center; gap:6px;">
                                        <span style="display:inline-block; width:13px; height:13px; border:1.5px solid #000; text-align:center; line-height:11px; font-size:10px; font-weight:bold; flex-shrink:0;">
                                            {{ $isReady ? 'V' : '' }}
                                        </span>
                                        <span style="font-size:10.5px; font-weight:normal; color:#000;">Ready</span>
                                    </div>
                                </td>
                            @endforeach
                            @if ($hasFourthCol)
                                <td style="border:1px solid #000;"></td>
                            @endif
                        </tr>

                        {{-- Row 9: Dedicated INDENT / KOSONG ROW with Square Box --}}
                        <tr style="height:28px;">
                            <td style="border:1px solid #000; padding:4px 4px;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            <td style="border:1px solid #000;"></td>
                            @foreach ($vendors as $v)
                                @php
                                    $isRec = ($v['name'] ?? '') === $comparison->selected_vendor;
                                    $avail = strtolower($v['availability'] ?? $v['ready_indent'] ?? '');
                                    $isIndent = ($avail === 'indent' || !empty($v['indent']));
                                @endphp
                                <td style="border:1px solid #000; padding:4px 10px; vertical-align:middle; {{ $isRec ? 'background:#f0fff4;' : '' }}">
                                    <div style="display:inline-flex; align-items:center; gap:6px; flex-wrap:nowrap;">
                                        <span style="display:inline-block; width:13px; height:13px; border:1.5px solid #000; text-align:center; line-height:11px; font-size:10px; font-weight:bold; flex-shrink:0;">
                                            {{ $isIndent ? 'V' : '' }}
                                        </span>
                                        <span style="font-size:10.5px; font-weight:normal; color:#000; white-space:nowrap;">Indent / Kosong</span>
                                        @if($isIndent && (!empty($v['indent_duration']) || !empty($v['delivery_days'])))
                                            <span style="font-size:9.5px; color:#555; white-space:nowrap;">({{ $v['indent_duration'] ?? ($v['delivery_days'].' hr') }})</span>
                                        @endif
                                    </div>
                                </td>
                            @endforeach
                            @if ($hasFourthCol)
                                <td style="border:1px solid #000;"></td>
                            @endif
                        </tr>

                        {{-- Rows 10 to 12: Standard Empty Grid Rows below --}}
                        @for ($i = 0; $i < 3; $i++)
                            <tr style="height:26px;">
                                <td style="border:1px solid #000; padding:4px 4px;">&nbsp;</td>
                                <td style="border:1px solid #000;"></td>
                                <td style="border:1px solid #000;"></td>
                                <td style="border:1px solid #000;"></td>
                                <td style="border:1px solid #000;"></td>
                                <td style="border:1px solid #000;"></td>
                                @foreach ($vendors as $v)
                                    @php $isRec = ($v['name'] ?? '') === $comparison->selected_vendor; @endphp
                                    <td style="border:1px solid #000; {{ $isRec ? 'background:#f0fff4;' : '' }}"></td>
                                @endforeach
                                @if ($hasFourthCol)
                                    <td style="border:1px solid #000;"></td>
                                @endif
                            </tr>
                        @endfor
                    </tbody>
                </table>

                {{-- Footer Matching RFQ General (Clean, Elegant, with Natural Whitespace) --}}
                <div class="mt-3 d-flex justify-content-between align-items-start" style="font-size:11px;">
                    <div>
                        <div class="fw-bold">No. CLVP : {{ $comparison->comparison_code }}</div>
                        <div class="text-muted" style="margin-top:2px;">Ref PO: {{ $comparison->po_name }}</div>
                        @if ($comparison->notes)
                            <div class="text-muted mt-2" style="max-width:580px;">{{ $comparison->notes }}</div>
                        @endif
                    </div>
                    <div style="text-align:right;">
                        <div>Tgl {{ $comparison->created_at->format('d/m/y') }}</div>
                        <div>Dibuat oleh,</div>
                        <div style="height:48px;"></div>
                        <div>({{ $comparison->prepared_by_name }})</div>
                        <div style="font-size:8px; color:#666; margin-top:4px;">SDP/FR/PCH/12, Rev.02</div>
                    </div>
                </div>

            </div>{{-- end clvpDocument --}}

        </div>{{-- end tabClvp --}}

        {{-- ════════════════════════════════════════════════════════ --}}
        {{-- TAB 3: AUDIT LOG (MATCHING RFQ GENERAL)                  --}}
        {{-- ════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="tabLog" role="tabpanel">
            <div class="card" style="max-width:640px;">
                <div class="card-header py-2">
                    <h6 class="mb-0"><i class="bi bi-journal-text me-2"></i>Activity Log</h6>
                </div>
                <div class="card-body p-0">
                    @if ($comparison->logs->isEmpty())
                        <div class="p-3 text-muted small">No activity recorded yet.</div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($comparison->logs as $log)
                                <li class="list-group-item py-3">
                                    <div class="d-flex gap-3 align-items-start">
                                        <i class="bi {{ $log->actionIcon() }} fs-5 mt-1 flex-shrink-0"></i>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">{{ $log->actionLabel() }}</div>
                                            <div class="text-muted small">
                                                by {{ $log->user->name ?? 'System' }}
                                                &middot; {{ $log->created_at->format('d M Y H:i') }}
                                                ({{ $log->created_at->diffForHumans() }})
                                            </div>
                                            @if ($log->notes)
                                                <div class="mt-1 p-2 bg-light rounded small fst-italic">
                                                    {{ $log->notes }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- REJECT MODAL --}}
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('car_comparisons.reject', $comparison) }}" class="modal-content">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Reject Car Comparison</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                    <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Please state clearly why this comparison is rejected…"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>

    {{-- CANCEL MODAL --}}
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('car_comparisons.cancel', $comparison) }}" class="modal-content">
                @csrf
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title"><i class="bi bi-slash-circle me-2"></i>Cancel Car Comparison</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold">Cancellation Reason <span class="text-danger">*</span></label>
                    <textarea name="cancel_reason" class="form-control" rows="3" required placeholder="Provide reason for cancelling this comparison…"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-secondary fw-bold">Confirm Cancel</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('keydown', function(e) {
            if (['input', 'textarea', 'select'].includes(document.activeElement.tagName.toLowerCase())) {
                return;
            }
            @if ($prevId)
                if (e.key === 'ArrowLeft') {
                    window.location.href = "{{ route('car_comparisons.show', ['comparison' => $prevId] + ($statusFilter ? ['status' => $statusFilter] : [])) }}";
                }
            @endif
            @if ($nextId)
                if (e.key === 'ArrowRight') {
                    window.location.href = "{{ route('car_comparisons.show', ['comparison' => $nextId] + ($statusFilter ? ['status' => $statusFilter] : [])) }}";
                }
            @endif
        });
    </script>
@endpush
