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
        Schema::create('costing_accessories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('costing_id')->constrained('costings')->onDelete('cascade');
            $table->foreignId('costing_vehicle_id')->constrained('costing_vehicles')->onDelete('cascade');
            $table->string('accessory_name');
            $table->decimal('amount', 15, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('costing_accessories');
    }
};
