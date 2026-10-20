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
        Schema::table('costings', function (Blueprint $table) {
            $table->string('fph_type')->default('retail')->after('notes')->index();
            $table->string('approval_status')->default('approved')->after('fph_type')->index();
            $table->foreignId('approved_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('costings', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'fph_type',
                'approval_status',
                'approved_by',
                'approved_at',
                'rejection_reason',
            ]);
        });
    }
};
