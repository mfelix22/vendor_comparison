<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Replaces the bloated 208 BBN list with the clean 38 official Indonesian Provinces + Batam FTZ (39 total).
     */
    public function up(): void
    {
        // 38 Official Indonesian Provinces + Batam Free Trade Zone (FTZ)
        $provinces = [
            // ── SUMATERA (10 Provinces + 1 FTZ Hub) ──────────────────────────
            [
                'kode'         => 'ACH',
                'name'         => 'Aceh',
                'display_name' => 'Aceh (Banda Aceh / Plat BL)',
                'pulau'        => 'SUMATERA',
                'provinsi'     => 'ACEH',
                'odoo_bbn_id'  => 27,
            ],
            [
                'kode'         => 'MES',
                'name'         => 'Sumatera Utara',
                'display_name' => 'Sumatera Utara (Medan / Plat BK, BB)',
                'pulau'        => 'SUMATERA',
                'provinsi'     => 'SUMATERA UTARA',
                'odoo_bbn_id'  => 10,
            ],
            [
                'kode'         => 'PDG',
                'name'         => 'Sumatera Barat',
                'display_name' => 'Sumatera Barat (Padang / Plat BA)',
                'pulau'        => 'SUMATERA',
                'provinsi'     => 'SUMATERA BARAT',
                'odoo_bbn_id'  => 23,
            ],
            [
                'kode'         => 'PKU',
                'name'         => 'Riau',
                'display_name' => 'Riau (Pekanbaru / Plat BM)',
                'pulau'        => 'SUMATERA',
                'provinsi'     => 'RIAU',
                'odoo_bbn_id'  => 12,
            ],
            [
                'kode'         => 'KRU',
                'name'         => 'Kepulauan Riau',
                'display_name' => 'Kepulauan Riau (Tanjung Pinang / Plat BP)',
                'pulau'        => 'SUMATERA',
                'provinsi'     => 'KEPULAUAN RIAU',
                'odoo_bbn_id'  => null,
            ],
            [
                'kode'         => 'BTM',
                'name'         => 'Batam (FTZ)',
                'display_name' => 'Batam (FTZ - Free Trade Zone / Plat BP)',
                'pulau'        => 'SUMATERA',
                'provinsi'     => 'KEPULAUAN RIAU',
                'odoo_bbn_id'  => 15,
            ],
            [
                'kode'         => 'JMB',
                'name'         => 'Jambi',
                'display_name' => 'Jambi (Jambi / Plat BH)',
                'pulau'        => 'SUMATERA',
                'provinsi'     => 'JAMBI',
                'odoo_bbn_id'  => null,
            ],
            [
                'kode'         => 'PLB',
                'name'         => 'Sumatera Selatan',
                'display_name' => 'Sumatera Selatan (Palembang / Plat BG)',
                'pulau'        => 'SUMATERA',
                'provinsi'     => 'SUMATERA SELATAN',
                'odoo_bbn_id'  => 11,
            ],
            [
                'kode'         => 'BKU',
                'name'         => 'Bengkulu',
                'display_name' => 'Bengkulu (Bengkulu / Plat BD)',
                'pulau'        => 'SUMATERA',
                'provinsi'     => 'BENGKULU',
                'odoo_bbn_id'  => 19,
            ],
            [
                'kode'         => 'LMP',
                'name'         => 'Lampung',
                'display_name' => 'Lampung (Bandar Lampung / Plat BE)',
                'pulau'        => 'SUMATERA',
                'provinsi'     => 'LAMPUNG',
                'odoo_bbn_id'  => 8,
            ],
            [
                'kode'         => 'BGK',
                'name'         => 'Kepulauan Bangka Belitung',
                'display_name' => 'Kepulauan Bangka Belitung (Pangkal Pinang / Plat BN)',
                'pulau'        => 'SUMATERA',
                'provinsi'     => 'BANGKA BELITUNG',
                'odoo_bbn_id'  => 28,
            ],

            // ── JAWA (6 Provinces) ───────────────────────────────────────────
            [
                'kode'         => 'JKT',
                'name'         => 'DKI Jakarta',
                'display_name' => 'DKI Jakarta (Jakarta / Plat B)',
                'pulau'        => 'JAWA',
                'provinsi'     => 'DKI JAKARTA',
                'odoo_bbn_id'  => 2,
            ],
            [
                'kode'         => 'BDG',
                'name'         => 'Jawa Barat',
                'display_name' => 'Jawa Barat (Bandung / Plat D, E, F, T, Z)',
                'pulau'        => 'JAWA',
                'provinsi'     => 'JAWA BARAT',
                'odoo_bbn_id'  => 3,
            ],
            [
                'kode'         => 'SMG',
                'name'         => 'Jawa Tengah',
                'display_name' => 'Jawa Tengah (Semarang / Plat H, G, K, R, AA, AD)',
                'pulau'        => 'JAWA',
                'provinsi'     => 'JAWA TENGAH',
                'odoo_bbn_id'  => 13,
            ],
            [
                'kode'         => 'YOG',
                'name'         => 'DI Yogyakarta',
                'display_name' => 'DI Yogyakarta (Yogyakarta / Plat AB)',
                'pulau'        => 'JAWA',
                'provinsi'     => 'DAERAH ISTIMEWA YOGYAKARTA',
                'odoo_bbn_id'  => 16,
            ],
            [
                'kode'         => 'SUB',
                'name'         => 'Jawa Timur',
                'display_name' => 'Jawa Timur (Surabaya / Plat L, W, M, N, P, S, AE, AG)',
                'pulau'        => 'JAWA',
                'provinsi'     => 'JAWA TIMUR',
                'odoo_bbn_id'  => 14,
            ],
            [
                'kode'         => 'CLG',
                'name'         => 'Banten',
                'display_name' => 'Banten (Serang / Cilegon / Tangerang / Plat A)',
                'pulau'        => 'JAWA',
                'provinsi'     => 'BANTEN',
                'odoo_bbn_id'  => 5,
            ],

            // ── BALI & NUSA TENGGARA (3 Provinces) ───────────────────────────
            [
                'kode'         => 'DPS',
                'name'         => 'Bali',
                'display_name' => 'Bali (Denpasar / Plat DK)',
                'pulau'        => 'BALI & NUSA TENGGARA',
                'provinsi'     => 'BALI',
                'odoo_bbn_id'  => 7,
            ],
            [
                'kode'         => 'MTM',
                'name'         => 'Nusa Tenggara Barat',
                'display_name' => 'Nusa Tenggara Barat (Mataram / Lombok / Plat DR, EA)',
                'pulau'        => 'BALI & NUSA TENGGARA',
                'provinsi'     => 'NUSA TENGGARA BARAT',
                'odoo_bbn_id'  => 21,
            ],
            [
                'kode'         => 'KPG',
                'name'         => 'Nusa Tenggara Timur',
                'display_name' => 'Nusa Tenggara Timur (Kupang / Plat DH, EB, ED)',
                'pulau'        => 'BALI & NUSA TENGGARA',
                'provinsi'     => 'NUSA TENGGARA TIMUR',
                'odoo_bbn_id'  => null,
            ],

            // ── KALIMANTAN (5 Provinces) ─────────────────────────────────────
            [
                'kode'         => 'PTA',
                'name'         => 'Kalimantan Barat',
                'display_name' => 'Kalimantan Barat (Pontianak / Plat KB)',
                'pulau'        => 'KALIMANTAN',
                'provinsi'     => 'KALIMANTAN BARAT',
                'odoo_bbn_id'  => 22,
            ],
            [
                'kode'         => 'PLK',
                'name'         => 'Kalimantan Tengah',
                'display_name' => 'Kalimantan Tengah (Palangka Raya / Plat KH)',
                'pulau'        => 'KALIMANTAN',
                'provinsi'     => 'KALIMANTAN TENGAH',
                'odoo_bbn_id'  => null,
            ],
            [
                'kode'         => 'BJM',
                'name'         => 'Kalimantan Selatan',
                'display_name' => 'Kalimantan Selatan (Banjarmasin / Plat DA)',
                'pulau'        => 'KALIMANTAN',
                'provinsi'     => 'KALIMANTAN SELATAN',
                'odoo_bbn_id'  => null,
            ],
            [
                'kode'         => 'BPN',
                'name'         => 'Kalimantan Timur',
                'display_name' => 'Kalimantan Timur (Samarinda / Balikpapan / Plat KT)',
                'pulau'        => 'KALIMANTAN',
                'provinsi'     => 'KALIMANTAN TIMUR',
                'odoo_bbn_id'  => 4,
            ],
            [
                'kode'         => 'TRK',
                'name'         => 'Kalimantan Utara',
                'display_name' => 'Kalimantan Utara (Tanjung Selor / Tarakan / Plat KU)',
                'pulau'        => 'KALIMANTAN',
                'provinsi'     => 'KALIMANTAN UTARA',
                'odoo_bbn_id'  => 29,
            ],

            // ── SULAWESI (6 Provinces) ───────────────────────────────────────
            [
                'kode'         => 'MND',
                'name'         => 'Sulawesi Utara',
                'display_name' => 'Sulawesi Utara (Manado / Plat DB, DL)',
                'pulau'        => 'SULAWESI',
                'provinsi'     => 'SULAWESI UTARA',
                'odoo_bbn_id'  => 17,
            ],
            [
                'kode'         => 'PLU',
                'name'         => 'Sulawesi Tengah',
                'display_name' => 'Sulawesi Tengah (Palu / Plat DN)',
                'pulau'        => 'SULAWESI',
                'provinsi'     => 'SULAWESI TENGAH',
                'odoo_bbn_id'  => null,
            ],
            [
                'kode'         => 'MKS',
                'name'         => 'Sulawesi Selatan',
                'display_name' => 'Sulawesi Selatan (Makassar / Plat DD, DP, DW)',
                'pulau'        => 'SULAWESI',
                'provinsi'     => 'SULAWESI SELATAN',
                'odoo_bbn_id'  => 9,
            ],
            [
                'kode'         => 'KDR',
                'name'         => 'Sulawesi Tenggara',
                'display_name' => 'Sulawesi Tenggara (Kendari / Plat DT)',
                'pulau'        => 'SULAWESI',
                'provinsi'     => 'SULAWESI TENGGARA',
                'odoo_bbn_id'  => null,
            ],
            [
                'kode'         => 'GTO',
                'name'         => 'Gorontalo',
                'display_name' => 'Gorontalo (Gorontalo / Plat DM)',
                'pulau'        => 'SULAWESI',
                'provinsi'     => 'GORONTALO',
                'odoo_bbn_id'  => null,
            ],
            [
                'kode'         => 'MMJ',
                'name'         => 'Sulawesi Barat',
                'display_name' => 'Sulawesi Barat (Mamuju / Plat DC)',
                'pulau'        => 'SULAWESI',
                'provinsi'     => 'SULAWESI BARAT',
                'odoo_bbn_id'  => null,
            ],

            // ── PAPUA & MALUKU (8 Provinces) ─────────────────────────────────
            [
                'kode'         => 'AMB',
                'name'         => 'Maluku',
                'display_name' => 'Maluku (Ambon / Plat DE)',
                'pulau'        => 'PAPUA & MALUKU',
                'provinsi'     => 'MALUKU',
                'odoo_bbn_id'  => 18,
            ],
            [
                'kode'         => 'TTN',
                'name'         => 'Maluku Utara',
                'display_name' => 'Maluku Utara (Sofifi / Ternate / Plat DG)',
                'pulau'        => 'PAPUA & MALUKU',
                'provinsi'     => 'MALUKU UTARA',
                'odoo_bbn_id'  => null,
            ],
            [
                'kode'         => 'JYA',
                'name'         => 'Papua',
                'display_name' => 'Papua (Jayapura / Plat PA)',
                'pulau'        => 'PAPUA & MALUKU',
                'provinsi'     => 'PAPUA',
                'odoo_bbn_id'  => 20,
            ],
            [
                'kode'         => 'MKW',
                'name'         => 'Papua Barat',
                'display_name' => 'Papua Barat (Manokwari / Plat PB)',
                'pulau'        => 'PAPUA & MALUKU',
                'provinsi'     => 'PAPUA BARAT',
                'odoo_bbn_id'  => null,
            ],
            [
                'kode'         => 'MRK',
                'name'         => 'Papua Selatan',
                'display_name' => 'Papua Selatan (Merauke)',
                'pulau'        => 'PAPUA & MALUKU',
                'provinsi'     => 'PAPUA SELATAN',
                'odoo_bbn_id'  => null,
            ],
            [
                'kode'         => 'NBR',
                'name'         => 'Papua Tengah',
                'display_name' => 'Papua Tengah (Nabire)',
                'pulau'        => 'PAPUA & MALUKU',
                'provinsi'     => 'PAPUA TENGAH',
                'odoo_bbn_id'  => null,
            ],
            [
                'kode'         => 'WMN',
                'name'         => 'Papua Pegunungan',
                'display_name' => 'Papua Pegunungan (Jayawijaya / Wamena)',
                'pulau'        => 'PAPUA & MALUKU',
                'provinsi'     => 'PAPUA PEGUNUNGAN',
                'odoo_bbn_id'  => null,
            ],
            [
                'kode'         => 'SRG',
                'name'         => 'Papua Barat Daya',
                'display_name' => 'Papua Barat Daya (Sorong)',
                'pulau'        => 'PAPUA & MALUKU',
                'provinsi'     => 'PAPUA BARAT DAYA',
                'odoo_bbn_id'  => 26,
            ],
        ];

        // 1. Collect standard province codes
        $standardCodes = array_column($provinces, 'kode');

        // 2. Remove legacy non-standard city entries (previously dumped regencies/cities)
        // Keep only standard codes or those explicitly created with user ID
        DB::table('costing_bbn_masters')
            ->whereNotIn('kode', $standardCodes)
            ->whereNull('created_by')
            ->delete();

        // 3. Upsert clean 38 Provinces + Batam FTZ
        foreach ($provinces as $p) {
            $existing = DB::table('costing_bbn_masters')
                ->where('kode', $p['kode'])
                ->first();

            if ($existing) {
                DB::table('costing_bbn_masters')
                    ->where('id', $existing->id)
                    ->update([
                        'name'         => $p['name'],
                        'display_name' => $p['display_name'],
                        'pulau'        => $p['pulau'],
                        'provinsi'     => $p['provinsi'],
                        'odoo_bbn_id'  => $p['odoo_bbn_id'] ?? $existing->odoo_bbn_id,
                        'is_active'    => true,
                        'updated_at'   => now(),
                    ]);
            } else {
                DB::table('costing_bbn_masters')->insert([
                    'kode'         => $p['kode'],
                    'name'         => $p['name'],
                    'display_name' => $p['display_name'],
                    'pulau'        => $p['pulau'],
                    'provinsi'     => $p['provinsi'],
                    'odoo_bbn_id'  => $p['odoo_bbn_id'],
                    'is_active'    => true,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }

        // 4. Standardize existing vehicle BBN strings to clean official Province names
        if (Schema::hasTable('costing_vehicles')) {
            DB::table('costing_vehicles')
                ->whereIn('bbn', ['Jakarta', 'JAKARTA', 'Jabodetabek'])
                ->update(['bbn' => 'DKI Jakarta']);

            DB::table('costing_vehicles')
                ->where('bbn', 'Semarang')
                ->update(['bbn' => 'Jawa Tengah']);

            DB::table('costing_vehicles')
                ->whereIn('bbn', ['MAKASSAR', 'Makassar'])
                ->update(['bbn' => 'Sulawesi Selatan']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op or non-destructive reversal
    }
};
