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
            $table->foreignId('controller_id')->nullable()->after('manager_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('controller_approved_at')->nullable()->after('controller_id');
            $table->text('controller_notes')->nullable()->after('controller_approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_vendor_comparisons', function (Blueprint $table) {
            $table->dropConstrainedForeignId('controller_id');
            $table->dropColumn(['controller_approved_at', 'controller_notes']);
        });
    }
};

