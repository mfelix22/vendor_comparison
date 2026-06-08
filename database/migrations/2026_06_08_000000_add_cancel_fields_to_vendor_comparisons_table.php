<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_comparisons', function (Blueprint $table) {
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('odoo_synced_at');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            $table->string('cancel_reason')->nullable()->after('cancelled_at');

            $table->foreign('cancelled_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vendor_comparisons', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['cancelled_by', 'cancelled_at', 'cancel_reason']);
        });
    }
};
