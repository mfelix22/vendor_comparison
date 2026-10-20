<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('costing_bbn_masters', function (Blueprint $table) {
            $table->string('pulau')->nullable()->after('display_name')->index();
            $table->string('provinsi')->nullable()->after('pulau')->index();
        });

        // Automatically backfill existing BBNs by matching against costing_regions
        $regions = DB::table('costing_regions')->get();
        $bbns = DB::table('costing_bbn_masters')->get();

        foreach ($bbns as $b) {
            $nameClean = strtolower(trim($b->name ?: $b->display_name ?: ''));
            if (!$nameClean) continue;

            $matched = $regions->first(function ($r) use ($nameClean) {
                $rArea = strtolower(trim($r->area));
                return $rArea === $nameClean || str_contains($nameClean, $rArea) || str_contains($rArea, $nameClean);
            });

            if ($matched) {
                DB::table('costing_bbn_masters')
                    ->where('id', $b->id)
                    ->update([
                        'pulau' => $matched->pulau,
                        'provinsi' => $matched->provinsi,
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('costing_bbn_masters', function (Blueprint $table) {
            $table->dropColumn(['pulau', 'provinsi']);
        });
    }
};
