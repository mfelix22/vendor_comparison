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
        Schema::create('costing_bbn_masters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('odoo_bbn_id')->nullable()->index();
            $table->string('kode')->index();
            $table->text('alamat')->nullable();
            $table->string('display_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('costing_bbn_masters');
    }
};
