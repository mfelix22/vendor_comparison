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
            $table->string('smd')->nullable()->after('customer_name');
            $table->string('masa_sewa')->nullable()->after('smd');
            $table->date('request_delivery_date')->nullable()->after('masa_sewa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('costings', function (Blueprint $table) {
            $table->dropColumn(['smd', 'masa_sewa', 'request_delivery_date']);
        });
    }
};
