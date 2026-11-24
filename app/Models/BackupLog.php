<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
