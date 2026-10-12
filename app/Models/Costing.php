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
        'status',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
