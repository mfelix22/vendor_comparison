<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CostingVehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'costing_id',
        'sort_order',
        'pulau',
        'provinsi',
        'brand',
        'vehicle_type',
        'odoo_product_id',
        'bbn',
        'odoo_bbn_id',
        'pemakaian',
        'harga_otr',
        'diskon',
        'harga_nett',
        'estimasi_kenaikan',
        'harga_setelah_kenaikan',
        'total_acc',
        'grand_total',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'harga_otr'              => 'decimal:2',
            'diskon'                 => 'decimal:2',
            'harga_nett'             => 'decimal:2',
            'estimasi_kenaikan'      => 'decimal:2',
            'harga_setelah_kenaikan' => 'decimal:2',
            'total_acc'              => 'decimal:2',
            'grand_total'            => 'decimal:2',
        ];
    }

    public function costing(): BelongsTo
    {
        return $this->belongsTo(Costing::class, 'costing_id');
    }

    public function accessories(): HasMany
    {
        return $this->hasMany(CostingAccessory::class, 'costing_vehicle_id')->orderBy('sort_order');
    }
}
