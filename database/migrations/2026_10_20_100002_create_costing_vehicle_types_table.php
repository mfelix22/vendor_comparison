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
        Schema::create('costing_vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('costing_brand_id')->constrained('costing_brands')->onDelete('cascade');
            $table->string('brand_name')->nullable()->index();
            $table->string('name')->index();
            $table->unsignedBigInteger('odoo_product_id')->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('costing_vehicle_types');
    }
};
