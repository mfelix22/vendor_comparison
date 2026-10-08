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
        Schema::create('costing_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('costing_id')->constrained('costings')->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->string('brand')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->unsignedBigInteger('odoo_product_id')->nullable();
            $table->string('bbn')->nullable();
            $table->unsignedBigInteger('odoo_bbn_id')->nullable();
            $table->string('pemakaian')->nullable();
            $table->decimal('harga_otr', 15, 2)->default(0);
            $table->decimal('diskon', 15, 2)->default(0);
            $table->decimal('harga_nett', 15, 2)->default(0);
            $table->decimal('estimasi_kenaikan', 15, 2)->default(0);
            $table->decimal('harga_setelah_kenaikan', 15, 2)->default(0);
            $table->decimal('total_acc', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('costing_vehicles');
    }
};
