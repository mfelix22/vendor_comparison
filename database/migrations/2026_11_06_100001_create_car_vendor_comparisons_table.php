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
        Schema::create('car_vendor_comparisons', function (Blueprint $table) {
            $table->id();

            // Comparison sequence code (e.g. 2026/CP-CAR/00001)
            $table->string('comparison_code', 50)->nullable()->index();

            // Odoo RFQ Reference
            $table->unsignedBigInteger('po_id')->index();
            $table->string('po_name');
            $table->string('po_vendor')->nullable();

            // Car Specifications
            $table->string('car_model')->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->string('uom', 50)->default('unit');
            $table->string('warna', 100)->nullable();
            $table->string('bbn', 100)->nullable();
            $table->string('vin_code', 50)->nullable();
            $table->decimal('pricelist_ori', 16, 2)->default(0);

            // Comparison Matrix Data (3 to 10 vendors)
            $table->json('vendors');
            $table->string('selected_vendor')->nullable();
            $table->text('notes')->nullable();

            // Workflow status: pending_manager -> approved / rejected / cancelled
            $table->string('status', 50)->default('pending_manager')->index();

            // Supervisor who prepared / submitted
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('supervisor_submitted_at')->nullable();
            $table->text('supervisor_notes')->nullable();

            // Manager approval
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('manager_approved_at')->nullable();
            $table->text('manager_notes')->nullable();

            // Rejection
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Cancellation
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();

            // Odoo sync tracking
            $table->timestamp('odoo_synced_at')->nullable();

            // Creator
            $table->foreignId('created_by')->constrained('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_vendor_comparisons');
    }
};

