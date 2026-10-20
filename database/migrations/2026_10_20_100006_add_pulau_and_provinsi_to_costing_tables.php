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
        // 1. Add pulau and provinsi columns to costing_vehicles
        if (!Schema::hasColumn('costing_vehicles', 'pulau')) {
            Schema::table('costing_vehicles', function (Blueprint $table) {
                $table->string('pulau', 100)->nullable()->after('sort_order')->index();
                $table->string('provinsi', 100)->nullable()->after('pulau')->index();
            });
        }

        // 2. Add pulau and provinsi columns to costing_bbn_masters
        if (!Schema::hasColumn('costing_bbn_masters', 'pulau')) {
            Schema::table('costing_bbn_masters', function (Blueprint $table) {
                $table->string('pulau', 100)->nullable()->after('display_name')->index();
                $table->string('provinsi', 100)->nullable()->after('pulau')->index();
            });
        }

        // 3. Populate complete Indonesian BBN baseline (208 cities across all 38 provinces and 6 island groups)
        // Zero seeders required, safe for fresh Portainer deployment
        $this->seedBbnMasterBaseline();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('costing_vehicles', 'pulau')) {
            Schema::table('costing_vehicles', function (Blueprint $table) {
                $table->dropColumn(['pulau', 'provinsi']);
            });
        }

        if (Schema::hasColumn('costing_bbn_masters', 'pulau')) {
            Schema::table('costing_bbn_masters', function (Blueprint $table) {
                $table->dropColumn(['pulau', 'provinsi']);
            });
        }
    }

    private function seedBbnMasterBaseline(): void
    {
        $now = now();
        $bbns = [
            ['kode' => 'ACH', 'name' => 'Aceh', 'display_name' => 'Aceh', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['kode' => 'AMB', 'name' => 'Ambon', 'display_name' => 'Ambon', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU'],
            ['kode' => 'BDG', 'name' => 'Bandung', 'display_name' => 'Bandung', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'BGK', 'name' => 'Bangka Belitung', 'display_name' => 'Bangka Belitung', 'pulau' => 'SUMATERA', 'provinsi' => 'BANGKA BELITUNG'],
            ['kode' => 'BKU', 'name' => 'Bengkulu', 'display_name' => 'Bengkulu', 'pulau' => 'SUMATERA', 'provinsi' => 'BENGKULU'],
            ['kode' => 'BPN', 'name' => 'Balikpapan', 'display_name' => 'Balikpapan', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TIMUR'],
            ['kode' => 'BTM', 'name' => 'Batam', 'display_name' => 'Batam', 'pulau' => 'SUMATERA', 'provinsi' => 'KEPULAUAN RIAU'],
            ['kode' => 'CLG', 'name' => 'Cilegon', 'display_name' => 'Cilegon', 'pulau' => 'JAWA', 'provinsi' => 'BANTEN'],
            ['kode' => 'CRB', 'name' => 'Cirebon', 'display_name' => 'Cirebon', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'DPS', 'name' => 'Denpasar / Bali', 'display_name' => 'Denpasar / Bali', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'BALI'],
            ['kode' => 'JKT', 'name' => 'Jakarta', 'display_name' => 'Jakarta', 'pulau' => 'JAWA', 'provinsi' => 'DKI JAKARTA'],
            ['kode' => 'JYA', 'name' => 'Jayapura', 'display_name' => 'Jayapura', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA'],
            ['kode' => 'KRW', 'name' => 'Karawang', 'display_name' => 'Karawang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'LMP', 'name' => 'Lampung', 'display_name' => 'Lampung', 'pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG'],
            ['kode' => 'MES', 'name' => 'Medan', 'display_name' => 'Medan', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['kode' => 'MKS', 'name' => 'Makassar', 'display_name' => 'Makassar', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['kode' => 'MLL', 'name' => 'Malili', 'display_name' => 'Malili', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['kode' => 'MND', 'name' => 'Manado', 'display_name' => 'Manado', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI UTARA'],
            ['kode' => 'MTM', 'name' => 'Mataram / Lombok', 'display_name' => 'Mataram / Lombok', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA BARAT'],
            ['kode' => 'PDG', 'name' => 'Padang', 'display_name' => 'Padang', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],
            ['kode' => 'PKU', 'name' => 'Pekanbaru', 'display_name' => 'Pekanbaru', 'pulau' => 'SUMATERA', 'provinsi' => 'RIAU'],
            ['kode' => 'PLB', 'name' => 'Palembang', 'display_name' => 'Palembang', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN'],
            ['kode' => 'PTA', 'name' => 'Pontianak', 'display_name' => 'Pontianak', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN BARAT'],
            ['kode' => 'RMB', 'name' => 'Rembang', 'display_name' => 'Rembang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'SMG', 'name' => 'Semarang', 'display_name' => 'Semarang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'SRG', 'name' => 'Sorong', 'display_name' => 'Sorong', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA BARAT DAYA'],
            ['kode' => 'SUB', 'name' => 'Surabaya', 'display_name' => 'Surabaya', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['kode' => 'TRK', 'name' => 'Tarakan', 'display_name' => 'Tarakan', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN UTARA'],
            ['kode' => 'YOG', 'name' => 'Yogyakarta', 'display_name' => 'Yogyakarta', 'pulau' => 'JAWA', 'provinsi' => 'DAERAH ISTIMEWA YOGYAKARTA'],
            ['kode' => 'JBD', 'name' => 'Jabodetabek', 'display_name' => 'Jabodetabek', 'pulau' => 'JAWA', 'provinsi' => 'DKI JAKARTA'],
            ['kode' => 'JKR', 'name' => 'Jakarta Pusat', 'display_name' => 'Jakarta Pusat', 'pulau' => 'JAWA', 'provinsi' => 'DKI JAKARTA'],
            ['kode' => 'JAK', 'name' => 'Jakarta Selatan', 'display_name' => 'Jakarta Selatan', 'pulau' => 'JAWA', 'provinsi' => 'DKI JAKARTA'],
            ['kode' => 'JAKA', 'name' => 'Jakarta Barat', 'display_name' => 'Jakarta Barat', 'pulau' => 'JAWA', 'provinsi' => 'DKI JAKARTA'],
            ['kode' => 'JAK1', 'name' => 'Jakarta Timur', 'display_name' => 'Jakarta Timur', 'pulau' => 'JAWA', 'provinsi' => 'DKI JAKARTA'],
            ['kode' => 'JAK2', 'name' => 'Jakarta Utara', 'display_name' => 'Jakarta Utara', 'pulau' => 'JAWA', 'provinsi' => 'DKI JAKARTA'],
            ['kode' => 'KPL', 'name' => 'Kepulauan Seribu', 'display_name' => 'Kepulauan Seribu', 'pulau' => 'JAWA', 'provinsi' => 'DKI JAKARTA'],
            ['kode' => 'KBP', 'name' => 'Kabupaten Bandung', 'display_name' => 'Kabupaten Bandung', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'BND', 'name' => 'Bandung Barat', 'display_name' => 'Bandung Barat', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'CMH', 'name' => 'Cimahi', 'display_name' => 'Cimahi', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'BGR', 'name' => 'Bogor', 'display_name' => 'Bogor', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'KAB', 'name' => 'Kabupaten Bogor', 'display_name' => 'Kabupaten Bogor', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'DPK', 'name' => 'Depok', 'display_name' => 'Depok', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'BKS', 'name' => 'Bekasi', 'display_name' => 'Bekasi', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'KABU', 'name' => 'Kabupaten Bekasi', 'display_name' => 'Kabupaten Bekasi', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'SKB', 'name' => 'Sukabumi', 'display_name' => 'Sukabumi', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'KAB1', 'name' => 'Kabupaten Sukabumi', 'display_name' => 'Kabupaten Sukabumi', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'PRW', 'name' => 'Purwakarta', 'display_name' => 'Purwakarta', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'SBN', 'name' => 'Subang', 'display_name' => 'Subang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'KAB2', 'name' => 'Kabupaten Cirebon', 'display_name' => 'Kabupaten Cirebon', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'IND', 'name' => 'Indramayu', 'display_name' => 'Indramayu', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'MJL', 'name' => 'Majalengka', 'display_name' => 'Majalengka', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'KNN', 'name' => 'Kuningan', 'display_name' => 'Kuningan', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'SMD', 'name' => 'Sumedang', 'display_name' => 'Sumedang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'GRT', 'name' => 'Garut', 'display_name' => 'Garut', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'TSK', 'name' => 'Tasikmalaya', 'display_name' => 'Tasikmalaya', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'CMS', 'name' => 'Ciamis', 'display_name' => 'Ciamis', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'BNJ', 'name' => 'Banjar', 'display_name' => 'Banjar', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'PNG', 'name' => 'Pangandaran', 'display_name' => 'Pangandaran', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['kode' => 'TNG', 'name' => 'Tangerang', 'display_name' => 'Tangerang', 'pulau' => 'JAWA', 'provinsi' => 'BANTEN'],
            ['kode' => 'TAN', 'name' => 'Tangerang Selatan', 'display_name' => 'Tangerang Selatan', 'pulau' => 'JAWA', 'provinsi' => 'BANTEN'],
            ['kode' => 'KAB3', 'name' => 'Kabupaten Tangerang', 'display_name' => 'Kabupaten Tangerang', 'pulau' => 'JAWA', 'provinsi' => 'BANTEN'],
            ['kode' => 'SRN', 'name' => 'Serang', 'display_name' => 'Serang', 'pulau' => 'JAWA', 'provinsi' => 'BANTEN'],
            ['kode' => 'LBK', 'name' => 'Lebak', 'display_name' => 'Lebak', 'pulau' => 'JAWA', 'provinsi' => 'BANTEN'],
            ['kode' => 'PND', 'name' => 'Pandeglang', 'display_name' => 'Pandeglang', 'pulau' => 'JAWA', 'provinsi' => 'BANTEN'],
            ['kode' => 'KLG', 'name' => 'Kaligawe', 'display_name' => 'Kaligawe', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'UNG', 'name' => 'Ungaran', 'display_name' => 'Ungaran', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'SLT', 'name' => 'Salatiga', 'display_name' => 'Salatiga', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'PKL', 'name' => 'Pekalongan', 'display_name' => 'Pekalongan', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'BTN', 'name' => 'Batang', 'display_name' => 'Batang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'TGL', 'name' => 'Tegal', 'display_name' => 'Tegal', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'BRB', 'name' => 'Brebes', 'display_name' => 'Brebes', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'PML', 'name' => 'Pemalang', 'display_name' => 'Pemalang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'KND', 'name' => 'Kendal', 'display_name' => 'Kendal', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'DMK', 'name' => 'Demak', 'display_name' => 'Demak', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'KDS', 'name' => 'Kudus', 'display_name' => 'Kudus', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'PAT', 'name' => 'Pati', 'display_name' => 'Pati', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'JPR', 'name' => 'Jepara', 'display_name' => 'Jepara', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'BLR', 'name' => 'Blora', 'display_name' => 'Blora', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'GRB', 'name' => 'Grobogan', 'display_name' => 'Grobogan', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'SLS', 'name' => 'Solo / Surakarta', 'display_name' => 'Solo / Surakarta', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'SKH', 'name' => 'Sukoharjo', 'display_name' => 'Sukoharjo', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'BYL', 'name' => 'Boyolali', 'display_name' => 'Boyolali', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'KLT', 'name' => 'Klaten', 'display_name' => 'Klaten', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'KRN', 'name' => 'Karanganyar', 'display_name' => 'Karanganyar', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'WNG', 'name' => 'Wonogiri', 'display_name' => 'Wonogiri', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'SRA', 'name' => 'Sragen', 'display_name' => 'Sragen', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'MGL', 'name' => 'Magelang', 'display_name' => 'Magelang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'PUR', 'name' => 'Purworejo', 'display_name' => 'Purworejo', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'TMN', 'name' => 'Temanggung', 'display_name' => 'Temanggung', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'WNS', 'name' => 'Wonosobo', 'display_name' => 'Wonosobo', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'KBM', 'name' => 'Kebumen', 'display_name' => 'Kebumen', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'BNY', 'name' => 'Banyumas / Purwokerto', 'display_name' => 'Banyumas / Purwokerto', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'CLC', 'name' => 'Cilacap', 'display_name' => 'Cilacap', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'PRB', 'name' => 'Purbalingga', 'display_name' => 'Purbalingga', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'BAN', 'name' => 'Banjarnegara', 'display_name' => 'Banjarnegara', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['kode' => 'SLM', 'name' => 'Sleman', 'display_name' => 'Sleman', 'pulau' => 'JAWA', 'provinsi' => 'DAERAH ISTIMEWA YOGYAKARTA'],
            ['kode' => 'BNT', 'name' => 'Bantul', 'display_name' => 'Bantul', 'pulau' => 'JAWA', 'provinsi' => 'DAERAH ISTIMEWA YOGYAKARTA'],
            ['kode' => 'KLN', 'name' => 'Kulon Progo', 'display_name' => 'Kulon Progo', 'pulau' => 'JAWA', 'provinsi' => 'DAERAH ISTIMEWA YOGYAKARTA'],
            ['kode' => 'GNN', 'name' => 'Gunungkidul', 'display_name' => 'Gunungkidul', 'pulau' => 'JAWA', 'provinsi' => 'DAERAH ISTIMEWA YOGYAKARTA'],
            ['kode' => 'SDR', 'name' => 'Sidoarjo', 'display_name' => 'Sidoarjo', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['kode' => 'GRS', 'name' => 'Gresik', 'display_name' => 'Gresik', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['kode' => 'MJK', 'name' => 'Mojokerto', 'display_name' => 'Mojokerto', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['kode' => 'PSR', 'name' => 'Pasuruan', 'display_name' => 'Pasuruan', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['kode' => 'MLN', 'name' => 'Malang', 'display_name' => 'Malang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['kode' => 'BAT', 'name' => 'Batu', 'display_name' => 'Batu', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['kode' => 'PRO', 'name' => 'Probolinggo', 'display_name' => 'Probolinggo', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['kode' => 'JMB', 'name' => 'Jember', 'display_name' => 'Jember', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['kode' => 'BANY', 'name' => 'Banyuwangi', 'display_name' => 'Banyuwangi', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['kode' => 'KDR', 'name' => 'Kediri', 'display_name' => 'Kediri', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['kode' => 'BLT', 'name' => 'Blitar', 'display_name' => 'Blitar', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['kode' => 'MDN', 'name' => 'Madiun', 'display_name' => 'Madiun', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['kode' => 'TBN', 'name' => 'Tuban', 'display_name' => 'Tuban', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['kode' => 'LMN', 'name' => 'Lamongan', 'display_name' => 'Lamongan', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['kode' => 'BJN', 'name' => 'Bojonegoro', 'display_name' => 'Bojonegoro', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['kode' => 'JOM', 'name' => 'Jombang', 'display_name' => 'Jombang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['kode' => 'BAND', 'name' => 'Banda Aceh', 'display_name' => 'Banda Aceh', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['kode' => 'SAB', 'name' => 'Sabang', 'display_name' => 'Sabang', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['kode' => 'LHK', 'name' => 'Lhokseumawe', 'display_name' => 'Lhokseumawe', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['kode' => 'LNG', 'name' => 'Langsa', 'display_name' => 'Langsa', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['kode' => 'BIN', 'name' => 'Binjai', 'display_name' => 'Binjai', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['kode' => 'PMT', 'name' => 'Pematangsiantar', 'display_name' => 'Pematangsiantar', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['kode' => 'DLS', 'name' => 'Deli Serdang', 'display_name' => 'Deli Serdang', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['kode' => 'KEP', 'name' => 'Kepulauan Nias', 'display_name' => 'Kepulauan Nias', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['kode' => 'GUN', 'name' => 'Gunungsitoli', 'display_name' => 'Gunungsitoli', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['kode' => 'ASH', 'name' => 'Asahan', 'display_name' => 'Asahan', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['kode' => 'KAR', 'name' => 'Karo', 'display_name' => 'Karo', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['kode' => 'BKT', 'name' => 'Bukittinggi', 'display_name' => 'Bukittinggi', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],
            ['kode' => 'PDN', 'name' => 'Padang Panjang', 'display_name' => 'Padang Panjang', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],
            ['kode' => 'PYK', 'name' => 'Payakumbuh', 'display_name' => 'Payakumbuh', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],
            ['kode' => 'PRM', 'name' => 'Pariaman', 'display_name' => 'Pariaman', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],
            ['kode' => 'DUM', 'name' => 'Dumai', 'display_name' => 'Dumai', 'pulau' => 'SUMATERA', 'provinsi' => 'RIAU'],
            ['kode' => 'KMP', 'name' => 'Kampar', 'display_name' => 'Kampar', 'pulau' => 'SUMATERA', 'provinsi' => 'RIAU'],
            ['kode' => 'SIA', 'name' => 'Siak', 'display_name' => 'Siak', 'pulau' => 'SUMATERA', 'provinsi' => 'RIAU'],
            ['kode' => 'BNG', 'name' => 'Bengkalis', 'display_name' => 'Bengkalis', 'pulau' => 'SUMATERA', 'provinsi' => 'RIAU'],
            ['kode' => 'TNJ', 'name' => 'Tanjungpinang', 'display_name' => 'Tanjungpinang', 'pulau' => 'SUMATERA', 'provinsi' => 'KEPULAUAN RIAU'],
            ['kode' => 'BINT', 'name' => 'Bintan', 'display_name' => 'Bintan', 'pulau' => 'SUMATERA', 'provinsi' => 'KEPULAUAN RIAU'],
            ['kode' => 'KRM', 'name' => 'Karimun', 'display_name' => 'Karimun', 'pulau' => 'SUMATERA', 'provinsi' => 'KEPULAUAN RIAU'],
            ['kode' => 'JAM', 'name' => 'Jambi', 'display_name' => 'Jambi', 'pulau' => 'SUMATERA', 'provinsi' => 'JAMBI'],
            ['kode' => 'MRJ', 'name' => 'Muaro Jambi', 'display_name' => 'Muaro Jambi', 'pulau' => 'SUMATERA', 'provinsi' => 'JAMBI'],
            ['kode' => 'BUN', 'name' => 'Bungo', 'display_name' => 'Bungo', 'pulau' => 'SUMATERA', 'provinsi' => 'JAMBI'],
            ['kode' => 'PRA', 'name' => 'Prabumulih', 'display_name' => 'Prabumulih', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN'],
            ['kode' => 'LUB', 'name' => 'Lubuklinggau', 'display_name' => 'Lubuklinggau', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN'],
            ['kode' => 'BAN1', 'name' => 'Banyuasin', 'display_name' => 'Banyuasin', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN'],
            ['kode' => 'PAN', 'name' => 'Pangkalpinang', 'display_name' => 'Pangkalpinang', 'pulau' => 'SUMATERA', 'provinsi' => 'BANGKA BELITUNG'],
            ['kode' => 'BANG', 'name' => 'Bangka', 'display_name' => 'Bangka', 'pulau' => 'SUMATERA', 'provinsi' => 'BANGKA BELITUNG'],
            ['kode' => 'BEL', 'name' => 'Belitung', 'display_name' => 'Belitung', 'pulau' => 'SUMATERA', 'provinsi' => 'BANGKA BELITUNG'],
            ['kode' => 'RJN', 'name' => 'Rejang Lebong', 'display_name' => 'Rejang Lebong', 'pulau' => 'SUMATERA', 'provinsi' => 'BENGKULU'],
            ['kode' => 'BAN2', 'name' => 'Bandar Lampung', 'display_name' => 'Bandar Lampung', 'pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG'],
            ['kode' => 'MTR', 'name' => 'Metro', 'display_name' => 'Metro', 'pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG'],
            ['kode' => 'LAM', 'name' => 'Lampung Selatan', 'display_name' => 'Lampung Selatan', 'pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG'],
            ['kode' => 'LAMP', 'name' => 'Lampung Tengah', 'display_name' => 'Lampung Tengah', 'pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG'],
            ['kode' => 'SNG', 'name' => 'Singkawang', 'display_name' => 'Singkawang', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN BARAT'],
            ['kode' => 'KBR', 'name' => 'Kubu Raya', 'display_name' => 'Kubu Raya', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN BARAT'],
            ['kode' => 'KTP', 'name' => 'Ketapang', 'display_name' => 'Ketapang', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN BARAT'],
            ['kode' => 'PLN', 'name' => 'Palangka Raya', 'display_name' => 'Palangka Raya', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TENGAH'],
            ['kode' => 'SMP', 'name' => 'Sampit', 'display_name' => 'Sampit', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TENGAH'],
            ['kode' => 'PANG', 'name' => 'Pangkalan Bun', 'display_name' => 'Pangkalan Bun', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TENGAH'],
            ['kode' => 'BANJ', 'name' => 'Banjarmasin', 'display_name' => 'Banjarmasin', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN SELATAN'],
            ['kode' => 'BAN3', 'name' => 'Banjarbaru', 'display_name' => 'Banjarbaru', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN SELATAN'],
            ['kode' => 'MRT', 'name' => 'Martapura', 'display_name' => 'Martapura', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN SELATAN'],
            ['kode' => 'TBL', 'name' => 'Tabalong', 'display_name' => 'Tabalong', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN SELATAN'],
            ['kode' => 'SMR', 'name' => 'Samarinda', 'display_name' => 'Samarinda', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TIMUR'],
            ['kode' => 'BON', 'name' => 'Bontang', 'display_name' => 'Bontang', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TIMUR'],
            ['kode' => 'KTK', 'name' => 'Kutai Kartanegara', 'display_name' => 'Kutai Kartanegara', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TIMUR'],
            ['kode' => 'PNJ', 'name' => 'Penajam Paser Utara / IKN', 'display_name' => 'Penajam Paser Utara / IKN', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TIMUR'],
            ['kode' => 'BER', 'name' => 'Berau', 'display_name' => 'Berau', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TIMUR'],
            ['kode' => 'TANJ', 'name' => 'Tanjung Selor', 'display_name' => 'Tanjung Selor', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN UTARA'],
            ['kode' => 'NNK', 'name' => 'Nunukan', 'display_name' => 'Nunukan', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN UTARA'],
            ['kode' => 'GOW', 'name' => 'Gowa', 'display_name' => 'Gowa', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['kode' => 'MRS', 'name' => 'Maros', 'display_name' => 'Maros', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['kode' => 'PRP', 'name' => 'Parepare', 'display_name' => 'Parepare', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['kode' => 'PLP', 'name' => 'Palopo', 'display_name' => 'Palopo', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['kode' => 'LWT', 'name' => 'Luwu Timur', 'display_name' => 'Luwu Timur', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['kode' => 'BONE', 'name' => 'Bone', 'display_name' => 'Bone', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['kode' => 'BIT', 'name' => 'Bitung', 'display_name' => 'Bitung', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI UTARA'],
            ['kode' => 'TMH', 'name' => 'Tomohon', 'display_name' => 'Tomohon', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI UTARA'],
            ['kode' => 'KTM', 'name' => 'Kotamobagu', 'display_name' => 'Kotamobagu', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI UTARA'],
            ['kode' => 'PAL', 'name' => 'Palu', 'display_name' => 'Palu', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGAH'],
            ['kode' => 'MRW', 'name' => 'Morowali', 'display_name' => 'Morowali', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGAH'],
            ['kode' => 'LWK', 'name' => 'Luwuk / Banggai', 'display_name' => 'Luwuk / Banggai', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGAH'],
            ['kode' => 'KEN', 'name' => 'Kendari', 'display_name' => 'Kendari', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGGARA'],
            ['kode' => 'BAU', 'name' => 'Baubau', 'display_name' => 'Baubau', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGGARA'],
            ['kode' => 'KLK', 'name' => 'Kolaka', 'display_name' => 'Kolaka', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGGARA'],
            ['kode' => 'GRN', 'name' => 'Gorontalo', 'display_name' => 'Gorontalo', 'pulau' => 'SULAWESI', 'provinsi' => 'GORONTALO'],
            ['kode' => 'MMJ', 'name' => 'Mamuju', 'display_name' => 'Mamuju', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI BARAT'],
            ['kode' => 'DNP', 'name' => 'Denpasar', 'display_name' => 'Denpasar', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'BALI'],
            ['kode' => 'BDN', 'name' => 'Badung', 'display_name' => 'Badung', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'BALI'],
            ['kode' => 'GNY', 'name' => 'Gianyar', 'display_name' => 'Gianyar', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'BALI'],
            ['kode' => 'TAB', 'name' => 'Tabanan', 'display_name' => 'Tabanan', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'BALI'],
            ['kode' => 'BLL', 'name' => 'Buleleng', 'display_name' => 'Buleleng', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'BALI'],
            ['kode' => 'MAT', 'name' => 'Mataram', 'display_name' => 'Mataram', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA BARAT'],
            ['kode' => 'LMB', 'name' => 'Lombok Barat', 'display_name' => 'Lombok Barat', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA BARAT'],
            ['kode' => 'SMB', 'name' => 'Sumbawa', 'display_name' => 'Sumbawa', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA BARAT'],
            ['kode' => 'BIM', 'name' => 'Bima', 'display_name' => 'Bima', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA BARAT'],
            ['kode' => 'KPN', 'name' => 'Kupang', 'display_name' => 'Kupang', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['kode' => 'LBN', 'name' => 'Labuan Bajo', 'display_name' => 'Labuan Bajo', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['kode' => 'MMR', 'name' => 'Maumere', 'display_name' => 'Maumere', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['kode' => 'END', 'name' => 'Ende', 'display_name' => 'Ende', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['kode' => 'TUA', 'name' => 'Tual', 'display_name' => 'Tual', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU'],
            ['kode' => 'TRN', 'name' => 'Ternate', 'display_name' => 'Ternate', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU UTARA'],
            ['kode' => 'TDR', 'name' => 'Tidore', 'display_name' => 'Tidore', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU UTARA'],
            ['kode' => 'BIA', 'name' => 'Biak', 'display_name' => 'Biak', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA'],
            ['kode' => 'MNK', 'name' => 'Manokwari', 'display_name' => 'Manokwari', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA BARAT'],
            ['kode' => 'RJM', 'name' => 'Raja Ampat', 'display_name' => 'Raja Ampat', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA BARAT DAYA'],
            ['kode' => 'TMK', 'name' => 'Timika', 'display_name' => 'Timika', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA TENGAH'],
            ['kode' => 'NBR', 'name' => 'Nabire', 'display_name' => 'Nabire', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA TENGAH'],
            ['kode' => 'MRK', 'name' => 'Merauke', 'display_name' => 'Merauke', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA SELATAN'],
            ['kode' => 'WMN', 'name' => 'Wamena', 'display_name' => 'Wamena', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA PEGUNUNGAN'],
        ];

        foreach ($bbns as $b) {
            $existing = DB::table('costing_bbn_masters')->where('kode', $b['kode'])->first();
            if ($existing) {
                DB::table('costing_bbn_masters')->where('id', $existing->id)->update([
                    'name'         => $b['name'],
                    'display_name' => $b['display_name'],
                    'pulau'        => $b['pulau'],
                    'provinsi'     => $b['provinsi'],
                    'updated_at'   => $now,
                ]);
            } else {
                DB::table('costing_bbn_masters')->insert([
                    'odoo_bbn_id'  => null,
                    'kode'         => $b['kode'],
                    'name'         => $b['name'],
                    'display_name' => $b['display_name'],
                    'pulau'        => $b['pulau'],
                    'provinsi'     => $b['provinsi'],
                    'is_active'    => 1,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]);
            }
        }
    }
};