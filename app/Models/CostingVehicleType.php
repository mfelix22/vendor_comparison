<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostingVehicleType extends Model
{
    use HasFactory;

    protected $fillable = [
        'costing_brand_id',
        'brand_name',
        'name',
        'odoo_product_id',
        'harga_otr',
        'diskon',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'harga_otr' => 'float',
            'diskon'    => 'float',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(CostingBrand::class, 'costing_brand_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function priceHistories()
    {
        return $this->hasMany(CostingVehicleTypePriceHistory::class, 'costing_vehicle_type_id')->orderByDesc('created_at');
    }

    public function pendingPriceHistory()
    {
        return $this->hasOne(CostingVehicleTypePriceHistory::class, 'costing_vehicle_type_id')->where('status', 'pending')->latestOfMany();
    }

    public function latestApprovedPriceHistory()
    {
        return $this->hasOne(CostingVehicleTypePriceHistory::class, 'costing_vehicle_type_id')->where('status', 'approved')->latestOfMany();
    }

    public function hasPendingPriceChange(): bool
    {
        return $this->priceHistories()->where('status', 'pending')->exists();
    }

    public function getHargaNettoAttribute(): float
    {
        return max(0, (float)$this->harga_otr - (float)$this->diskon);
    }
}
