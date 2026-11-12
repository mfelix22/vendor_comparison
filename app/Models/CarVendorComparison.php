<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarVendorComparison extends Model
{
    protected $fillable = [
        'comparison_code',
        'po_id',
        'po_name',
        'po_vendor',
        'car_model',
        'qty',
        'uom',
        'warna',
        'bbn',
        'vin_code',
        'pricelist_ori',
        'vendors',
        'selected_vendor',
        'notes',
        'status',
        'supervisor_id',
        'supervisor_submitted_at',
        'supervisor_approved_at',
        'supervisor_notes',
        'manager_id',
        'manager_approved_at',
        'manager_notes',
        'controller_id',
        'controller_approved_at',
        'controller_notes',
        'bypassed_by',
        'bypassed_at',
        'bypass_reason',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'cancelled_by',
        'cancelled_at',
        'cancel_reason',
        'odoo_synced_at',
        'created_by',
    ];

    protected $casts = [
        'vendors'                 => 'array',
        'pricelist_ori'           => 'float',
        'supervisor_submitted_at' => 'datetime',
        'supervisor_approved_at'  => 'datetime',
        'manager_approved_at'     => 'datetime',
        'controller_approved_at'  => 'datetime',
        'bypassed_at'             => 'datetime',
        'rejected_at'             => 'datetime',
        'cancelled_at'            => 'datetime',
        'odoo_synced_at'          => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function controller()
    {
        return $this->belongsTo(User::class, 'controller_id');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function bypassedBy()
    {
        return $this->belongsTo(User::class, 'bypassed_by');
    }

    public function logs()
    {
        return $this->hasMany(CarComparisonLog::class, 'car_comparison_id')->orderBy('created_at');
    }

    // ── Status Helpers ─────────────────────────────────────

    public function isPendingSupervisor(): bool
    {
        return $this->status === 'pending_supervisor';
    }

    public function isPendingManager(): bool
    {
        return $this->status === 'pending_manager';
    }

    public function isPendingController(): bool
    {
        return $this->status === 'pending_controller';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isBypassed(): bool
    {
        return $this->bypassed_by !== null;
    }

    public function requiresSupervisorApproval(): bool
    {
        if ($this->isPendingSupervisor() || $this->supervisor_approved_at !== null) {
            return true;
        }
        if ($this->isBypassed() && $this->supervisor_submitted_at === null) {
            return true;
        }
        if ($this->creator && !$this->creator->isSupervisor() && !$this->creator->isManager() && !$this->creator->isAdmin()) {
            return true;
        }
        return false;
    }

    public function isEditableBy(?User $user): bool
    {
        if (!$user || $user->isController()) return false;
        if ($this->isPendingSupervisor()) {
            return $user->id === $this->created_by || $user->isAdmin();
        }
        return $this->isPendingManager()
            && $this->supervisor_approved_at === null
            && ($user->id === $this->created_by || $user->id === $this->supervisor_id || $user->isAdmin());
    }

    public function canBypassApprove(?User $user): bool
    {
        if (!$user) return false;
        return ($user->isManager() || $user->isAdmin()) && $this->isPendingSupervisor();
    }

    public function isCancellableBy(?User $user): bool
    {
        if (!$user || $user->isController()) return false;
        // Creator can cancel while waiting for supervisor review
        if ($this->isPendingSupervisor() && ($user->id === $this->created_by || $user->isAdmin())) {
            return true;
        }
        // Supervisor can cancel while waiting for manager
        if ($user->isSupervisor() && $this->isPendingManager()) {
            return true;
        }
        // Manager can cancel while waiting for controller or after final approval
        if ($user->isManager() && ($this->isPendingController() || $this->isApproved())) {
            return true;
        }
        // Admin can cancel at any active post-submission or approved stage
        if ($user->isAdmin() && ($this->isPendingManager() || $this->isPendingController() || $this->isApproved())) {
            return true;
        }
        return false;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending_supervisor' => 'Pending Supervisor',
            'pending_manager'    => 'Pending Manager',
            'pending_controller' => 'Pending Controller',
            'approved'           => 'Approved',
            'rejected'           => 'Rejected',
            'cancelled'          => 'Cancelled',
            default              => 'Draft',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'pending_supervisor' => 'bg-warning text-dark',
            'pending_manager'    => 'bg-info text-dark',
            'pending_controller' => 'bg-primary',
            'approved'           => 'bg-success',
            'rejected'           => 'bg-danger',
            'cancelled'          => 'bg-secondary',
            default              => 'bg-secondary',
        };
    }

    public function getPreparedByNameAttribute(): string
    {
        if ($this->creator) {
            return $this->creator->name;
        }
        if ($this->supervisor) {
            return $this->supervisor->name;
        }
        return 'Purchasing';
    }
}

