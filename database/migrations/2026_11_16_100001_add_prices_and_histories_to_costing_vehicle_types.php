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
        // 1. Add active Harga OTR and Diskon to costing_vehicle_types (Additive only)
        Schema::table('costing_vehicle_types', function (Blueprint $table) {
            $table->decimal('harga_otr', 16, 2)->default(0)->after('odoo_product_id');
            $table->decimal('diskon', 16, 2)->default(0)->after('harga_otr');
        });

        // 2. Create Price History and Approval Audit Trail table
        Schema::create('costing_vehicle_type_price_histories', function (Blueprint $table) {
            $table->id();
            
            // Explicit short constraint names to satisfy MySQL 64-char identifier limit
            $table->unsignedBigInteger('costing_vehicle_type_id');
            $table->foreign('costing_vehicle_type_id', 'fk_cvth_type_id')
                ->references('id')
                ->on('costing_vehicle_types')
                ->cascadeOnDelete();

            $table->decimal('old_harga_otr', 16, 2)->default(0);
            $table->decimal('new_harga_otr', 16, 2)->default(0);
            $table->decimal('old_diskon', 16, 2)->default(0);
            $table->decimal('new_diskon', 16, 2)->default(0);
            
            // Status: pending (waiting for manager), approved (active), rejected
            $table->string('status', 20)->default('pending')->index();
            $table->string('source', 30)->default('manual'); // manual_create, manual_edit, excel_import
            $table->string('batch_id', 50)->nullable()->index(); // for grouping bulk excel imports
            
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->foreign('requested_by', 'fk_cvth_req_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by', 'fk_cvth_app_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('costing_vehicle_type_price_histories');

        Schema::table('costing_vehicle_types', function (Blueprint $table) {
            $table->dropColumn(['harga_otr', 'diskon']);
        });
    }
};
