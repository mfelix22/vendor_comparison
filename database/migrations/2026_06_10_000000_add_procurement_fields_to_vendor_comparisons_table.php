<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_comparisons', function (Blueprint $table) {
            $table->unsignedBigInteger('procurement_id')->nullable()->after('supervisor_notes');
            $table->timestamp('procurement_approved_at')->nullable()->after('procurement_id');
            $table->text('procurement_notes')->nullable()->after('procurement_approved_at');
            $table->boolean('requires_procurement')->default(false)->after('procurement_notes');

            $table->foreign('procurement_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vendor_comparisons', function (Blueprint $table) {
            $table->dropForeign(['procurement_id']);
            $table->dropColumn(['procurement_id', 'procurement_approved_at', 'procurement_notes', 'requires_procurement']);
        });
    }
};
