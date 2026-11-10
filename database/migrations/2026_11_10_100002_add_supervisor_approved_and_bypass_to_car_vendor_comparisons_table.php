<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('car_vendor_comparisons', function (Blueprint $table) {
            $table->timestamp('supervisor_approved_at')->nullable()->after('supervisor_submitted_at');
            $table->foreignId('bypassed_by')->nullable()->after('manager_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('bypassed_at')->nullable()->after('bypassed_by');
            $table->text('bypass_reason')->nullable()->after('bypassed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_vendor_comparisons', function (Blueprint $table) {
            $table->dropForeign(['bypassed_by']);
            $table->dropColumn([
                'supervisor_approved_at',
                'bypassed_by',
                'bypassed_at',
                'bypass_reason',
            ]);
        });
    }
};

