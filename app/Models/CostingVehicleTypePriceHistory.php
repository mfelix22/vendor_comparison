<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostingVehicleTypePriceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'costing_vehicle_type_id',
        'old_harga_otr',
        'new_harga_otr',
        'old_diskon',
        'new_diskon',
        'status',
        'source',
        'batch_id',
        'requested_by',
        'approved_by',
        'approved_at',
        'notes',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'old_harga_otr' => 'float',
            'new_harga_otr' => 'float',
            'old_diskon'    => 'float',
            'new_diskon'    => 'float',
            'approved_at'   => 'datetime',
        ];
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(CostingVehicleType::class, 'costing_vehicle_type_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'pending'  => 'bg-warning text-dark',
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            default    => 'bg-secondary',
        };
    }
}

