<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostingBbnMaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'odoo_bbn_id',
        'kode',
        'name',
        'alamat',
        'display_name',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

