@php
    $userRole = $log->user->role ?? null;
    $roleLabel = match($userRole) {
        'creator'    => 'Purchasing Staff',
        'supervisor' => 'Purchasing Supervisor',
        'manager'    => 'Purchasing Manager',
        'controller' => 'Purchasing Controller',
        'admin'      => 'Admin',
        default      => null,
    };
    $roleBadgeClass = match($userRole) {
        'creator'    => 'bg-secondary-subtle text-secondary border',
        'supervisor' => 'bg-info-subtle text-info border',
        'manager'    => 'bg-primary-subtle text-primary border',
        'controller' => 'bg-dark-subtle text-dark border',
        default      => 'bg-light text-muted border',
    };

    $notesBg = match($log->action) {
        'revision_requested' => 'background: #fffbeb; border: 1px solid #fde68a !important; color: #92400e;',
        'resubmitted'        => 'background: #eff6ff; border: 1px solid #bfdbfe !important; color: #1e40af;',
        'rejected'           => 'background: #fef2f2; border: 1px solid #fecaca !important; color: #991b1b;',
        default              => 'background: #f8fafc; border: 1px solid #e2e8f0 !important; color: #334155;',
    };
@endphp

<li class="list-group-item py-3 px-3 border-0 border-bottom">
    <div class="d-flex gap-3 align-items-start">
        <i class="bi {{ $log->actionIcon() }} fs-5 mt-1 flex-shrink-0"></i>
        <div class="flex-grow-1">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="fw-semibold text-dark">{{ $log->actionLabel() }}</span>
            </div>
            <div class="text-muted small mt-1">
                oleh <span class="fw-medium text-dark">{{ $log->user->name ?? 'System' }}</span>
                @if ($roleLabel)
                    <span class="badge {{ $roleBadgeClass }} ms-1" style="font-size: 0.68rem; font-weight: 500;">
                        {{ $roleLabel }}
                    </span>
                @endif
                &middot; <span title="{{ $log->created_at->format('d M Y H:i:s') }}">{{ $log->created_at->format('d M Y H:i') }}</span>
                <span class="text-secondary fst-italic">({{ $log->created_at->diffForHumans() }})</span>
            </div>
            @if ($log->notes)
                <div class="mt-2 p-2 rounded small" style="{{ $notesBg }}">
                    @if ($log->action === 'revision_requested')
                        <i class="bi bi-chat-quote-fill me-1 text-warning"></i>
                    @elseif ($log->action === 'rejected')
                        <i class="bi bi-exclamation-octagon-fill me-1 text-danger"></i>
                    @elseif ($log->action === 'resubmitted')
                        <i class="bi bi-arrow-repeat me-1 text-primary"></i>
                    @endif
                    {{ $log->notes }}
                </div>
            @endif
        </div>
    </div>
</li>

