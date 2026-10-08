<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostingAccessoryTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'default_amount',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'default_amount' => 'decimal:2',
        ];
    }
}
