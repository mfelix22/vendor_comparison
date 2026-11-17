@extends('layouts.app')

@section('title', 'CLVP – ' . $comparison->po_name)

@section('content')

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('comparisons.index') }}">Approvals</a></li>
            <li class="breadcrumb-item active">{{ $comparison->po_name }}</li>
        </ol>
    </nav>

    {{-- Revision Switcher (when multiple comparisons exist for this PO) --}}
    @if (isset($allRevisions) && $allRevisions->count() > 1)
        <div class="card mb-3 shadow-sm border-0" style="background: linear-gradient(135deg, #f0fdfa 0%, #e6fffa 100%); border-left: 5px solid #0d9488 !important;">
            <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history fs-5" style="color: #0d9488;"></i>
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 0.88rem;">Riwayat Versi Komparasi (PO: {{ $comparison->po_name }})</div>
                        <div class="text-muted small" style="font-size: 0.76rem;">Terdapat {{ $allRevisions->count() }} versi komparasi untuk pengadaan ini (bisa beralih versi di sebelah kanan):</div>
                    </div>
                </div>
                <div class="btn-group btn-group-sm shadow-sm" role="group">
                    @foreach ($allRevisions as $idx => $rev)
                        @php
                            $isCurrent = $rev->id === $comparison->id;
                            $label = $idx === 0 ? 'Comparison 1 (Original)' : ($allRevisions->count() === 2 ? 'Comparison Revisi' : 'Comparison Revisi ' . $idx);
                        @endphp
                        <a href="{{ route('comparisons.show', $rev) }}"
                           class="btn {{ $isCurrent ? 'text-white fw-bold' : 'btn-outline-secondary bg-white' }}"
                           style="{{ $isCurrent ? 'background-color: #0d9488; border-color: #0d9488;' : '' }}"
                           title="{{ $rev->comparison_code }} ({{ $rev->statusLabel() }})">
                            <i class="bi {{ $isCurrent ? 'bi-check-circle-fill' : 'bi-file-earmark-text' }} me-1"></i>
                            {{ $label }}
                            <span class="badge {{ $rev->statusBadgeClass() }} ms-1" style="font-size: 0.68rem;">
                                {{ $rev->statusLabel() }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Prominent rejection notice for creators --}}
    @if ($comparison->isRejected() && Auth::user()->isCreator())
        <div class="alert alert-danger d-flex gap-3 align-items-start mb-4">
            <i class="bi bi-x-octagon-fill fs-4 flex-shrink-0 mt-1"></i>
            <div>
                <h6 class="mb-1 fw-bold">This CLVP was rejected</h6>
                <div class="mb-1">
                    Rejected by <strong>{{ $comparison->rejectedBy->name ?? '—' }}</strong>
                    on <strong>{{ $comparison->rejected_at?->format('d M Y H:i') }}</strong>
                </div>
                <div class="fst-italic">"{{ $comparison->rejection_reason }}"</div>
                <div class="mt-2">
                    <a href="{{ route('rfq.show', $comparison->po_id) }}" class="btn btn-sm btn-danger">
                        <i class="bi bi-pencil me-1"></i>Submit New CLVP for this RFQ
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Prominent cancellation notice for creators --}}
    @if ($comparison->isCancelled() && Auth::user()->isCreator())
        <div class="alert alert-warning d-flex gap-3 align-items-start mb-4">
            <i class="bi bi-slash-circle-fill fs-4 flex-shrink-0 mt-1"></i>
            <div>
                <h6 class="mb-1 fw-bold">This CLVP was cancelled</h6>
                <div class="mb-1">
                    Cancelled by <strong>{{ $comparison->cancelledBy->name ?? '—' }}</strong>
                    on <strong>{{ $comparison->cancelled_at?->format('d M Y H:i') }}</strong>
                </div>
                @if ($comparison->cancel_reason)
                    <div class="fst-italic">"{{ $comparison->cancel_reason }}"</div>
                @endif
                <div class="mt-2">
                    <a href="{{ route('rfq.show', $comparison->po_id) }}" class="btn btn-sm btn-warning">
                        <i class="bi bi-pencil me-1"></i>Submit New CLVP for this RFQ
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Tabs --}}
    <div class="d-flex justify-content-between align-items-end mb-4 border-bottom">
        <ul class="nav nav-tabs border-bottom-0 mb-0" id="clvpTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabApproval">
                    <i class="bi bi-diagram-3 me-1"></i>Approval Workflow
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabClvp">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Dokumen CLVP
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
            @if (!empty($prevId))
                <a href="{{ route('comparisons.show', ['comparison' => $prevId] + (!empty($statusFilter) ? ['status' => $statusFilter] : [])) }}" class="btn btn-sm btn-outline-secondary fw-bold fs-5 px-3" style="line-height: 1;">
                    <i class="bi bi-chevron-left"></i>
                </a>
            @else
                <button class="btn btn-sm btn-outline-secondary fw-bold fs-5 px-3 disabled" style="line-height: 1;"><i class="bi bi-chevron-left"></i></button>
            @endif

            @if (!empty($nextId))
                <a href="{{ route('comparisons.show', ['comparison' => $nextId] + (!empty($statusFilter) ? ['status' => $statusFilter] : [])) }}" class="btn btn-sm btn-outline-secondary fw-bold fs-5 px-3" style="line-height: 1;">
                    <i class="bi bi-chevron-right"></i>
                </a>
            @else
                <button class="btn btn-sm btn-outline-secondary fw-bold fs-5 px-3 disabled" style="line-height: 1;"><i class="bi bi-chevron-right"></i></button>
            @endif
        </div>
    </div>

    <div class="tab-content">

        {{-- ════════════════════════════════════════════════════════ --}}
        {{-- TAB 1: APPROVAL WORKFLOW                                 --}}
        {{-- ════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade show active" id="tabApproval">
            <div class="row g-4">

                {{-- ─── Left column: approval workflow card ─────────────────── --}}
                <div class="col-lg-4">

                    {{-- Status card --}}
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

                            {{-- Edit button for creator while pending supervisor --}}
                            @if ($comparison->isEditableBy(Auth::user()))
                                <div class="mb-3">
                                    <a href="{{ route('comparisons.edit', $comparison) }}"
                                        class="btn btn-warning btn-sm w-100">
                                        <i class="bi bi-pencil-square me-1"></i>Edit CLVP
                                    </a>
                                </div>
                            @endif

                            {{-- Timeline --}}
                            <ul class="list-unstyled mb-0">
                                {{-- Step 1: Submission --}}
                                <li class="d-flex gap-3 mb-3">
                                    <div class="text-center" style="width:28px">
                                        <span class="badge bg-success rounded-circle p-2">
                                            <i class="bi bi-check-lg"></i>
                                        </span>
                                        <div style="width:2px;height:30px;background:#e5e7eb;margin:4px auto"></div>
                                    </div>
                                    <div>
                                        <div class="fw-semibold small">Submitted</div>
                                        <div class="text-muted small">{{ $comparison->creator->name ?? '—' }}</div>
                                        <div class="text-muted small">{{ $comparison->created_at?->format('d M Y H:i') ?? '—' }}
                                        </div>
                                        @if ($comparison->notes)
                                            <div class="mt-1 p-2 bg-light rounded small fst-italic">
                                                {{ $comparison->notes }}
                                            </div>
                                        @endif
                                    </div>
                                </li>


                                {{-- Step 2: Supervisor --}}
                                <li class="d-flex gap-3 mb-3">
                                    <div class="text-center" style="width:28px">
                                        @if ($comparison->supervisor_approved_at)
                                            <span class="badge bg-success rounded-circle p-2"><i class="bi bi-check-lg"></i></span>
                                        @elseif($comparison->isRejected() && $comparison->rejectedBy?->isSupervisor())
                                            <span class="badge bg-danger rounded-circle p-2"><i class="bi bi-x-lg"></i></span>
                                        @elseif($comparison->isPendingSupervisor())
                                            <span class="badge bg-warning rounded-circle p-2"><i class="bi bi-hourglass-split"></i></span>
                                        @else
                                            <span class="badge bg-secondary rounded-circle p-2"><i class="bi bi-dash"></i></span>
                                        @endif
                                        <div style="width:2px;height:30px;background:#e5e7eb;margin:4px auto"></div>
                                    </div>
                                    <div>
                                        <div class="fw-semibold small">Supervisor Approval</div>
                                        @if ($comparison->supervisor_approved_at)
                                            <div class="text-muted small">{{ $comparison->supervisor->name ?? '—' }}</div>
                                            <div class="text-muted small">{{ $comparison->supervisor_approved_at->format('d M Y H:i') }}</div>
                                            @if ($comparison->supervisor_notes)
                                                <div class="mt-1 p-2 bg-light rounded small fst-italic">{{ $comparison->supervisor_notes }}</div>
                                            @endif
                                        @elseif($comparison->isPendingSupervisor())
                                            <div class="text-warning small">Waiting for approval…</div>
                                        @elseif($comparison->isApproved() && $comparison->isBypassed() && !$comparison->supervisor_approved_at)
                                            <div class="text-muted small fst-italic">Skipped — Manager bypass</div>
                                        @else
                                            <div class="text-muted small">—</div>
                                        @endif
                                    </div>
                                </li>

                                {{-- Step 4: Manager --}}
                                <li class="d-flex gap-3 {{ $comparison->isKaroseri() ? 'mb-3' : '' }}">
                                    <div class="text-center" style="width:28px">
                                        @if ($comparison->manager_approved_at)
                                            <span class="badge bg-success rounded-circle p-2"><i class="bi bi-check-lg"></i></span>
                                        @elseif($comparison->isRejected() && $comparison->rejectedBy?->isManager())
                                            <span class="badge bg-danger rounded-circle p-2"><i class="bi bi-x-lg"></i></span>
                                        @elseif($comparison->isPendingManager())
                                            <span class="badge bg-warning rounded-circle p-2"><i class="bi bi-hourglass-split"></i></span>
                                        @else
                                            <span class="badge bg-secondary rounded-circle p-2"><i class="bi bi-dash"></i></span>
                                        @endif
                                        @if ($comparison->isKaroseri())
                                            <div style="width:2px;height:30px;background:#e5e7eb;margin:4px auto"></div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-semibold small">Manager Approval</div>
                                        @if ($comparison->manager_approved_at)
                                            <div class="text-muted small">{{ $comparison->manager->name ?? '—' }}</div>
                                            <div class="text-muted small">{{ $comparison->manager_approved_at->format('d M Y H:i') }}</div>
                                            @if ($comparison->isBypassed())
                                                <div class="mt-1">
                                                    <span class="badge bg-warning text-dark">Manager bypass</span>
                                                </div>
                                                <div class="mt-1 p-2 bg-light rounded small fst-italic">{{ $comparison->bypass_reason }}</div>
                                            @elseif ($comparison->manager_notes)
                                                <div class="mt-1 p-2 bg-light rounded small fst-italic">{{ $comparison->manager_notes }}</div>
                                            @endif
                                        @elseif($comparison->isPendingManager())
                                            <div class="text-warning small">Waiting for approval…</div>
                                        @else
                                            <div class="text-muted small">—</div>
                                        @endif
                                    </div>
                                </li>

                                {{-- Step 5: Controller Acknowledgement (KAROSERI only) --}}
                                @if ($comparison->isKaroseri())
                                <li class="d-flex gap-3">
                                    <div class="text-center" style="width:28px">
                                        @if ($comparison->isAcknowledgedByController())
                                            <span class="badge rounded-circle p-2 text-white" style="background:#0d9488"><i class="bi bi-eye-fill"></i></span>
                                        @elseif ($comparison->isApproved())
                                            <span class="badge rounded-circle p-2 text-white" style="background:#0d9488; opacity:.4"><i class="bi bi-hourglass-split"></i></span>
                                        @else
                                            <span class="badge bg-secondary rounded-circle p-2"><i class="bi bi-dash"></i></span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-semibold small">Controller — Mengetahui</div>
                                        @if ($comparison->isAcknowledgedByController())
                                            <div class="small" style="color:#0d9488">{{ $comparison->controller->name ?? '—' }}</div>
                                            <div class="text-muted small">{{ $comparison->controller_acknowledged_at->format('d M Y H:i') }}</div>
                                            @if ($comparison->controller_notes)
                                                <div class="mt-1 p-2 bg-light rounded small fst-italic">{{ $comparison->controller_notes }}</div>
                                            @endif
                                        @elseif ($comparison->isApproved())
                                            <div class="small" style="color:#0d9488; opacity:.7">Pending acknowledgement…</div>
                                        @else
                                            <div class="text-muted small">—</div>
                                        @endif
                                    </div>
                                </li>
                                @endif
                            </ul>

                            {{-- Rejection info --}}
                            @if ($comparison->isRejected())
                                <div class="alert alert-danger mt-3 mb-0 py-2 small">
                                    <strong><i class="bi bi-x-circle me-1"></i>Rejected</strong>
                                    by {{ $comparison->rejectedBy->name ?? '—' }}
                                    on {{ $comparison->rejected_at?->format('d M Y H:i') }}<br>
                                    <em>{{ $comparison->rejection_reason }}</em>
                                </div>
                            @endif

                            {{-- Cancellation info --}}
                            @if ($comparison->isCancelled())
                                <div class="alert alert-secondary mt-3 mb-0 py-2 small">
                                    <strong><i class="bi bi-x-circle me-1"></i>Cancelled</strong>
                                    by {{ $comparison->cancelledBy->name ?? '—' }}
                                    on {{ $comparison->cancelled_at?->format('d M Y H:i') }}<br>
                                    <em>{{ $comparison->cancel_reason }}</em>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Cancel / Resubmit card for approved comparisons --}}
                    @if ($comparison->isCancellableBy(Auth::user()))
                        <div class="card border-warning-subtle shadow-sm mt-3">
                            <div class="card-header py-2 bg-light border-bottom">
                                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-gear-wide-connected me-2 text-warning"></i>Tindakan Manager (Revisi / Pembatalan)</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-2">Komparasi ini sudah berstatus <strong>Approved</strong>. Anda dapat meminta revisi kepada staff atau membatalkan komparasi secara permanen:</p>
                                
                                {{-- Tab / Toggle between Resubmit vs Cancel --}}
                                <div class="d-flex gap-2 mb-3">
                                    <button type="button" class="btn btn-sm btn-warning text-dark active flex-fill" id="btnTabResubmit" onclick="setCancelAction('resubmit')">
                                        <i class="bi bi-arrow-repeat me-1"></i> Minta Resubmit (Revisi)
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger flex-fill" id="btnTabCancel" onclick="setCancelAction('cancel')">
                                        <i class="bi bi-x-circle me-1"></i> Batal Permanen
                                    </button>
                                </div>

                                <form method="POST" action="{{ route('comparisons.cancel', $comparison) }}" id="formCancelAction">
                                    @csrf
                                    <input type="hidden" name="action_type" id="cancelActionType" value="resubmit">

                                    <div id="resubmitInfoAlert" class="alert alert-warning py-2 small mb-2">
                                        <i class="bi bi-info-circle me-1"></i> Staff pembuat (<strong>{{ $comparison->creator->name ?? 'Staff' }}</strong>) akan diminta mengajukan <strong>Comparison Revisi</strong>. Seluruh data vendor lama akan otomatis terisi agar staff tidak perlu mengetik dari nol. Komparasi saat ini diarsipkan sebagai <strong>Comparison 1</strong>.
                                    </div>
                                    <div id="cancelInfoAlert" class="alert alert-danger py-2 small mb-2 d-none">
                                        <i class="bi bi-exclamation-triangle me-1"></i> Komparasi ini akan <strong>dibatalkan total secara permanen</strong>.
                                    </div>

                                    <label class="form-label fw-semibold small" id="lblReason">Alasan Permintaan Revisi <span class="text-danger">*</span></label>
                                    <textarea name="cancel_reason" class="form-control form-control-sm mb-3" rows="2"
                                        id="txtCancelReason" placeholder="Tuliskan catatan revisi untuk staff, e.g. Mohon ganti vendor kedua, cari yang bisa TOP 30 hari…" required></textarea>

                                    <button type="submit" class="btn btn-warning w-100 fw-semibold text-dark" id="btnSubmitCancelAction">
                                        <i class="bi bi-arrow-repeat me-2"></i>Kirim Permintaan Resubmit ke Staff
                                    </button>
                                </form>
                            </div>
                        </div>

                        <script>
                            function setCancelAction(type) {
                                document.getElementById('cancelActionType').value = type;
                                const btnResubmit = document.getElementById('btnTabResubmit');
                                const btnCancel = document.getElementById('btnTabCancel');
                                const alertResubmit = document.getElementById('resubmitInfoAlert');
                                const alertCancel = document.getElementById('cancelInfoAlert');
                                const lblReason = document.getElementById('lblReason');
                                const txtReason = document.getElementById('txtCancelReason');
                                const btnSubmit = document.getElementById('btnSubmitCancelAction');

                                if (type === 'resubmit') {
                                    btnResubmit.className = 'btn btn-sm btn-warning text-dark active flex-fill';
                                    btnCancel.className = 'btn btn-sm btn-outline-danger flex-fill';

                                    alertResubmit.classList.remove('d-none');
                                    alertCancel.classList.add('d-none');

                                    lblReason.innerHTML = 'Alasan Permintaan Revisi <span class="text-danger">*</span>';
                                    txtReason.placeholder = 'Tuliskan catatan revisi untuk staff, e.g. Mohon ganti vendor kedua, cari yang bisa TOP 30 hari…';

                                    btnSubmit.className = 'btn btn-warning w-100 fw-semibold text-dark';
                                    btnSubmit.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i>Kirim Permintaan Resubmit ke Staff';
                                } else {
                                    btnCancel.className = 'btn btn-sm btn-danger text-white active flex-fill';
                                    btnResubmit.className = 'btn btn-sm btn-outline-warning text-dark flex-fill';

                                    alertCancel.classList.remove('d-none');
                                    alertResubmit.classList.add('d-none');

                                    lblReason.innerHTML = 'Alasan Pembatalan Permanen <span class="text-danger">*</span>';
                                    txtReason.placeholder = 'Tuliskan alasan pembatalan permanen…';

                                    btnSubmit.className = 'btn btn-danger w-100 fw-semibold text-white';
                                    btnSubmit.innerHTML = '<i class="bi bi-x-circle me-2"></i>Batalkan Komparasi Permanen';
                                }
                            }
                        </script>
                    @endif

                    {{-- Action card (supervisor / manager) --}}
                    @if (
                        (Auth::user()->isSupervisor() && $comparison->isPendingSupervisor()) ||
                        (Auth::user()->isManager() && $comparison->isPendingManager()))
                        <div class="card border-warning">
                            <div class="card-header py-2" style="background:#fefce8; border-color:#fde047">
                                <h6 class="mb-0 text-warning"><i class="bi bi-pencil-square me-2"></i>Your Action Required
                                </h6>
                            </div>
                            <div class="card-body">
                                {{-- Approve --}}
                                <form method="POST" action="{{ route('comparisons.approve', $comparison) }}"
                                    class="mb-3">
                                    @csrf
                                    <label class="form-label fw-semibold small">Approval Notes (optional)</label>
                                    <textarea name="notes" class="form-control form-control-sm mb-2" rows="2" placeholder="Add a comment…"></textarea>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-check-circle me-2"></i>
                                        @if (Auth::user()->isSupervisor())
                                            Approve (Send to Manager)
                                        @else
                                            Approve (Final Approval)
                                        @endif
                                    </button>
                                </form>

                                {{-- Reject --}}
                                <form method="POST" action="{{ route('comparisons.reject', $comparison) }}">
                                    @csrf
                                    <label class="form-label fw-semibold small text-danger">Rejection Reason <span
                                            class="text-danger">*</span></label>
                                    <textarea name="rejection_reason" class="form-control form-control-sm mb-2" rows="2"
                                        placeholder="State the reason for rejection…" required></textarea>
                                    <button type="submit" class="btn btn-outline-danger w-100"
                                        onclick="return confirm('Are you sure you want to reject this comparison?')">
                                        <i class="bi bi-x-circle me-2"></i>Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    {{-- Manager bypass card (skip Supervisor) --}}
                    @if ($comparison->canBypassApprove(Auth::user()))
                        <div class="card border-danger mt-3">
                            <div class="card-header py-2" style="background:#fef2f2; border-color:#ef4444">
                                <h6 class="mb-0 text-danger"><i class="bi bi-skip-forward me-2"></i>Bypass Approval</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-2">
                                    The Supervisor review step is pending but unavailable.
                                    As Manager, you may approve this comparison directly and close the workflow.
                                </p>
                                <form method="POST" action="{{ route('comparisons.bypass-approve', $comparison) }}">
                                    @csrf
                                    <label class="form-label fw-semibold small text-danger">Reason for Bypass <span
                                            class="text-danger">*</span></label>
                                    <textarea name="bypass_reason" class="form-control form-control-sm mb-2" rows="2"
                                        placeholder="e.g. Supervisor is on leave…" required></textarea>
                                    <button type="submit" class="btn btn-success w-100"
                                        onclick="return confirm('Are you sure you want to bypass Supervisor approval and give final approval?')">
                                        <i class="bi bi-check-circle me-2"></i>Bypass & Final Approve
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    {{-- Controller Acknowledgement card (KAROSERI + approved + controller role) --}}
                    @if ($comparison->isApproved() && $comparison->isKaroseri() && Auth::user()->isController() && !$comparison->isAcknowledgedByController())
                        <div class="card mt-3" style="border-color:#0d9488;">
                            <div class="card-header py-2" style="background:#ccfbf1; border-color:#0d9488;">
                                <h6 class="mb-0" style="color:#0d9488;"><i class="bi bi-eye-fill me-2"></i>Mengetahui — Your Acknowledgement Required</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-2">This KAROSERI comparison has been approved by the Manager. Please acknowledge (Mengetahui) to confirm you have noted it.</p>
                                <form method="POST" action="{{ route('comparisons.acknowledge', $comparison) }}">
                                    @csrf
                                    <label class="form-label fw-semibold small" style="color:#0d9488;">Notes <span class="text-muted small">(optional)</span></label>
                                    <textarea name="notes" class="form-control form-control-sm mb-2" rows="2" placeholder="Add any notes (optional)…"></textarea>
                                    <button type="submit" class="btn w-100 text-white" style="background:#0d9488;"
                                        onclick="return confirm('Confirm acknowledgement (Mengetahui) for this KAROSERI comparison?')">
                                        <i class="bi bi-check2-all me-2"></i>Mengetahui — Confirm Acknowledgement
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    {{-- Odoo Integration hidden --}}
                    @if (false)
                        <div class="card mt-3 {{ $comparison->odoo_synced_at ? 'border-success' : 'border-primary' }}">
                            <div
                                class="card-header py-2 {{ $comparison->odoo_synced_at ? 'bg-success' : 'bg-primary' }} text-white">
                                <h6 class="mb-0"><i class="bi bi-cloud me-2"></i>Odoo Integration</h6>
                            </div>
                            <div class="card-body">
                                @if ($comparison->odoo_synced_at)
                                    <div class="d-flex align-items-center gap-2 text-success">
                                        <i class="bi bi-cloud-check-fill fs-4"></i>
                                        <div>
                                            <div class="fw-semibold small">Posted to Odoo</div>
                                            <div class="text-muted small">
                                                {{ $comparison->odoo_synced_at->format('d M Y H:i') }}</div>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-muted small mb-3">Attach the CLVP PDF to the Odoo RFQ and post an
                                        approval note to its chatter.</p>
                                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                                        data-bs-target="#odooPostModal">
                                        <i class="bi bi-cloud-upload me-2"></i>Post to Odoo
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif {{-- /Odoo Integration hidden --}}
                </div>

                {{-- ─── Right column: RFQ + vendor comparison data ────────── --}}
                <div class="col-lg-8">

                    {{-- RFQ Summary --}}
                    @if (!empty($rfq))
                        @php
                            // Calculate system best-price vendor from vendor_prices
                            $vpVendors = $comparison->vendors ?? [];
                            $vpRows2 = $comparison->vendor_prices ?? [];
                            $vendorTotals = [];
                            foreach ($vpVendors as $vi => $v) {
                                $dType = $v['discount_type'] ?? ((float) ($v['discount'] ?? 0) > 0 ? 'percent' : ((float) ($v['fix_discount'] ?? 0) > 0 ? 'fixed' : ''));
                                $vendorDisc = (float) ($v['discount'] ?? 0);
                                $vendorFix = (float) ($v['fix_discount'] ?? 0);
                                $dRate = $vendorDisc / 100;
                                $total = 0;
                                $cantSupply = false;
                                foreach ($vpRows2 as $row) {
                                    $price = (float) ($row['prices'][$vi] ?? 0);
                                    $qty = (float) ($row['qty'] ?? 1);
                                    if ($price <= 0) {
                                        $cantSupply = true;
                                        break;
                                    }
                                    $pricelist = (float) ($row['pricelist_original'] ?? 0);
                                    // Backward-compat: if stored price ≈ pricelist, it was base price
                                    $isBasePrice = $pricelist > 0 && abs($price - $pricelist) < 2;
                                    $total += (($dType === 'percent' && $vendorDisc > 0 && $isBasePrice)
                                        ? $price * (1 - $dRate)
                                        : $price) * $qty;
                                }
                                if (!$cantSupply && $total > 0) {
                                    if ($dType === 'fixed' && $vendorFix > 0) {
                                        $total = max(0, $total - $vendorFix);
                                    }
                                    $vendorTotals[$vi] = [
                                        'name' => $v['name'],
                                        'total' => $total,
                                    ];
                                }
                            }
                            $systemBestName = null;
                            if (!empty($vendorTotals)) {
                                $bestVi = array_key_first($vendorTotals);
                                foreach ($vendorTotals as $vi => $data) {
                                    if ($data['total'] < $vendorTotals[$bestVi]['total']) {
                                        $bestVi = $vi;
                                    }
                                }
                                $systemBestName = $vendorTotals[$bestVi]['name'];
                            }
                            $choiceMatchesBest = $systemBestName && $comparison->selected_vendor === $systemBestName;
                        @endphp

                        <div class="card mb-4">
                            <div class="card-header py-2">
                                <h6 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>{{ $rfq['name'] }}</h6>
                            </div>
                            <div class="card-body py-2">
                                <div class="row g-2 small">
                                    <div class="col-6 col-md-3">
                                        <div class="text-muted">Vendor</div>
                                        <div class="fw-semibold">
                                            {{ is_array($rfq['partner_id']) ? $rfq['partner_id'][1] : '—' }}
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="text-muted">Source</div>
                                        <div class="fw-semibold">{{ ($rfq['origin'] ?? null) ?: '—' }}</div>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <div class="text-muted d-flex align-items-center gap-1">
                                            <i class="bi bi-person-check-fill text-primary"></i> Staff's Choice
                                        </div>
                                        <div class="fw-semibold text-primary">{{ $comparison->selected_vendor ?: '—' }}
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
                                            <div
                                                class="fw-semibold {{ $choiceMatchesBest ? 'text-success' : 'text-danger' }}">
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
                                        <div class="text-muted">Total</div>
                                        <div class="fw-semibold">
                                            {{ is_array($rfq['currency_id'] ?? null) ? $rfq['currency_id'][1] : 'IDR' }}
                                            {{ number_format($rfq['amount_total'] ?? 0, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                                @if ($comparison->notes)
                                    <div class="mt-2 p-2 bg-light rounded small">
                                        <strong>Notes:</strong> {{ $comparison->notes }}
                                    </div>
                                @endif

                                {{-- Vendors selected for this comparison --}}
                                @if (!empty($comparison->vendors))
                                    <div class="mt-2">
                                        <div class="text-muted small fw-semibold mb-1">Vendors Selected for Comparison
                                            ({{ count($comparison->vendors) }}):</div>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach ($comparison->vendors as $v)
                                                @php
                                                    $isStaffChoice = $v['name'] === $comparison->selected_vendor;
                                                    $isSysBest = $v['name'] === $systemBestName;
                                                    $isLocal = in_array(strtolower(trim($v['name'])), $localSupplierNames ?? []);
                                                @endphp
                                                <span
                                                    class="badge d-inline-flex align-items-center gap-1
                                                    {{ $isStaffChoice && $isSysBest ? 'bg-success' : ($isStaffChoice ? 'bg-primary' : ($isSysBest ? 'bg-warning text-dark' : 'bg-light text-dark border')) }}">
                                                    @if ($isStaffChoice)
                                                        <i class="bi bi-person-check-fill" title="Staff's choice"></i>
                                                    @endif
                                                    @if ($isSysBest && !$isStaffChoice)
                                                        <i class="bi bi-cpu-fill" title="System recommendation"></i>
                                                    @endif
                                                    {{ $v['name'] }}
                                                    @if ($isLocal)
                                                        <span class="ms-1" style="font-size:.65rem; color:#15803d; background:#fff; border:1px solid #15803d; border-radius:.2rem; padding:0 3px; display:inline-block">Local</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                        <div class="text-muted small mt-1 d-flex gap-3 flex-wrap">
                                            <span><i class="bi bi-person-check-fill text-primary me-1"></i>Blue = Staff's
                                                choice</span>
                                            <span><i class="bi bi-cpu-fill text-warning me-1"></i>Yellow = System best
                                                price</span>
                                            <span><i class="bi bi-check-circle-fill text-success me-1"></i>Green = Both
                                                match</span>
                                            <span><span style="font-size:.65rem; color:#15803d; background:#fff; border:1px solid #15803d; border-radius:.2rem; padding:0 3px; display:inline-block">Local</span> = Master supplier (not in Odoo)</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- ── Legend ── --}}
                        <div class="d-flex gap-3 mb-3 flex-wrap align-items-center">
                            <span class="fw-semibold small">Legend:</span>
                            <span class="badge price-best px-2 py-1">&#9733; Best Price</span>
                            <span class="badge price-current px-2 py-1">&#9830; Current RFQ Vendor</span>
                            <span class="badge price-worst px-2 py-1">&#9660; Highest Price</span>
                            <span class="badge bg-primary px-2 py-1">&#128197; Latest Purchase</span>
                            <span class="ms-auto text-muted small">
                                <i class="bi bi-info-circle me-1"></i>
                                Benchmarks (Latest, Best Price per tahun, Highest Price) menggunakan harga nett tax exclude per pc.
                            </span>
                        </div>

                        {{-- Per-product vendor comparison (condensed) --}}
                        @php $currency = is_array($rfq['currency_id'] ?? null) ? $rfq['currency_id'][1] : 'IDR'; @endphp
                        @foreach ($rfq['lines'] as $line)
                            @php
                                if (!is_array($line['product_id'])) {
                                    continue;
                                }
                                $productId = $line['product_id'][0];
                                $productName = $line['product_id'][1];
                                $uom = is_array($line['product_uom']) ? $line['product_uom'][1] : '';
                                $rfqVendorId = is_array($rfq['partner_id']) ? $rfq['partner_id'][0] : null;
                                $rfqVendorNm = is_array($rfq['partner_id']) ? $rfq['partner_id'][1] : '—';
                                $historyKey = $productId . '::' . \App\Models\VendorComparison::normalizeDescription($line['name'] ?? '');
                                $vendorRows = $history[$historyKey] ?? $history[$productId] ?? [];
                                $otherRows = array_values(array_values($vendorRows));
                                $totalHistoryCount = count($otherRows);

                                // Helper: compute nett price per pc (tax exclude after discount)
                                $getHistNett = function($r) {
                                    if (isset($r['nett_price']) && (float)$r['nett_price'] > 0) {
                                        return (float)$r['nett_price'];
                                    }
                                    $qty = (float)($r['product_qty'] ?? 1);
                                    $subtotal = (float)($r['price_subtotal'] ?? 0);
                                    if ($qty > 0 && $subtotal > 0) {
                                        return round($subtotal / $qty, 2);
                                    }
                                    $pu = (float)($r['price_unit'] ?? 0);
                                    $disc = (float)($r['discount'] ?? 0);
                                    if ($pu > 0 && $disc > 0) {
                                        return round($pu * (1 - ($disc / 100)), 2);
                                    }
                                    return $pu;
                                };

                                // Current RFQ line calculations
                                $currentLineNett = (float)($line['nett_unit_price'] ?? (
                                    ($line['product_qty'] > 0 && !empty($line['price_subtotal'])) 
                                        ? round($line['price_subtotal'] / $line['product_qty'], 2) 
                                        : ((float)$line['price_unit'] * (1 - (((float)($line['discount'] ?? 0)) / 100)))
                                ));
                                $currentLinePricelist = (float)($line['price_unit'] ?? 0);
                                $currentLineDiscount = (float)($line['discount'] ?? 0);
                                $currentHasDisc = ($currentLineDiscount > 0) || ($currentLinePricelist > 0 && abs($currentLineNett - $currentLinePricelist) > 0.01);

                                // 1. Most recent purchase (by date desc)
                                $byDate = $otherRows;
                                usort($byDate, fn($a, $b) => strtotime($b['date']) <=> strtotime($a['date']));
                                $mostRecentRow = $byDate[0] ?? null;

                                // 2. Best Price per Tahun (grouped by calendar year)
                                $historyByYear = [];
                                foreach ($otherRows as $r) {
                                    $yr = $r['year'] ?? (!empty($r['date']) ? (int) date('Y', strtotime($r['date'])) : null);
                                    if ($yr) {
                                        $historyByYear[$yr][] = $r;
                                    }
                                }
                                krsort($historyByYear); // Sort years descending (2026, 2025, 2024...)

                                $bestPriceByYear = []; // [ year => row ]
                                foreach ($historyByYear as $yr => $rowsInYear) {
                                    usort($rowsInYear, function($a, $b) use ($getHistNett) {
                                        $pa = $getHistNett($a);
                                        $pb = $getHistNett($b);
                                        if ($pa == $pb) {
                                            return strtotime($b['date']) <=> strtotime($a['date']);
                                        }
                                        return $pa <=> $pb;
                                    });
                                    if (!empty($rowsInYear[0])) {
                                        $bestPriceByYear[$yr] = $rowsInYear[0];
                                    }
                                }

                                // Overall best price row across all history
                                $byPriceAsc = $otherRows;
                                usort($byPriceAsc, function($a, $b) use ($getHistNett) {
                                    $pa = $getHistNett($a);
                                    $pb = $getHistNett($b);
                                    if ($pa == $pb) {
                                        return strtotime($b['date']) <=> strtotime($a['date']);
                                    }
                                    return $pa <=> $pb;
                                });
                                $bestPriceRow = $byPriceAsc[0] ?? null;

                                // 3. Highest price purchase in history (highest nett price, most recent date on tie)
                                $byPriceDesc = $otherRows;
                                usort($byPriceDesc, function($a, $b) use ($getHistNett) {
                                    $pa = $getHistNett($a);
                                    $pb = $getHistNett($b);
                                    if ($pa == $pb) {
                                        return strtotime($b['date']) <=> strtotime($a['date']);
                                    }
                                    return $pb <=> $pa;
                                });
                                $worstPriceRow = $byPriceDesc[0] ?? null;

                                // Pool valid prices to determine genuine variation
                                $pricePool = array_filter(array_merge(
                                    [$currentLineNett],
                                    array_map($getHistNett, $otherRows)
                                ), fn($p) => $p !== null && $p > 0);

                                $uniquePrices = array_unique($pricePool);
                                $hasVariation = count($uniquePrices) > 1;
                                $globalMin = $hasVariation ? min($uniquePrices) : null;
                                $globalMax = $hasVariation ? max($uniquePrices) : null;

                                // Current RFQ state
                                $isCurrentBest = $hasVariation && $currentLineNett == $globalMin;
                                $isCurrentWorst = $hasVariation && $currentLineNett == $globalMax;
                                $currentClass = 'price-current';
                                if ($isCurrentBest) {
                                    $currentClass = 'price-best';
                                } elseif ($isCurrentWorst) {
                                    $currentClass = 'price-worst';
                                }

                                // Latest Purchase state
                                $latestNett = $mostRecentRow ? $getHistNett($mostRecentRow) : 0;
                                $latestYear = $mostRecentRow ? ($mostRecentRow['year'] ?? (!empty($mostRecentRow['date']) ? (int) date('Y', strtotime($mostRecentRow['date'])) : null)) : null;
                                $isLatestBestForYear = $latestYear && isset($bestPriceByYear[$latestYear]) && ($bestPriceByYear[$latestYear]['order_id'] ?? null) === ($mostRecentRow['order_id'] ?? null);
                                $isLatestBestOverall = $hasVariation && $mostRecentRow && $latestNett == $globalMin;
                                $isLatestWorst = $hasVariation && $mostRecentRow && $latestNett == $globalMax;

                                // Track rendered POs to prevent duplicate rows
                                $renderedOrderIds = [];
                                if ($mostRecentRow && !empty($mostRecentRow['order_id'])) {
                                    $renderedOrderIds[] = $mostRecentRow['order_id'];
                                }

                                // Determine standalone Best Price per year rows needed
                                $standaloneYearlyBestRows = [];
                                foreach ($bestPriceByYear as $yr => $bRow) {
                                    $bOrderId = $bRow['order_id'] ?? null;
                                    if ($bOrderId && in_array($bOrderId, $renderedOrderIds)) {
                                        continue;
                                    }
                                    $standaloneYearlyBestRows[$yr] = $bRow;
                                    if ($bOrderId) {
                                        $renderedOrderIds[] = $bOrderId;
                                    }
                                }

                                // Determine whether standalone Highest Price row is needed
                                $showWorstRow = $hasVariation 
                                    && $worstPriceRow 
                                    && !empty($worstPriceRow['order_id'])
                                    && !in_array($worstPriceRow['order_id'], $renderedOrderIds)
                                    && $getHistNett($worstPriceRow) > $globalMin;
                            @endphp
                            <div class="card product-item-card mb-4 shadow-sm" style="border: 2px solid #94a3b8; border-radius: 8px; overflow: hidden;">
                                <div class="card-header py-2 d-flex align-items-center gap-2 flex-wrap" style="background-color: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
                                    <i class="bi bi-box-seam text-secondary"></i>
                                    <span class="badge bg-secondary">{{ $productName }}</span>
                                    <span class="fw-semibold text-dark">{{ $line['name'] }}</span>
                                    <span class="badge bg-white text-dark border">{{ $line['product_qty'] }}
                                        {{ $uom }}</span>
                                    <span class="ms-auto text-muted small">
                                        RFQ Unit Price:&nbsp;
                                        <strong @class([
                                            'text-success' => $isCurrentBest,
                                            'text-danger'  => $isCurrentWorst,
                                        ])>
                                            {{ $currency }} {{ number_format($currentLineNett, 2, ',', '.') }}
                                        </strong>
                                        @if ($currentHasDisc && $currentLineDiscount > 0)
                                            <span class="text-danger" style="font-size: 0.72rem;">(-{{ rtrim(rtrim(number_format($currentLineDiscount, 2, ',', '.'), '0'), ',') }}%)</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="card-body p-0">
                                    {{-- TABLE 1: Current RFQ Quote --}}
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm align-middle mb-0">
                                            <thead>
                                                <tr class="table-light text-muted small text-nowrap">
                                                    <th class="ps-3" style="width: 30%;">Current Vendor</th>
                                                    <th class="text-center" style="width: 16%;">Quoted Price</th>
                                                    <th class="text-center" style="width: 6%;">Qty</th>
                                                    <th class="text-center" style="width: 6%;">UoM</th>
                                                    <th style="width: 15%;">RFQ / PO</th>
                                                    <th style="width: 14%;">Date</th>
                                                    <th class="text-center" style="width: 13%;">Note</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="{{ $currentClass }}">
                                                    <td class="ps-3 fw-semibold">
                                                        <i class="bi bi-star-fill text-warning me-1"></i>{{ $rfqVendorNm }}
                                                    </td>
                                                    <td class="text-center fw-bold">
                                                        <div>{{ $currency }} {{ number_format($currentLineNett, 2, ',', '.') }}</div>
                                                        @if ($currentHasDisc)
                                                            <div class="text-muted small fw-normal" style="font-size: 0.7rem;">
                                                                @if ($currentLineDiscount > 0)
                                                                    <span class="text-danger fw-semibold">Disc. {{ rtrim(rtrim(number_format($currentLineDiscount, 2, ',', '.'), '0'), ',') }}%</span> &bull; 
                                                                @endif
                                                                List: {{ number_format($currentLinePricelist, 0, ',', '.') }}
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{ $line['product_qty'] }}</td>
                                                    <td class="text-center">{{ $uom }}</td>
                                                    <td class="text-muted">{{ $rfq['name'] }}</td>
                                                    <td class="text-muted">
                                                        {{ \Carbon\Carbon::parse($rfq['date_order'])->format('d M Y') }}
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-warning text-dark">Current RFQ</span>
                                                        @if ($isCurrentBest)
                                                            <span class="badge bg-success">Best Price</span>
                                                        @elseif ($isCurrentWorst)
                                                            <span class="badge bg-danger">Highest Price</span>
                                                        @endif
                                                    </td>
                                                </tr>

                                                {{-- Divider: Purchase History --}}
                                                <tr class="border-top border-bottom" style="background-color: #f8fafc;">
                                                    <td colspan="7" class="px-3 py-2">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <i class="bi bi-clock-history text-primary fs-6"></i>
                                                                <span class="fw-bold text-dark" style="font-size: 0.82rem; letter-spacing: 0.02em;">Purchase History</span>
                                                                <span class="badge bg-secondary-subtle text-secondary border" style="font-size: 0.68rem; font-weight: 500;">Odoo</span>
                                                            </div>
                                                            @if ($totalHistoryCount > 0)
                                                                <span class="badge bg-white text-secondary border font-monospace shadow-sm" style="font-size: 0.72rem; font-weight: 500;">
                                                                    <i class="bi bi-receipt me-1"></i>{{ $totalHistoryCount }} past {{ Str::plural('purchase', $totalHistoryCount) }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>

                                                {{-- Historical Table Header Row --}}
                                                <tr class="table-light text-muted small text-nowrap">
                                                    <th class="ps-3">Historical Vendor</th>
                                                    <th class="text-center">Unit Price (Nett)</th>
                                                    <th class="text-center">Qty</th>
                                                    <th class="text-center">UoM</th>
                                                    <th>Past PO</th>
                                                    <th>Purchase Date</th>
                                                    <th class="text-center">Note</th>
                                                </tr>

                                                {{-- Row 2: Latest Purchase --}}
                                                @if ($mostRecentRow)
                                                    @php
                                                        $rNett = $getHistNett($mostRecentRow);
                                                        $rList = (float)($mostRecentRow['price_unit'] ?? 0);
                                                        $rDisc = (float)($mostRecentRow['discount'] ?? 0);
                                                        $rHasDisc = ($rDisc > 0) || ($rList > 0 && abs($rNett - $rList) > 0.01);
                                                    @endphp
                                                    <tr class="table-info">
                                                        <td class="ps-3 fw-semibold" style="box-shadow: inset 4px 0 0 #0d6efd;">{{ $mostRecentRow['vendor_name'] }}</td>
                                                        <td class="text-center fw-semibold">
                                                            <div>{{ $currency }} {{ number_format($rNett, 2, ',', '.') }}</div>
                                                            @if ($rHasDisc)
                                                                <div class="text-muted small fw-normal" style="font-size: 0.7rem;">
                                                                    @if ($rDisc > 0)
                                                                        <span class="text-danger fw-semibold">Disc. {{ rtrim(rtrim(number_format($rDisc, 2, ',', '.'), '0'), ',') }}%</span> &bull; 
                                                                    @endif
                                                                    List: {{ number_format($rList, 0, ',', '.') }}
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">{{ $mostRecentRow['product_qty'] }}</td>
                                                        <td class="text-center">{{ $mostRecentRow['uom'] }}</td>
                                                        <td>
                                                            @if (!empty($mostRecentRow['order_id']))
                                                                <a href="{{ route('rfq.show', $mostRecentRow['order_id']) }}" class="text-decoration-none small">
                                                                    {{ $mostRecentRow['po_name'] }}
                                                                </a>
                                                            @else
                                                                <span class="text-muted small">{{ $mostRecentRow['po_name'] }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="fw-semibold">
                                                            {{ \Carbon\Carbon::parse($mostRecentRow['date'])->format('d M Y H:i') }}
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-primary">Latest Purchase</span>
                                                            @if ($isLatestBestForYear)
                                                                <span class="badge bg-success">&#9733; Best Price {{ $latestYear }}</span>
                                                            @elseif ($isLatestBestOverall)
                                                                <span class="badge bg-success">&#9733; Best Price</span>
                                                            @elseif ($isLatestWorst)
                                                                <span class="badge bg-danger">&#9660; Highest Price</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endif

                                                {{-- Row 3+: Standalone Best Price per Year (for years distinct from Latest Purchase) --}}
                                                @foreach ($standaloneYearlyBestRows as $yr => $yearlyRow)
                                                    @php
                                                        $yNett = $getHistNett($yearlyRow);
                                                        $yList = (float)($yearlyRow['price_unit'] ?? 0);
                                                        $yDisc = (float)($yearlyRow['discount'] ?? 0);
                                                        $yHasDisc = ($yDisc > 0) || ($yList > 0 && abs($yNett - $yList) > 0.01);
                                                    @endphp
                                                    <tr class="price-best">
                                                        <td class="ps-3 fw-semibold">{{ $yearlyRow['vendor_name'] }}</td>
                                                        <td class="text-center fw-semibold">
                                                            <div>{{ $currency }} {{ number_format($yNett, 2, ',', '.') }}</div>
                                                            @if ($yHasDisc)
                                                                <div class="text-muted small fw-normal" style="font-size: 0.7rem;">
                                                                    @if ($yDisc > 0)
                                                                        <span class="text-danger fw-semibold">Disc. {{ rtrim(rtrim(number_format($yDisc, 2, ',', '.'), '0'), ',') }}%</span> &bull; 
                                                                    @endif
                                                                    List: {{ number_format($yList, 0, ',', '.') }}
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">{{ $yearlyRow['product_qty'] }}</td>
                                                        <td class="text-center">{{ $yearlyRow['uom'] }}</td>
                                                        <td>
                                                            @if (!empty($yearlyRow['order_id']))
                                                                <a href="{{ route('rfq.show', $yearlyRow['order_id']) }}" class="text-decoration-none small">
                                                                    {{ $yearlyRow['po_name'] }}
                                                                </a>
                                                            @else
                                                                <span class="text-muted small">{{ $yearlyRow['po_name'] }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-muted">
                                                            {{ \Carbon\Carbon::parse($yearlyRow['date'])->format('d M Y H:i') }}
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-success">&#9733; Best Price {{ $yr }}</span>
                                                            @if ($yNett == $globalMin && count($bestPriceByYear) > 1)
                                                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.65rem;">All-Time Best</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach

                                                {{-- Row 4: Highest Price (shown if distinct from Latest and all Best Price rows) --}}
                                                @if ($showWorstRow)
                                                    @php
                                                        $wNett = $getHistNett($worstPriceRow);
                                                        $wList = (float)($worstPriceRow['price_unit'] ?? 0);
                                                        $wDisc = (float)($worstPriceRow['discount'] ?? 0);
                                                        $wHasDisc = ($wDisc > 0) || ($wList > 0 && abs($wNett - $wList) > 0.01);
                                                    @endphp
                                                    <tr class="price-worst">
                                                        <td class="ps-3">{{ $worstPriceRow['vendor_name'] }}</td>
                                                        <td class="text-center">
                                                            <div>{{ $currency }} {{ number_format($wNett, 2, ',', '.') }}</div>
                                                            @if ($wHasDisc)
                                                                <div class="text-muted small fw-normal" style="font-size: 0.7rem;">
                                                                    @if ($wDisc > 0)
                                                                        <span class="text-danger fw-semibold">Disc. {{ rtrim(rtrim(number_format($wDisc, 2, ',', '.'), '0'), ',') }}%</span> &bull; 
                                                                    @endif
                                                                    List: {{ number_format($wList, 0, ',', '.') }}
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">{{ $worstPriceRow['product_qty'] }}</td>
                                                        <td class="text-center">{{ $worstPriceRow['uom'] }}</td>
                                                        <td>
                                                            @if (!empty($worstPriceRow['order_id']))
                                                                <a href="{{ route('rfq.show', $worstPriceRow['order_id']) }}" class="text-decoration-none small">
                                                                    {{ $worstPriceRow['po_name'] }}
                                                                </a>
                                                            @else
                                                                <span class="text-muted small">{{ $worstPriceRow['po_name'] }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-muted">
                                                            {{ \Carbon\Carbon::parse($worstPriceRow['date'])->format('d M Y H:i') }}
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-danger">&#9660; Highest Price</span>
                                                        </td>
                                                    </tr>
                                                @endif

                                                @if (!$mostRecentRow)
                                                    <tr>
                                                        <td colspan="7" class="text-center text-muted py-3 small">
                                                            <i class="bi bi-clock-history me-1"></i>No past purchase history found in Odoo for this product.
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-warning">Could not load RFQ data from Odoo.</div>
                    @endif

                </div>{{-- end right column --}}
            </div>{{-- end row --}}
        </div>{{-- end tabApproval --}}

        {{-- ════════════════════════════════════════════════════════ --}}
        {{-- TAB 2: CLVP DOCUMENT                                    --}}
        {{-- ════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="tabClvp">

            @if (!Auth::user()->isViewer())
            <div class="d-flex justify-content-end mb-3 gap-2">
                <a href="{{ route('comparisons.pdf', $comparison) }}" target="_blank" class="btn btn-danger btn-sm">
                    <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF
                </a>
                <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i>Print
                </button>
            </div>
            @endif

            {{-- Post to Odoo modal hidden --}}
            @if (false)
                <div class="modal fade" id="odooPostModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('comparisons.odoo-post', $comparison) }}"
                                id="odooPostForm">
                                @csrf
                                <div class="modal-header">
                                    <h6 class="modal-title">
                                        <i class="bi bi-cloud-upload me-2 text-primary"></i>Post to Odoo —
                                        {{ $comparison->po_name }}
                                    </h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="text-muted small mb-3">
                                        This will attach the CLVP PDF to the Odoo RFQ and post a log note to its chatter.
                                        The note will include the recommended vendor, approval details, and your message
                                        below.
                                    </p>
                                    <div class="bg-light border rounded p-3 mb-3 small">
                                        <div class="fw-semibold mb-1 text-muted">Auto-generated summary:</div>
                                        <div>✅ CLVP Approved — Recommended:
                                            <strong>{{ $comparison->selected_vendor }}</strong>
                                        </div>
                                        <div>Approved by: <strong>{{ $comparison->manager->name ?? '—' }}</strong>
                                            on {{ $comparison->manager_approved_at?->format('d M Y') }}</div>
                                        <div>Submitted by: <strong>{{ $comparison->creator->name ?? '—' }}</strong></div>
                                    </div>
                                    <label class="form-label fw-semibold">Additional note <span
                                            class="text-muted fw-normal">(optional)</span></label>
                                    <textarea name="note" id="odooNoteInput" class="form-control" rows="4"
                                        placeholder="Write any extra context to include in the Odoo log note, e.g. 'PO to be issued by end of week' or special instructions…"></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" id="odooPostBtn" class="btn btn-primary btn-sm">
                                        <i class="bi bi-cloud-upload me-1"></i>Post to Odoo
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <script>
                    document.getElementById('odooPostForm').addEventListener('submit', function() {
                        const btn = document.getElementById('odooPostBtn');
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Posting…';
                    });
                </script>
            @endif {{-- /Post to Odoo modal hidden --}}

            {{-- ── CLVP Document ── --}}
            <div id="clvpDocument" class="bg-white border p-4"
                style="font-family: Arial, sans-serif; font-size:12px; color:#000;">

                {{-- Header --}}
                <div class="text-center mb-3">
                    <div style="font-size:15px; font-weight:bold; letter-spacing:1px;">
                        COMPARISON LOCAL VENDOR PRICE ( CLVP )
                    </div>
                </div>

                <div class="d-flex align-items-start mb-3 gap-4">
                    {{-- Logo area --}}
                    <div style="min-width:90px;">
                        <img src="{{ asset('logo.png') }}" style="width:140px; height:auto;">
                    </div>

                    {{-- Category checkboxes --}}
                    <div class="d-flex gap-4 align-items-center" style="font-size:12px;">
                        @php
                            $catMap = [
                                'unit_baru' => 'Unit Baru',
                                'aksesoris' => 'Aksesoris Mobil',
                                'sparepart' => 'Sparepart',
                                'umum' => 'Umum',
                            ];
                        @endphp
                        @foreach ($catMap as $val => $lbl)
                            <span style="display:inline-flex; align-items:center; gap:4px;">
                                <span
                                    style="display:inline-block; width:14px; height:14px; border:1.5px solid #000; text-align:center; line-height:12px; font-size:11px; font-weight:bold;">
                                    {{ $comparison->category === $val ? 'V' : '' }}
                                </span>
                                {{ $lbl }}
                            </span>
                        @endforeach
                    </div>
                </div>

                {{-- CLVP Table --}}
                @php
                    $vendors = $comparison->vendors ?? [];
                    $vpRows = $comparison->vendor_prices ?? [];
                    $currency = 'Rp';
                @endphp

                <table style="width:100%; border-collapse:collapse; font-size:11px;">
                    <thead>
                        {{-- Row 1: fixed headers + "MITRA BISNIS" spanning vendor cols --}}
                        <tr>
                            <th rowspan="2"
                                style="border:1px solid #000; padding:4px 6px; text-align:center; width:28px;">No</th>
                            <th rowspan="2" style="border:1px solid #000; padding:4px 6px; text-align:center;">Nama
                                Barang</th>
                            <th rowspan="2"
                                style="border:1px solid #000; padding:4px 6px; text-align:center; width:60px;">Kode Barang
                            </th>
                            <th rowspan="2"
                                style="border:1px solid #000; padding:4px 6px; text-align:center; width:36px;">Qty</th>
                            <th rowspan="2"
                                style="border:1px solid #000; padding:4px 6px; text-align:center; width:36px;">UoM</th>
                            <th rowspan="2"
                                style="border:1px solid #000; padding:4px 6px; text-align:center; width:90px;">Pricelist
                                Original</th>
                            @if (!empty($vendors))
                                <th colspan="{{ count($vendors) }}"
                                    style="border:1px solid #000; padding:4px 6px; text-align:center; background:#f0f0f0;">
                                    MITRA BISNIS
                                </th>
                            @endif
                        </tr>
                        {{-- Row 2: individual vendor names + PIC/TELP --}}
                        <tr>
                            @foreach ($vendors as $vi => $v)
                                @php
                                    $isRec = ($v['name'] ?? '') === $comparison->selected_vendor;
                                @endphp
                                <th
                                    style="border:1px solid #000; padding:4px 6px; text-align:center; min-width:110px;
                                {{ $isRec ? 'background:#d4edda;' : '' }}">
                                    <div style="font-weight:bold;">{{ $v['name'] ?? '—' }}</div>
                                    @if (!empty($v['pic']))
                                        <div style="font-weight:normal; font-size:10px;">PIC : {{ $v['pic'] }}</div>
                                    @endif
                                    @if (!empty($v['phone']))
                                        <div style="font-weight:normal; font-size:10px;">TELP : {{ $v['phone'] }}</div>
                                    @endif
                                    @if ($isRec)
                                        <div style="font-size:9px; color:#155724; font-weight:bold;">✓ Rekomendasi</div>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Build ordered RFQ line index for product name lookup --}}
                        @php
                            $rfqProductLines = [];
                            if (!empty($rfq['lines'])) {
                                $li = 0;
                                foreach ($rfq['lines'] as $l) {
                                    if (!is_array($l['product_id'])) {
                                        continue;
                                    }
                                    $rfqProductLines[$li++] = $l;
                                }
                            }
                        @endphp
                        {{-- Product rows --}}
                        @foreach ($vpRows as $ri => $row)
                            @php
                                $rl = $rfqProductLines[$ri] ?? null;
                                $pCode = $rl ? $rl['product_code'] ?? '' : $row['product_code'] ?? '';
                                $pName = $rl ? $rl['name'] : $row['product_name'] ?? '';
                            @endphp
                            <tr>
                                <td style="border:1px solid #000; padding:4px 6px; text-align:center;">{{ $ri + 1 }}
                                </td>
                                <td style="border:1px solid #000; padding:4px 6px;">
                                    @if (!empty($pCode))
                                        <span
                                            style="background:#6c757d;color:#fff;padding:1px 4px;border-radius:3px;font-size:8px;margin-right:3px;">{{ $pCode }}</span>
                                    @endif
                                    {{ $pName }}
                                </td>
                                <td
                                    style="border:1px solid #000; padding:4px 6px; text-align:center; color:#888; font-size:10px;">
                                    {{ $pCode }}
                                </td>
                                <td style="border:1px solid #000; padding:4px 6px; text-align:center;">
                                    {{ $row['qty'] ?? '' }}</td>
                                <td style="border:1px solid #000; padding:4px 6px; text-align:center;">
                                    {{ $row['uom'] ?? '' }}</td>
                                <td style="border:1px solid #000; padding:4px 6px; text-align:right;">
                                    {{ number_format($row['pricelist_original'] ?? 0, 0, ',', '.') }}
                                </td>
                                @foreach ($vendors as $vi => $v)
                                    @php
                                        $price = $row['prices'][$vi] ?? null;
                                        $isRec = ($v['name'] ?? '') === $comparison->selected_vendor;
                                        $pricelist = (float) ($row['pricelist_original'] ?? 0);
                                        $dType = $v['discount_type'] ?? (!empty($v['discount']) ? 'percent' : ((float) ($v['fix_discount'] ?? 0) > 0 ? 'fixed' : ''));
                                        preg_match('/[\d.]+/', $v['discount'] ?? '', $dm);
                                        $dRate = isset($dm[0]) ? (float) $dm[0] / 100 : 0;
                                        // Backward-compat: old sparepart stored base price. If price ≈ pricelist, apply discount.
                                        $isBasePrice = $pricelist > 0 && $price !== null && abs((float)$price - $pricelist) < 2;
                                        $displayPrice = ($dType === 'percent' && $isBasePrice)
                                            ? (float)$price * (1 - $dRate)
                                            : (float)$price;
                                    @endphp
                                    <td
                                        style="border:1px solid #000; padding:4px 6px; text-align:right; {{ $isRec ? 'background:#f0fff4;' : '' }}">
                                        @if ($price === null || $price === '' || $price == 0)
                                            <span style="color:#888; font-style:italic;">Tidak Menjual Barang</span>
                                        @else
                                            {{ $currency }}{{ number_format($displayPrice, 0, ',', '.') }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach

                        {{-- Disc row: right after last product, before spacers --}}
                        @if (collect($vendors)->filter(fn($v) => !empty($v['discount']) || (float) ($v['fix_discount'] ?? 0) > 0)->count() > 0)
                            <tr>
                                <td style="border:1px solid #000; padding:4px 6px;"></td>
                                <td style="border:1px solid #000;"></td>
                                <td style="border:1px solid #000;"></td>
                                <td style="border:1px solid #000;"></td>
                                <td style="border:1px solid #000;"></td>
                                <td style="border:1px solid #000;"></td>
                                @foreach ($vendors as $v)
                                    @php
                                        $isRec = ($v['name'] ?? '') === $comparison->selected_vendor;
                                        $dType = $v['discount_type'] ?? (!empty($v['discount']) ? 'percent' : ((float) ($v['fix_discount'] ?? 0) > 0 ? 'fixed' : ''));
                                    @endphp
                                    <td
                                        style="border:1px solid #000; padding:4px 6px; text-align:center; font-size:10px; font-weight:bold; color:#c0392b; {{ $isRec ? 'background:#f0fff4;' : '' }}">
                                        @if ($dType === 'percent' && !empty($v['discount']))
                                            Disc {{ rtrim($v['discount'], '%') }}%
                                        @elseif ($dType === 'fixed' && (float) ($v['fix_discount'] ?? 0) > 0)
                                            Fix Disc {{ number_format((float) $v['fix_discount'], 0, ',', '.') }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endif

                        {{-- Empty spacer rows for aesthetic (like original form) --}}
                        @for ($i = 0; $i < max(0, 6 - count($vpRows)); $i++)
                            <tr>
                                <td style="border:1px solid #000; padding:4px 6px;">&nbsp;</td>
                                <td style="border:1px solid #000;"></td>
                                <td style="border:1px solid #000;"></td>
                                <td style="border:1px solid #000;"></td>
                                <td style="border:1px solid #000;"></td>
                                <td style="border:1px solid #000;"></td>
                                @foreach ($vendors as $v)
                                    <td style="border:1px solid #000;"></td>
                                @endforeach
                            </tr>
                        @endfor

                        {{-- TOTAL row --}}
                        <tr style="font-weight:bold; background:#f9f9f9;">
                            <td colspan="5"
                                style="border:1px solid #000; padding:4px 6px; text-align:right; font-weight:bold;">TOTAL
                            </td>
                            <td style="border:1px solid #000; padding:4px 6px; text-align:right;">
                                @php
                                    $origTotal = 0;
                                    foreach ($vpRows as $row) {
                                        $origTotal += (float)($row['pricelist_original'] ?? 0) * (float)($row['qty'] ?? 1);
                                    }
                                @endphp
                                {{ $currency }}{{ number_format($origTotal, 0, ',', '.') }}
                            </td>
                            @foreach ($vendors as $vi => $v)
                                @php
                                    $dType = $v['discount_type'] ?? ((float) ($v['discount'] ?? 0) > 0 ? 'percent' : ((float) ($v['fix_discount'] ?? 0) > 0 ? 'fixed' : ''));
                                    $vendorDisc = (float) ($v['discount'] ?? 0);
                                    $vendorFix = (float) ($v['fix_discount'] ?? 0);
                                    $dRate = $vendorDisc / 100;
                                    $vTotal = 0;
                                    foreach ($vpRows as $row) {
                                        $p = (float) ($row['prices'][$vi] ?? 0);
                                        $qty = (float) ($row['qty'] ?? 1);
                                        $pricelist = (float) ($row['pricelist_original'] ?? 0);
                                        // Backward-compat: if stored price ≈ pricelist, it was base price
                                        $isBasePrice = $pricelist > 0 && abs($p - $pricelist) < 2;
                                        $finalPrice = ($dType === 'percent' && $vendorDisc > 0 && $isBasePrice)
                                            ? $p * (1 - $dRate)
                                            : $p;
                                        $vTotal += $finalPrice * $qty;
                                    }
                                    if ($dType === 'fixed' && $vendorFix > 0) {
                                        $vTotal = max(0, $vTotal - $vendorFix);
                                    }
                                    $isRec = ($v['name'] ?? '') === $comparison->selected_vendor;
                                @endphp
                                <td
                                    style="border:1px solid #000; padding:4px 6px; text-align:right; font-weight:bold; {{ $isRec ? 'background:#f0fff4;' : '' }}">
                                    {{ $currency }}{{ number_format($vTotal, 0, ',', '.') }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- Availability row --}}
                        <tr>
                            <td colspan="6" style="border:1px solid #000; padding:3px 6px;"></td>
                            @foreach ($vendors as $v)
                                @php $isRec = ($v['name'] ?? '') === $comparison->selected_vendor; @endphp
                                <td
                                    style="border:1px solid #000; padding:3px 6px; font-size:10px; {{ $isRec ? 'background:#f0fff4;' : '' }}">
                                    @php
                                        $isReady = ($v['availability'] ?? '') === 'ready' || !empty($v['ready']);
                                        $isIndent = ($v['availability'] ?? '') === 'indent' || !empty($v['indent']);
                                    @endphp
                                    <div style="display:flex; align-items:center; gap:4px; margin-bottom:2px;">
                                        <span
                                            style="display:inline-block; width:12px; height:12px; border:1px solid #000; text-align:center; line-height:11px; font-size:10px;">
                                            {{ $isReady ? 'V' : '' }}
                                        </span> Ready
                                    </div>
                                    <div style="display:flex; align-items:center; gap:4px;">
                                        <span
                                            style="display:inline-block; width:12px; height:12px; border:1px solid #000; text-align:center; line-height:11px; font-size:10px;">
                                            {{ $isIndent ? 'V' : '' }}
                                        </span> Indent / Kosong
                                    </div>
                                </td>
                            @endforeach
                        </tr>

                        {{-- Indent duration row --}}
                        @if (collect($vendors)->contains(fn($v) => !empty($v['indent_duration'])))
                            <tr>
                                <td colspan="6"
                                    style="border:1px solid #000; padding:3px 6px; font-size:10px; font-style:italic; color:#c05c00;">
                                    Durasi Indent</td>
                                @foreach ($vendors as $v)
                                    @php $isRec = ($v['name'] ?? '') === $comparison->selected_vendor; @endphp
                                    <td
                                        style="border:1px solid #000; padding:3px 6px; font-size:10px; text-align:center; color:#c05c00; font-weight:600; {{ $isRec ? 'background:#f0fff4;' : '' }}">
                                        {{ $v['indent_duration'] ?? '' }}
                                    </td>
                                @endforeach
                            </tr>
                        @endif

                        {{-- Tax info --}}
                        <tr>
                            <td colspan="6" style="border:1px solid #000; padding:3px 6px;"></td>
                            @foreach ($vendors as $v)
                                @php $isRec = ($v['name'] ?? '') === $comparison->selected_vendor; @endphp
                                <td
                                    style="border:1px solid #000; padding:3px 6px; font-size:10px; {{ $isRec ? 'background:#f0fff4;' : '' }}">
                                    {{ $v['tax_info'] ?? '' }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- Payment terms --}}
                        <tr>
                            <td colspan="6" style="border:1px solid #000; padding:3px 6px;"></td>
                            @foreach ($vendors as $v)
                                @php $isRec = ($v['name'] ?? '') === $comparison->selected_vendor; @endphp
                                <td
                                    style="border:1px solid #000; padding:3px 6px; font-size:10px; {{ $isRec ? 'background:#f0fff4;' : '' }}">
                                    @php
                                        $top = $v['term_of_payment'] ?? '';
                                        if (is_numeric(trim($top))) {
                                            $top .= ' Hari';
                                        }
                                    @endphp
                                    {{ $top }}
                                </td>
                            @endforeach
                        </tr>

                        {{-- Payment method --}}
                        @if (collect($vendors)->where('payment_method', '!=', '')->count() > 0)
                            <tr>
                                <td colspan="6" style="border:1px solid #000; padding:3px 6px;"></td>
                                @foreach ($vendors as $v)
                                    @php $isRec = ($v['name'] ?? '') === $comparison->selected_vendor; @endphp
                                    <td
                                        style="border:1px solid #000; padding:3px 6px; font-size:10px; {{ $isRec ? 'background:#f0fff4;' : '' }}">
                                        {{ $v['payment_method'] ?? '' }}
                                    </td>
                                @endforeach
                            </tr>
                        @endif
                    </tbody>
                </table>

                {{-- Footer --}}
                <div class="mt-3 d-flex justify-content-between align-items-start">
                    <div style="font-size:11px;">
                        <strong>No. CLVP : {{ $comparison->comparison_code ?? $comparison->po_name }}</strong>
                        <div class="text-muted" style="margin-top:2px;">Ref PO: {{ $comparison->po_name }}</div>
                        @if ($comparison->notes)
                            <div class="text-muted mt-1">{{ $comparison->notes }}</div>
                        @endif
                    </div>
                    <div style="font-size:11px; text-align:right;">
                        Tgl {{ $comparison->created_at?->format('d/m/y') ?? '—' }}<br>
                        Dibuat oleh,<br><br><br><br><br>
                        ({{ $comparison->creator->name ?? '—' }})<br>
                        <span style="font-size:8px; color:#555;">SDP/FR/PCH/12, Rev.02</span>
                    </div>
                </div>

                {{-- If approved, show approval signatures --}}
                {{-- @if ($comparison->isApproved())
                    <div class="mt-2 d-flex gap-5" style="font-size:11px;">
                        <div style="text-align:center;">
                            Disetujui Supervisor,<br><br><br>
                            ({{ $comparison->supervisor->name ?? '—' }})<br>
                            <small>{{ $comparison->supervisor_approved_at?->format('d/m/Y') }}</small>
                        </div>
                        <div style="text-align:center;">
                            Disetujui Manager,<br><br><br>
                            ({{ $comparison->manager->name ?? '—' }})<br>
                            <small>{{ $comparison->manager_approved_at?->format('d/m/Y') }}</small>
                        </div>
                    </div>
                @endif --}}
            </div>{{-- end clvpDocument --}}

        </div>{{-- end tabClvp --}}

        {{-- ════════════════════════════════════════════════════════ --}}
        {{-- TAB 3: AUDIT LOG                                         --}}
        {{-- ════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="tabLog">
            <div style="max-width: 760px;">

                {{-- Context Banner for Revisions --}}
                @if (isset($allRevisions) && $allRevisions->count() > 1)
                    @php
                        $isRevision = $comparison->id !== $allRevisions->first()->id;
                        $origCmp = $allRevisions->first();
                    @endphp
                    @if ($isRevision)
                        <div class="alert border-0 shadow-sm d-flex gap-3 align-items-start mb-3" style="background: #f0fdf4; border-left: 4px solid #16a34a !important;">
                            <i class="bi bi-arrow-repeat fs-4 text-success flex-shrink-0 mt-1"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark" style="font-size:0.92rem;">
                                    Komparasi Ini Adalah Komparasi Revisi (PO: {{ $comparison->po_name }})
                                </div>
                                <div class="text-secondary small mt-1">
                                    Dokumen ini diajukan untuk merevisi komparasi sebelumnya (<strong>{{ $origCmp->comparison_code }}</strong>)
                                    @if ($origCmp->cancelled_at)
                                        yang dibatalkan oleh <strong>{{ $origCmp->cancelledBy->name ?? 'Manager' }}</strong> pada {{ $origCmp->cancelled_at?->format('d M Y H:i') }}.
                                    @elseif ($origCmp->rejected_at)
                                        yang ditolak oleh <strong>{{ $origCmp->rejectedBy->name ?? 'Approver' }}</strong> pada {{ $origCmp->rejected_at?->format('d M Y H:i') }}.
                                    @endif
                                </div>
                                @if ($origCmp->cancel_reason)
                                    <div class="mt-2 p-2 rounded bg-white border border-success-subtle small text-dark">
                                        <i class="bi bi-chat-quote-fill me-1 text-warning"></i><strong>Alasan Pembatalan / Catatan Revisi:</strong> "{{ $origCmp->cancel_reason }}"
                                    </div>
                                @elseif ($origCmp->rejection_reason)
                                    <div class="mt-2 p-2 rounded bg-white border border-danger-subtle small text-dark">
                                        <i class="bi bi-chat-quote-fill me-1 text-danger"></i><strong>Alasan Penolakan:</strong> "{{ $origCmp->rejection_reason }}"
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="alert border-0 shadow-sm d-flex gap-3 align-items-start mb-3" style="background: #fffbeb; border-left: 4px solid #f59e0b !important;">
                            <i class="bi bi-clock-history fs-4 text-warning flex-shrink-0 mt-1"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark" style="font-size:0.92rem;">
                                    Dokumen Ini Adalah Komparasi 1 (Original)
                                </div>
                                <div class="text-secondary small mt-1">
                                    Komparasi ini telah dibatalkan untuk revisi. Pengadaan telah dilanjutkan pada dokumen komparasi revisi baru.
                                </div>
                                @if ($comparison->cancel_reason)
                                    <div class="mt-2 p-2 rounded bg-white border border-warning-subtle small text-dark">
                                        <i class="bi bi-chat-quote-fill me-1 text-warning"></i><strong>Alasan Pembatalan:</strong> "{{ $comparison->cancel_reason }}"
                                    </div>
                                @endif
                                <div class="mt-2">
                                    <a href="{{ route('comparisons.show', $allRevisions->last()) }}" class="btn btn-sm btn-outline-dark fw-semibold">
                                        <i class="bi bi-arrow-right-circle me-1"></i>Buka Comparison Revisi Aktif ({{ $allRevisions->last()->comparison_code }}) &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                {{-- Activity Log Card --}}
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light py-2 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h6 class="mb-0 fw-semibold text-dark">
                            <i class="bi bi-journal-text me-2 text-primary"></i>Activity Log
                        </h6>
                        @if (isset($allRevisions) && $allRevisions->count() > 1)
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary btn-sm active" id="btnLogAll" onclick="toggleLogView('all')">
                                    <i class="bi bi-clock-history me-1"></i>Semua Versi PO ({{ $allRevisions->sum(fn($r) => $r->logs->count()) }})
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnLogCurrent" onclick="toggleLogView('current')">
                                    Hanya Versi Ini ({{ $comparison->logs->count() }})
                                </button>
                            </div>
                        @endif
                    </div>

                    <div class="card-body p-0">
                        {{-- VIEW 1: Current Comparison Only --}}
                        <div id="logCurrentContainer" class="{{ isset($allRevisions) && $allRevisions->count() > 1 ? 'd-none' : '' }}">
                            @if ($comparison->logs->isEmpty())
                                <div class="p-4 text-center text-muted small">Belum ada riwayat aktivitas yang tercatat.</div>
                            @else
                                <ul class="list-group list-group-flush">
                                    @foreach ($comparison->logs as $log)
                                        @include('comparisons.partials.log_item', ['log' => $log])
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        {{-- VIEW 2: All Revisions Timeline --}}
                        @if (isset($allRevisions) && $allRevisions->count() > 1)
                            <div id="logAllContainer">
                                @foreach ($allRevisions as $revIdx => $rev)
                                    @php
                                        $isThisCmp = $rev->id === $comparison->id;
                                        $revTitle = $revIdx === 0 ? 'Comparison 1 (Original)' : ($allRevisions->count() === 2 ? 'Comparison Revisi' : 'Comparison Revisi ' . $revIdx);
                                    @endphp
                                    <div class="p-3 {{ $revIdx > 0 ? 'border-top' : '' }}" style="{{ $isThisCmp ? 'background: #f8fafc;' : '' }}">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2 pb-2 border-bottom">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge {{ $isThisCmp ? 'bg-primary' : 'bg-secondary' }} px-2 py-1">
                                                    {{ $revTitle }}
                                                </span>
                                                <strong class="text-dark small">{{ $rev->comparison_code }}</strong>
                                                @if ($isThisCmp)
                                                    <span class="badge bg-primary-subtle text-primary border" style="font-size:0.68rem;">Dokumen Saat Ini</span>
                                                @else
                                                    <a href="{{ route('comparisons.show', $rev) }}" class="small text-decoration-none">
                                                        <i class="bi bi-box-arrow-up-right me-1"></i>Buka Dokumen
                                                    </a>
                                                @endif
                                            </div>
                                            <div>
                                                <span class="badge {{ $rev->statusBadgeClass() }}" style="font-size:0.72rem;">
                                                    {{ $rev->statusLabel() }}
                                                </span>
                                            </div>
                                        </div>

                                        @if ($rev->logs->isEmpty())
                                            <div class="text-muted small py-2 fst-italic">Tidak ada log tercatat untuk versi ini.</div>
                                        @else
                                            <ul class="list-group list-group-flush mb-0">
                                                @foreach ($rev->logs as $log)
                                                    @include('comparisons.partials.log_item', ['log' => $log])
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                    @if ($revIdx < $allRevisions->count() - 1)
                                        <div class="py-1 px-3 bg-light text-center border-top border-bottom small text-muted d-flex align-items-center justify-content-center gap-2">
                                            <i class="bi bi-arrow-down-circle text-primary"></i>
                                            <span class="fw-semibold">Transisi Revisi Pengadaan</span>
                                            <i class="bi bi-arrow-down-circle text-primary"></i>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                @if (isset($allRevisions) && $allRevisions->count() > 1)
                    <script>
                        function toggleLogView(mode) {
                            const btnAll = document.getElementById('btnLogAll');
                            const btnCurr = document.getElementById('btnLogCurrent');
                            const cntAll = document.getElementById('logAllContainer');
                            const cntCurr = document.getElementById('logCurrentContainer');

                            if (mode === 'all') {
                                btnAll.classList.add('active');
                                btnCurr.classList.remove('active');
                                cntAll.classList.remove('d-none');
                                cntCurr.classList.add('d-none');
                            } else {
                                btnCurr.classList.add('active');
                                btnAll.classList.remove('active');
                                cntCurr.classList.remove('d-none');
                                cntAll.classList.add('d-none');
                            }
                        }
                    </script>
                @endif

            </div>
        </div>{{-- end tabLog --}}

    </div>{{-- end tab-content --}}

    <div class="mt-3">
        <a href="{{ route('comparisons.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Approvals
        </a>
    </div>

    <style>
        @media print {
            body * {
                visibility: hidden !important;
            }

            #clvpDocument,
            #clvpDocument * {
                visibility: visible !important;
            }

            #clvpDocument {
                position: fixed;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 10px;
                border: none !important;
            }
        }
    </style>

@endsection
