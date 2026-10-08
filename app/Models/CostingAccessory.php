<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostingAccessory extends Model
{
    use HasFactory;

    protected $fillable = [
        'costing_id',
        'costing_vehicle_id',
        'accessory_name',
        'amount',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function costing(): BelongsTo
    {
        return $this->belongsTo(Costing::class, 'costing_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(CostingVehicle::class, 'costing_vehicle_id');
    }
}
