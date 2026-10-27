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
        // 1. Create costing_lokasi_pemakaians table
        if (!Schema::hasTable('costing_lokasi_pemakaians')) {
            Schema::create('costing_lokasi_pemakaians', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150)->index();
                $table->string('pulau', 100)->index();
                $table->string('provinsi', 100)->index();
                $table->boolean('is_active')->default(true)->index();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 2. Add vehicle note column to costing_vehicles table
        if (!Schema::hasColumn('costing_vehicles', 'note')) {
            Schema::table('costing_vehicles', function (Blueprint $table) {
                $table->text('note')->nullable()->after('grand_total');
            });
        }

        // 3. Seed complete 514 official Indonesian Kota & Kabupaten baseline (Zero seeders required)
        $this->seedOfficialLokasiPemakaianBaseline();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('costing_lokasi_pemakaians');

        if (Schema::hasColumn('costing_vehicles', 'note')) {
            Schema::table('costing_vehicles', function (Blueprint $table) {
                $table->dropColumn('note');
            });
        }
    }

    /**
     * Populates all 514 official Indonesian Kota & Kabupaten across all 38 provinces & 6 island groups.
     */
    private function seedOfficialLokasiPemakaianBaseline(): void
    {
        $now = now();
        $regions = [
            // =========================================================================
            // 1. JAWA (6 Provinces - 119 Kota/Kab)
            // =========================================================================
            // DKI JAKARTA (6)
            ['name' => 'Jakarta Pusat', 'pulau' => 'JAWA', 'provinsi' => 'DKI JAKARTA'],
            ['name' => 'Jakarta Barat', 'pulau' => 'JAWA', 'provinsi' => 'DKI JAKARTA'],
            ['name' => 'Jakarta Selatan', 'pulau' => 'JAWA', 'provinsi' => 'DKI JAKARTA'],
            ['name' => 'Jakarta Timur', 'pulau' => 'JAWA', 'provinsi' => 'DKI JAKARTA'],
            ['name' => 'Jakarta Utara', 'pulau' => 'JAWA', 'provinsi' => 'DKI JAKARTA'],
            ['name' => 'Kepulauan Seribu', 'pulau' => 'JAWA', 'provinsi' => 'DKI JAKARTA'],

            // JAWA BARAT (27)
            ['name' => 'Bandung', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Bandung Barat', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Bekasi', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Bogor', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Ciamis', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Cianjur', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Cirebon', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Garut', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Indramayu', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Karawang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Kuningan', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Majalengka', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Pangandaran', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Purwakarta', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Subang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Sukabumi', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Sumedang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Tasikmalaya', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Kota Bandung', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Kota Banjar', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Kota Bekasi', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Kota Bogor', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Kota Cimahi', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Kota Cirebon', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Kota Depok', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Kota Sukabumi', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],
            ['name' => 'Kota Tasikmalaya', 'pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT'],

            // JAWA TENGAH (35)
            ['name' => 'Banjarnegara', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Banyumas', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Batang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Blora', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Boyolali', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Brebes', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Cilacap', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Demak', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Grobogan', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Jepara', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Karanganyar', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Kebumen', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Kendal', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Klaten', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Kudus', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Magelang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Pati', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Pekalongan', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Pemalang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Purbalingga', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Purworejo', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Rembang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Semarang (Ungaran)', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Sragen', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Sukoharjo', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Tegal', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Temanggung', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Wonogiri', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Wonosobo', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Kota Magelang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Kota Pekalongan', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Kota Salatiga', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Kota Semarang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Kota Surakarta (Solo)', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],
            ['name' => 'Kota Tegal', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH'],

            // D.I. YOGYAKARTA (5)
            ['name' => 'Bantul', 'pulau' => 'JAWA', 'provinsi' => 'D.I. YOGYAKARTA'],
            ['name' => 'Gunungkidul', 'pulau' => 'JAWA', 'provinsi' => 'D.I. YOGYAKARTA'],
            ['name' => 'Kulon Progo', 'pulau' => 'JAWA', 'provinsi' => 'D.I. YOGYAKARTA'],
            ['name' => 'Sleman', 'pulau' => 'JAWA', 'provinsi' => 'D.I. YOGYAKARTA'],
            ['name' => 'Kota Yogyakarta', 'pulau' => 'JAWA', 'provinsi' => 'D.I. YOGYAKARTA'],

            // JAWA TIMUR (38)
            ['name' => 'Bangkalan', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Banyuwangi', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Blitar', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Bojonegoro', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Bondowoso', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Gresik', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Jember', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Jombang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Kediri', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Lamongan', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Lumajang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Madiun', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Magetan', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Malang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Mojokerto', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Nganjuk', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Ngawi', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Pacitan', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Pamekasan', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Pasuruan', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Ponorogo', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Probolinggo', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Sampang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Sidoarjo', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Situbondo', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Sumenep', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Trenggalek', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Tuban', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Tulungagung', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Kota Batu', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Kota Blitar', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Kota Kediri', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Kota Madiun', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Kota Malang', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Kota Mojokerto', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Kota Pasuruan', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Kota Probolinggo', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],
            ['name' => 'Kota Surabaya', 'pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR'],

            // BANTEN (8)
            ['name' => 'Lebak', 'pulau' => 'JAWA', 'provinsi' => 'BANTEN'],
            ['name' => 'Pandeglang', 'pulau' => 'JAWA', 'provinsi' => 'BANTEN'],
            ['name' => 'Serang', 'pulau' => 'JAWA', 'provinsi' => 'BANTEN'],
            ['name' => 'Tangerang', 'pulau' => 'JAWA', 'provinsi' => 'BANTEN'],
            ['name' => 'Kota Cilegon', 'pulau' => 'JAWA', 'provinsi' => 'BANTEN'],
            ['name' => 'Kota Serang', 'pulau' => 'JAWA', 'provinsi' => 'BANTEN'],
            ['name' => 'Kota Tangerang', 'pulau' => 'JAWA', 'provinsi' => 'BANTEN'],
            ['name' => 'Kota Tangerang Selatan', 'pulau' => 'JAWA', 'provinsi' => 'BANTEN'],

            // =========================================================================
            // 2. SUMATERA (10 Provinces - 154 Kota/Kab)
            // =========================================================================
            // ACEH (23)
            ['name' => 'Aceh Barat', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['name' => 'Aceh Barat Daya', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['name' => 'Aceh Besar', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['name' => 'Aceh Jaya', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['name' => 'Aceh Selatan', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['name' => 'Aceh Singkil', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['name' => 'Aceh Tamiang', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['name' => 'Aceh Tengah', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['name' => 'Aceh Tenggara', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['name' => 'Aceh Timur', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['name' => 'Aceh Utara', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['name' => 'Bener Meriah', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['name' => 'Bireuen', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['name' => 'Gayo Lues', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['name' => 'Nagan Raya', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['name' => 'Pidie', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['name' => 'Pidie Jaya', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['name' => 'Simeulue', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['name' => 'Kota Banda Aceh', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['name' => 'Kota Langsa', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['name' => 'Kota Lhokseumawe', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['name' => 'Kota Sabang', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],
            ['name' => 'Kota Subulussalam', 'pulau' => 'SUMATERA', 'provinsi' => 'ACEH'],

            // SUMATERA UTARA (33)
            ['name' => 'Asahan', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Batu Bara', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Dairi', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Deli Serdang', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Humbang Hasundutan', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Karo', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Labuhanbatu', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Labuhanbatu Selatan', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Labuhanbatu Utara', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Langkat', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Mandailing Natal', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Nias', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Nias Barat', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Nias Selatan', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Nias Utara', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Padang Lawas', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Padang Lawas Utara', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Pakpak Bharat', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Samosir', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Serdang Bedagai', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Simalungun', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Tapanuli Selatan', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Tapanuli Tengah', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Tapanuli Utara', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Toba', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Kota Binjai', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Kota Gunungsitoli', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Kota Medan', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Kota Padang Sidempuan', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Kota Pematang Siantar', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Kota Sibolga', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Kota Tanjung Balai', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],
            ['name' => 'Kota Tebing Tinggi', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA'],

            // SUMATERA BARAT (19)
            ['name' => 'Agam', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],
            ['name' => 'Dharmasraya', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],
            ['name' => 'Kepulauan Mentawai', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],
            ['name' => 'Lima Puluh Kota', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],
            ['name' => 'Padang Pariaman', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],
            ['name' => 'Pasaman', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],
            ['name' => 'Pasaman Barat', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],
            ['name' => 'Pesisir Selatan', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],
            ['name' => 'Sijunjung', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],
            ['name' => 'Solok', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],
            ['name' => 'Solok Selatan', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],
            ['name' => 'Tanah Datar', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],
            ['name' => 'Kota Bukittinggi', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],
            ['name' => 'Kota Padang', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],
            ['name' => 'Kota Padang Panjang', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],
            ['name' => 'Kota Pariaman', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],
            ['name' => 'Kota Payakumbuh', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],
            ['name' => 'Kota Sawahlunto', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],
            ['name' => 'Kota Solok', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT'],

            // RIAU (12)
            ['name' => 'Bengkalis', 'pulau' => 'SUMATERA', 'provinsi' => 'RIAU'],
            ['name' => 'Indragiri Hilir', 'pulau' => 'SUMATERA', 'provinsi' => 'RIAU'],
            ['name' => 'Indragiri Hulu', 'pulau' => 'SUMATERA', 'provinsi' => 'RIAU'],
            ['name' => 'Kampar', 'pulau' => 'SUMATERA', 'provinsi' => 'RIAU'],
            ['name' => 'Kepulauan Meranti', 'pulau' => 'SUMATERA', 'provinsi' => 'RIAU'],
            ['name' => 'Kuantan Singingi', 'pulau' => 'SUMATERA', 'provinsi' => 'RIAU'],
            ['name' => 'Pelalawan', 'pulau' => 'SUMATERA', 'provinsi' => 'RIAU'],
            ['name' => 'Rokan Hilir', 'pulau' => 'SUMATERA', 'provinsi' => 'RIAU'],
            ['name' => 'Rokan Hulu', 'pulau' => 'SUMATERA', 'provinsi' => 'RIAU'],
            ['name' => 'Siak', 'pulau' => 'SUMATERA', 'provinsi' => 'RIAU'],
            ['name' => 'Kota Dumai', 'pulau' => 'SUMATERA', 'provinsi' => 'RIAU'],
            ['name' => 'Kota Pekanbaru', 'pulau' => 'SUMATERA', 'provinsi' => 'RIAU'],

            // KEPULAUAN RIAU (7)
            ['name' => 'Bintan', 'pulau' => 'SUMATERA', 'provinsi' => 'KEPULAUAN RIAU'],
            ['name' => 'Karimun', 'pulau' => 'SUMATERA', 'provinsi' => 'KEPULAUAN RIAU'],
            ['name' => 'Kepulauan Anambas', 'pulau' => 'SUMATERA', 'provinsi' => 'KEPULAUAN RIAU'],
            ['name' => 'Lingga', 'pulau' => 'SUMATERA', 'provinsi' => 'KEPULAUAN RIAU'],
            ['name' => 'Natuna', 'pulau' => 'SUMATERA', 'provinsi' => 'KEPULAUAN RIAU'],
            ['name' => 'Kota Batam', 'pulau' => 'SUMATERA', 'provinsi' => 'KEPULAUAN RIAU'],
            ['name' => 'Kota Tanjung Pinang', 'pulau' => 'SUMATERA', 'provinsi' => 'KEPULAUAN RIAU'],

            // JAMBI (11)
            ['name' => 'Batanghari', 'pulau' => 'SUMATERA', 'provinsi' => 'JAMBI'],
            ['name' => 'Bungo', 'pulau' => 'SUMATERA', 'provinsi' => 'JAMBI'],
            ['name' => 'Kerinci', 'pulau' => 'SUMATERA', 'provinsi' => 'JAMBI'],
            ['name' => 'Merangin', 'pulau' => 'SUMATERA', 'provinsi' => 'JAMBI'],
            ['name' => 'Muaro Jambi', 'pulau' => 'SUMATERA', 'provinsi' => 'JAMBI'],
            ['name' => 'Sarolangun', 'pulau' => 'SUMATERA', 'provinsi' => 'JAMBI'],
            ['name' => 'Tanjung Jabung Barat', 'pulau' => 'SUMATERA', 'provinsi' => 'JAMBI'],
            ['name' => 'Tanjung Jabung Timur', 'pulau' => 'SUMATERA', 'provinsi' => 'JAMBI'],
            ['name' => 'Tebo', 'pulau' => 'SUMATERA', 'provinsi' => 'JAMBI'],
            ['name' => 'Kota Jambi', 'pulau' => 'SUMATERA', 'provinsi' => 'JAMBI'],
            ['name' => 'Kota Sungai Penuh', 'pulau' => 'SUMATERA', 'provinsi' => 'JAMBI'],

            // SUMATERA SELATAN (17)
            ['name' => 'Banyuasin', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN'],
            ['name' => 'Empat Lawang', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN'],
            ['name' => 'Lahat', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN'],
            ['name' => 'Muara Enim', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN'],
            ['name' => 'Musi Banyuasin', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN'],
            ['name' => 'Musi Rawas', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN'],
            ['name' => 'Musi Rawas Utara', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN'],
            ['name' => 'Ogan Ilir', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN'],
            ['name' => 'Ogan Komering Ilir', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN'],
            ['name' => 'Ogan Komering Ulu', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN'],
            ['name' => 'Ogan Komering Ulu Selatan', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN'],
            ['name' => 'Ogan Komering Ulu Timur', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN'],
            ['name' => 'Penukal Abab Lematang Ilir', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN'],
            ['name' => 'Kota Lubuk Linggau', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN'],
            ['name' => 'Kota Pagar Alam', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN'],
            ['name' => 'Kota Palembang', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN'],
            ['name' => 'Kota Prabumulih', 'pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN'],

            // KEPULAUAN BANGKA BELITUNG (7)
            ['name' => 'Bangka', 'pulau' => 'SUMATERA', 'provinsi' => 'KEPULAUAN BANGKA BELITUNG'],
            ['name' => 'Bangka Barat', 'pulau' => 'SUMATERA', 'provinsi' => 'KEPULAUAN BANGKA BELITUNG'],
            ['name' => 'Bangka Selatan', 'pulau' => 'SUMATERA', 'provinsi' => 'KEPULAUAN BANGKA BELITUNG'],
            ['name' => 'Bangka Tengah', 'pulau' => 'SUMATERA', 'provinsi' => 'KEPULAUAN BANGKA BELITUNG'],
            ['name' => 'Belitung', 'pulau' => 'SUMATERA', 'provinsi' => 'KEPULAUAN BANGKA BELITUNG'],
            ['name' => 'Belitung Timur', 'pulau' => 'SUMATERA', 'provinsi' => 'KEPULAUAN BANGKA BELITUNG'],
            ['name' => 'Kota Pangkal Pinang', 'pulau' => 'SUMATERA', 'provinsi' => 'KEPULAUAN BANGKA BELITUNG'],

            // BENGKULU (10)
            ['name' => 'Bengkulu Selatan', 'pulau' => 'SUMATERA', 'provinsi' => 'BENGKULU'],
            ['name' => 'Bengkulu Tengah', 'pulau' => 'SUMATERA', 'provinsi' => 'BENGKULU'],
            ['name' => 'Bengkulu Utara', 'pulau' => 'SUMATERA', 'provinsi' => 'BENGKULU'],
            ['name' => 'Kaur', 'pulau' => 'SUMATERA', 'provinsi' => 'BENGKULU'],
            ['name' => 'Kepahiang', 'pulau' => 'SUMATERA', 'provinsi' => 'BENGKULU'],
            ['name' => 'Lebong', 'pulau' => 'SUMATERA', 'provinsi' => 'BENGKULU'],
            ['name' => 'Mukomuko', 'pulau' => 'SUMATERA', 'provinsi' => 'BENGKULU'],
            ['name' => 'Rejang Lebong', 'pulau' => 'SUMATERA', 'provinsi' => 'BENGKULU'],
            ['name' => 'Seluma', 'pulau' => 'SUMATERA', 'provinsi' => 'BENGKULU'],
            ['name' => 'Kota Bengkulu', 'pulau' => 'SUMATERA', 'provinsi' => 'BENGKULU'],

            // LAMPUNG (15)
            ['name' => 'Lampung Barat', 'pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG'],
            ['name' => 'Lampung Selatan', 'pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG'],
            ['name' => 'Lampung Tengah', 'pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG'],
            ['name' => 'Lampung Timur', 'pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG'],
            ['name' => 'Lampung Utara', 'pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG'],
            ['name' => 'Mesuji', 'pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG'],
            ['name' => 'Pesawaran', 'pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG'],
            ['name' => 'Pesisir Barat', 'pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG'],
            ['name' => 'Pringsewu', 'pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG'],
            ['name' => 'Tanggamus', 'pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG'],
            ['name' => 'Tulang Bawang', 'pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG'],
            ['name' => 'Tulang Bawang Barat', 'pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG'],
            ['name' => 'Way Kanan', 'pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG'],
            ['name' => 'Kota Bandar Lampung', 'pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG'],
            ['name' => 'Kota Metro', 'pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG'],

            // =========================================================================
            // 3. KALIMANTAN (5 Provinces - 56 Kota/Kab)
            // =========================================================================
            // KALIMANTAN BARAT (14)
            ['name' => 'Bengkayang', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN BARAT'],
            ['name' => 'Kapuas Hulu', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN BARAT'],
            ['name' => 'Kayong Utara', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN BARAT'],
            ['name' => 'Ketapang', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN BARAT'],
            ['name' => 'Kubu Raya', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN BARAT'],
            ['name' => 'Landak', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN BARAT'],
            ['name' => 'Melawi', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN BARAT'],
            ['name' => 'Mempawah', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN BARAT'],
            ['name' => 'Sambas', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN BARAT'],
            ['name' => 'Sanggau', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN BARAT'],
            ['name' => 'Sekadau', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN BARAT'],
            ['name' => 'Sintang', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN BARAT'],
            ['name' => 'Kota Pontianak', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN BARAT'],
            ['name' => 'Kota Singkawang', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN BARAT'],

            // KALIMANTAN TENGAH (14)
            ['name' => 'Barito Selatan', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TENGAH'],
            ['name' => 'Barito Timur', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TENGAH'],
            ['name' => 'Barito Utara', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TENGAH'],
            ['name' => 'Gunung Mas', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TENGAH'],
            ['name' => 'Kapuas', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TENGAH'],
            ['name' => 'Katingan', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TENGAH'],
            ['name' => 'Kotawaringin Barat', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TENGAH'],
            ['name' => 'Kotawaringin Timur (Sampit)', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TENGAH'],
            ['name' => 'Lamandau', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TENGAH'],
            ['name' => 'Murung Raya', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TENGAH'],
            ['name' => 'Pulang Pisau', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TENGAH'],
            ['name' => 'Sukamara', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TENGAH'],
            ['name' => 'Seruyan', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TENGAH'],
            ['name' => 'Kota Palangka Raya', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TENGAH'],

            // KALIMANTAN SELATAN (13)
            ['name' => 'Balangan', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN SELATAN'],
            ['name' => 'Banjar (Martapura)', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN SELATAN'],
            ['name' => 'Barito Kuala', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN SELATAN'],
            ['name' => 'Hulu Sungai Selatan (Kandangan)', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN SELATAN'],
            ['name' => 'Hulu Sungai Tengah (Barabai)', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN SELATAN'],
            ['name' => 'Hulu Sungai Utara (Amuntai)', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN SELATAN'],
            ['name' => 'Kotabaru', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN SELATAN'],
            ['name' => 'Tabalong (Tanjung)', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN SELATAN'],
            ['name' => 'Tanah Bumbu (Batulicin)', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN SELATAN'],
            ['name' => 'Tanah Laut (Pelaihari)', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN SELATAN'],
            ['name' => 'Tapin (Rantau)', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN SELATAN'],
            ['name' => 'Kota Banjarbaru', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN SELATAN'],
            ['name' => 'Kota Banjarmasin', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN SELATAN'],

            // KALIMANTAN TIMUR (10)
            ['name' => 'Berau', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TIMUR'],
            ['name' => 'Kutai Barat', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TIMUR'],
            ['name' => 'Kutai Kartanegara (Tenggarong)', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TIMUR'],
            ['name' => 'Kutai Timur (Sangatta)', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TIMUR'],
            ['name' => 'Mahakam Ulu', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TIMUR'],
            ['name' => 'Paser (Tanah Grogot)', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TIMUR'],
            ['name' => 'Penajam Paser Utara (IKN)', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TIMUR'],
            ['name' => 'Kota Balikpapan', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TIMUR'],
            ['name' => 'Kota Bontang', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TIMUR'],
            ['name' => 'Kota Samarinda', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TIMUR'],

            // KALIMANTAN UTARA (5)
            ['name' => 'Bulungan (Tanjung Selor)', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN UTARA'],
            ['name' => 'Malinau', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN UTARA'],
            ['name' => 'Nunukan', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN UTARA'],
            ['name' => 'Tana Tidung', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN UTARA'],
            ['name' => 'Kota Tarakan', 'pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN UTARA'],

            // =========================================================================
            // 4. SULAWESI (6 Provinces - 81 Kota/Kab)
            // =========================================================================
            // SULAWESI UTARA (15)
            ['name' => 'Bolaang Mongondow', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI UTARA'],
            ['name' => 'Bolaang Mongondow Selatan', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI UTARA'],
            ['name' => 'Bolaang Mongondow Timur', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI UTARA'],
            ['name' => 'Bolaang Mongondow Utara', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI UTARA'],
            ['name' => 'Kepulauan Sangihe', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI UTARA'],
            ['name' => 'Kepulauan Siau Tagulandang Biaro (Sitaro)', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI UTARA'],
            ['name' => 'Kepulauan Talaud', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI UTARA'],
            ['name' => 'Minahasa', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI UTARA'],
            ['name' => 'Minahasa Selatan', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI UTARA'],
            ['name' => 'Minahasa Tenggara', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI UTARA'],
            ['name' => 'Minahasa Utara', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI UTARA'],
            ['name' => 'Kota Bitung', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI UTARA'],
            ['name' => 'Kota Kotamobagu', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI UTARA'],
            ['name' => 'Kota Manado', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI UTARA'],
            ['name' => 'Kota Tomohon', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI UTARA'],

            // GORONTALO (6)
            ['name' => 'Boalemo', 'pulau' => 'SULAWESI', 'provinsi' => 'GORONTALO'],
            ['name' => 'Bone Bolango', 'pulau' => 'SULAWESI', 'provinsi' => 'GORONTALO'],
            ['name' => 'Gorontalo', 'pulau' => 'SULAWESI', 'provinsi' => 'GORONTALO'],
            ['name' => 'Gorontalo Utara', 'pulau' => 'SULAWESI', 'provinsi' => 'GORONTALO'],
            ['name' => 'Pohuwato', 'pulau' => 'SULAWESI', 'provinsi' => 'GORONTALO'],
            ['name' => 'Kota Gorontalo', 'pulau' => 'SULAWESI', 'provinsi' => 'GORONTALO'],

            // SULAWESI TENGAH (13)
            ['name' => 'Banggai', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGAH'],
            ['name' => 'Banggai Kepulauan', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGAH'],
            ['name' => 'Banggai Laut', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGAH'],
            ['name' => 'Buol', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGAH'],
            ['name' => 'Donggala', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGAH'],
            ['name' => 'Morowali', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGAH'],
            ['name' => 'Morowali Utara', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGAH'],
            ['name' => 'Parigi Moutong', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGAH'],
            ['name' => 'Poso', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGAH'],
            ['name' => 'Sigi', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGAH'],
            ['name' => 'Tojo Una-Una', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGAH'],
            ['name' => 'Tolitoli', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGAH'],
            ['name' => 'Kota Palu', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGAH'],

            // SULAWESI BARAT (6)
            ['name' => 'Majene', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI BARAT'],
            ['name' => 'Mamasa', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI BARAT'],
            ['name' => 'Mamuju', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI BARAT'],
            ['name' => 'Mamuju Tengah', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI BARAT'],
            ['name' => 'Pasangkayu', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI BARAT'],
            ['name' => 'Polewali Mandar', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI BARAT'],

            // SULAWESI SELATAN (24)
            ['name' => 'Bantaeng', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['name' => 'Barru', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['name' => 'Bone', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['name' => 'Bulukumba', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['name' => 'Enrekang', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['name' => 'Gowa', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['name' => 'Jeneponto', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['name' => 'Kepulauan Selayar', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['name' => 'Luwu', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['name' => 'Luwu Timur (Malili)', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['name' => 'Luwu Utara', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['name' => 'Maros', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['name' => 'Pangkajene dan Kepulauan (Pangkep)', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['name' => 'Pinrang', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['name' => 'Sidenreng Rappang (Sidrap)', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['name' => 'Sinjai', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['name' => 'Soppeng', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['name' => 'Takalar', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['name' => 'Tana Toraja', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['name' => 'Toraja Utara', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['name' => 'Wajo', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['name' => 'Kota Makassar', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['name' => 'Kota Palopo', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],
            ['name' => 'Kota Parepare', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN'],

            // SULAWESI TENGGARA (17)
            ['name' => 'Bombana', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGGARA'],
            ['name' => 'Buton', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGGARA'],
            ['name' => 'Buton Selatan', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGGARA'],
            ['name' => 'Buton Tengah', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGGARA'],
            ['name' => 'Buton Utara', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGGARA'],
            ['name' => 'Kolaka', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGGARA'],
            ['name' => 'Kolaka Timur', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGGARA'],
            ['name' => 'Kolaka Utara', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGGARA'],
            ['name' => 'Konawe', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGGARA'],
            ['name' => 'Konawe Kepulauan', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGGARA'],
            ['name' => 'Konawe Selatan', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGGARA'],
            ['name' => 'Konawe Utara', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGGARA'],
            ['name' => 'Muna', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGGARA'],
            ['name' => 'Muna Barat', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGGARA'],
            ['name' => 'Wakatobi', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGGARA'],
            ['name' => 'Kota Baubau', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGGARA'],
            ['name' => 'Kota Kendari', 'pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGGARA'],

            // =========================================================================
            // 5. BALI & NUSA TENGGARA (3 Provinces - 41 Kota/Kab)
            // =========================================================================
            // BALI (9)
            ['name' => 'Badung', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'BALI'],
            ['name' => 'Bangli', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'BALI'],
            ['name' => 'Buleleng (Singaraja)', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'BALI'],
            ['name' => 'Gianyar', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'BALI'],
            ['name' => 'Jembrana (Negara)', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'BALI'],
            ['name' => 'Karangasem (Amlapura)', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'BALI'],
            ['name' => 'Klungkung', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'BALI'],
            ['name' => 'Tabanan', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'BALI'],
            ['name' => 'Kota Denpasar', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'BALI'],

            // NUSA TENGGARA BARAT (10)
            ['name' => 'Bima', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA BARAT'],
            ['name' => 'Dompu', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA BARAT'],
            ['name' => 'Lombok Barat', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA BARAT'],
            ['name' => 'Lombok Tengah (Praya)', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA BARAT'],
            ['name' => 'Lombok Timur (Selong)', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA BARAT'],
            ['name' => 'Lombok Utara', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA BARAT'],
            ['name' => 'Sumbawa', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA BARAT'],
            ['name' => 'Sumbawa Barat', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA BARAT'],
            ['name' => 'Kota Bima', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA BARAT'],
            ['name' => 'Kota Mataram', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA BARAT'],

            // NUSA TENGGARA TIMUR (22)
            ['name' => 'Alor', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['name' => 'Belu (Atambua)', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['name' => 'Ende', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['name' => 'Flores Timur (Larantuka)', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['name' => 'Kupang', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['name' => 'Lembata', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['name' => 'Malaka', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['name' => 'Manggarai (Ruteng)', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['name' => 'Manggarai Barat (Labuan Bajo)', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['name' => 'Manggarai Timur', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['name' => 'Nagekeo', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['name' => 'Ngada (Bajawa)', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['name' => 'Rote Ndao', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['name' => 'Sabu Raijua', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['name' => 'Sikka (Maumere)', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['name' => 'Sumba Barat (Waikabubak)', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['name' => 'Sumba Barat Daya (Tambolaka)', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['name' => 'Sumba Tengah', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['name' => 'Sumba Timur (Waingapu)', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['name' => 'Timor Tengah Selatan (Soe)', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['name' => 'Timor Tengah Utara (Kefamenanu)', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],
            ['name' => 'Kota Kupang', 'pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR'],

            // =========================================================================
            // 6. PAPUA & MALUKU (8 Provinces - 63 Kota/Kab)
            // =========================================================================
            // MALUKU (11)
            ['name' => 'Buru (Namlea)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU'],
            ['name' => 'Buru Selatan', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU'],
            ['name' => 'Kepulauan Aru (Dobo)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU'],
            ['name' => 'Kepulauan Tanimbar (Saumlaki)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU'],
            ['name' => 'Maluku Barat Daya', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU'],
            ['name' => 'Maluku Tengah (Masohi)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU'],
            ['name' => 'Maluku Tenggara (Langgur)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU'],
            ['name' => 'Seram Bagian Barat', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU'],
            ['name' => 'Seram Bagian Timur', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU'],
            ['name' => 'Kota Ambon', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU'],
            ['name' => 'Kota Tual', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU'],

            // MALUKU UTARA (10)
            ['name' => 'Halmahera Barat', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU UTARA'],
            ['name' => 'Halmahera Tengah (Wedabay)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU UTARA'],
            ['name' => 'Halmahera Timur', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU UTARA'],
            ['name' => 'Halmahera Selatan (Labuha)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU UTARA'],
            ['name' => 'Halmahera Utara (Tobelo)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU UTARA'],
            ['name' => 'Kepulauan Sula (Sanana)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU UTARA'],
            ['name' => 'Pulau Morotai', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU UTARA'],
            ['name' => 'Pulau Taliabu', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU UTARA'],
            ['name' => 'Kota Ternate', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU UTARA'],
            ['name' => 'Kota Tidore Kepulauan', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU UTARA'],

            // PAPUA (9)
            ['name' => 'Biak Numfor', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA'],
            ['name' => 'Jayapura (Sentani)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA'],
            ['name' => 'Keerom', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA'],
            ['name' => 'Kepulauan Yapen (Serui)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA'],
            ['name' => 'Mamberamo Raya', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA'],
            ['name' => 'Sarmi', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA'],
            ['name' => 'Supiori', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA'],
            ['name' => 'Waropen', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA'],
            ['name' => 'Kota Jayapura', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA'],

            // PAPUA BARAT (7)
            ['name' => 'Fakfak', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA BARAT'],
            ['name' => 'Kaimana', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA BARAT'],
            ['name' => 'Manokwari', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA BARAT'],
            ['name' => 'Manokwari Selatan', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA BARAT'],
            ['name' => 'Pegunungan Arfak', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA BARAT'],
            ['name' => 'Teluk Bintuni', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA BARAT'],
            ['name' => 'Teluk Wondama', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA BARAT'],

            // PAPUA BARAT DAYA (6)
            ['name' => 'Maybrat', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA BARAT DAYA'],
            ['name' => 'Raja Ampat', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA BARAT DAYA'],
            ['name' => 'Sorong', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA BARAT DAYA'],
            ['name' => 'Sorong Selatan (Teminabuan)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA BARAT DAYA'],
            ['name' => 'Tambrauw', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA BARAT DAYA'],
            ['name' => 'Kota Sorong', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA BARAT DAYA'],

            // PAPUA TENGAH (8)
            ['name' => 'Deiyai', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA TENGAH'],
            ['name' => 'Dogiyai', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA TENGAH'],
            ['name' => 'Intan Jaya (Sugapa)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA TENGAH'],
            ['name' => 'Mimika (Timika)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA TENGAH'],
            ['name' => 'Nabire', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA TENGAH'],
            ['name' => 'Paniai (Enarotali)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA TENGAH'],
            ['name' => 'Puncak (Ilaga)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA TENGAH'],
            ['name' => 'Puncak Jaya (Kotamulia)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA TENGAH'],

            // PAPUA PEGUNUNGAN (8)
            ['name' => 'Jayawijaya (Wamena)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA PEGUNUNGAN'],
            ['name' => 'Lanny Jaya (Tiom)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA PEGUNUNGAN'],
            ['name' => 'Mamberamo Tengah (Kobakma)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA PEGUNUNGAN'],
            ['name' => 'Nduga (Kenyam)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA PEGUNUNGAN'],
            ['name' => 'Pegunungan Bintang (Oksibil)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA PEGUNUNGAN'],
            ['name' => 'Tolikara (Karubaga)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA PEGUNUNGAN'],
            ['name' => 'Yahukimo (Dekai)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA PEGUNUNGAN'],
            ['name' => 'Yalimo (Elelim)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA PEGUNUNGAN'],

            // PAPUA SELATAN (4)
            ['name' => 'Asmat (Agats)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA SELATAN'],
            ['name' => 'Boven Digoel (Tanah Merah)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA SELATAN'],
            ['name' => 'Mappi (Kepi)', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA SELATAN'],
            ['name' => 'Merauke', 'pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA SELATAN'],
        ];

        foreach ($regions as $r) {
            $existing = DB::table('costing_lokasi_pemakaians')->where('name', $r['name'])->first();
            if ($existing) {
                DB::table('costing_lokasi_pemakaians')->where('id', $existing->id)->update([
                    'pulau'      => $r['pulau'],
                    'provinsi'   => $r['provinsi'],
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('costing_lokasi_pemakaians')->insert([
                    'name'       => $r['name'],
                    'pulau'      => $r['pulau'],
                    'provinsi'   => $r['provinsi'],
                    'is_active'  => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
};
