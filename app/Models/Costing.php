<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Costing extends Model
{
    use HasFactory;

    protected $fillable = [
        'costing_number',
        'customer_name',
        'odoo_partner_id',
        'notes',
        'fph_type',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isTender(): bool
    {
        return $this->fph_type === 'tender';
    }

    public function isPendingApproval(): bool
    {
        return $this->isTender() && $this->approval_status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }


    public function vehicles(): HasMany
    {
        return $this->hasMany(CostingVehicle::class, 'costing_id')->orderBy('sort_order');
    }

    public function accessories(): HasMany
    {
        return $this->hasMany(CostingAccessory::class, 'costing_id')->orderBy('sort_order');
    }
}
