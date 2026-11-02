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
            $table->date('tanggal_fph')->nullable()->after('customer_name');
            $table->date('tanggal_revisi_fph')->nullable()->after('tanggal_fph');
            $table->date('tanggal_email_smd')->nullable()->after('tanggal_revisi_fph');
        });

        Schema::table('costing_vehicles', function (Blueprint $table) {
            $table->unsignedInteger('qty')->default(1)->after('pemakaian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('costings', function (Blueprint $table) {
            $table->dropColumn(['tanggal_fph', 'tanggal_revisi_fph', 'tanggal_email_smd']);
        });

        Schema::table('costing_vehicles', function (Blueprint $table) {
            $table->dropColumn('qty');
        });
    }
};

