<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE vendor_comparisons MODIFY COLUMN status ENUM(
            'draft',
            'pending_procurement',
            'pending_supervisor',
            'pending_manager',
            'approved',
            'rejected',
            'cancelled'
        ) NOT NULL DEFAULT 'pending_supervisor'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE vendor_comparisons MODIFY COLUMN status ENUM(
            'draft',
            'pending_supervisor',
            'pending_manager',
            'approved',
            'rejected',
            'cancelled'
        ) NOT NULL DEFAULT 'pending_supervisor'");
    }
};
