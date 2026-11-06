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
        'supervisor_notes',
        'manager_id',
        'manager_approved_at',
        'manager_notes',
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
        'manager_approved_at'     => 'datetime',
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

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function logs()
    {
        return $this->hasMany(CarComparisonLog::class, 'car_comparison_id')->orderBy('created_at');
    }

    // ── Status Helpers ─────────────────────────────────────

    public function isPendingManager(): bool
    {
        return $this->status === 'pending_manager';
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

    public function isEditableBy(?User $user): bool
    {
        if (!$user) return false;
        return $this->isPendingManager()
            && ($user->id === $this->created_by || $user->id === $this->supervisor_id || $user->isAdmin());
    }

    public function isCancellableBy(?User $user): bool
    {
        if (!$user) return false;
        // Supervisor can cancel while waiting for manager
        if ($user->isSupervisor() && $this->isPendingManager()) {
            return true;
        }
        // Manager or Admin can cancel even after approval
        if (($user->isManager() || $user->isAdmin()) && $this->isApproved()) {
            return true;
        }
        return false;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending_manager' => 'Pending Manager',
            'approved'        => 'Approved',
            'rejected'        => 'Rejected',
            'cancelled'       => 'Cancelled',
            default           => 'Draft',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'pending_manager' => 'bg-info text-dark',
            'approved'        => 'bg-success',
            'rejected'        => 'bg-danger',
            'cancelled'       => 'bg-secondary',
            default           => 'bg-secondary',
        };
    }

    public function getPreparedByNameAttribute(): string
    {
        if ($this->supervisor && $this->supervisor->isSupervisor()) {
            return $this->supervisor->name;
        }
        if ($this->creator && ($this->creator->isSupervisor() || $this->creator->isAdmin())) {
            return $this->creator->name;
        }
        return $this->supervisor->name ?? $this->creator->name ?? 'Purchasing Supervisor';
    }
}

