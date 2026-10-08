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
        'alamat',
        'display_name',
    ];
}
