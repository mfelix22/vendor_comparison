<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create costing_regions master table
        Schema::create('costing_regions', function (Blueprint $table) {
            $table->id();
            $table->string('pulau', 100)->index();
            $table->string('provinsi', 100)->index();
            $table->string('area', 150)->index();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 2. Add pulau and provinsi columns to costing_vehicles
        Schema::table('costing_vehicles', function (Blueprint $table) {
            $table->string('pulau', 100)->nullable()->after('sort_order');
            $table->string('provinsi', 100)->nullable()->after('pulau');
        });

        // 3. Populate default Indonesian geographic baseline (Zero seeders, safe for fresh Portainer deployment)
        $this->seedInitialBaseline();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('costing_vehicles', function (Blueprint $table) {
            $table->dropColumn(['pulau', 'provinsi']);
        });

        Schema::dropIfExists('costing_regions');
    }

    private function seedInitialBaseline(): void
    {
        $now = now();
        $regions = [
            // ==================== JAWA ====================
            ['pulau' => 'JAWA', 'provinsi' => 'DKI JAKARTA', 'area' => 'Jakarta'],
            ['pulau' => 'JAWA', 'provinsi' => 'DKI JAKARTA', 'area' => 'Jabodetabek'],
            ['pulau' => 'JAWA', 'provinsi' => 'DKI JAKARTA', 'area' => 'Jakarta Pusat'],
            ['pulau' => 'JAWA', 'provinsi' => 'DKI JAKARTA', 'area' => 'Jakarta Selatan'],
            ['pulau' => 'JAWA', 'provinsi' => 'DKI JAKARTA', 'area' => 'Jakarta Barat'],
            ['pulau' => 'JAWA', 'provinsi' => 'DKI JAKARTA', 'area' => 'Jakarta Timur'],
            ['pulau' => 'JAWA', 'provinsi' => 'DKI JAKARTA', 'area' => 'Jakarta Utara'],
            ['pulau' => 'JAWA', 'provinsi' => 'DKI JAKARTA', 'area' => 'Kepulauan Seribu'],

            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Bandung'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Kabupaten Bandung'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Bandung Barat'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Cimahi'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Bogor'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Kabupaten Bogor'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Depok'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Bekasi'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Kabupaten Bekasi'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Sukabumi'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Kabupaten Sukabumi'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Karawang'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Purwakarta'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Subang'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Cirebon'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Kabupaten Cirebon'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Indramayu'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Majalengka'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Kuningan'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Sumedang'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Garut'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Tasikmalaya'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Ciamis'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Banjar'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA BARAT', 'area' => 'Pangandaran'],

            ['pulau' => 'JAWA', 'provinsi' => 'BANTEN', 'area' => 'Tangerang'],
            ['pulau' => 'JAWA', 'provinsi' => 'BANTEN', 'area' => 'Tangerang Selatan'],
            ['pulau' => 'JAWA', 'provinsi' => 'BANTEN', 'area' => 'Kabupaten Tangerang'],
            ['pulau' => 'JAWA', 'provinsi' => 'BANTEN', 'area' => 'Serang'],
            ['pulau' => 'JAWA', 'provinsi' => 'BANTEN', 'area' => 'Cilegon'],
            ['pulau' => 'JAWA', 'provinsi' => 'BANTEN', 'area' => 'Lebak'],
            ['pulau' => 'JAWA', 'provinsi' => 'BANTEN', 'area' => 'Pandeglang'],

            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Semarang'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Kaligawe'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Ungaran'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Salatiga'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Pekalongan'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Batang'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Tegal'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Brebes'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Pemalang'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Kendal'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Demak'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Kudus'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Pati'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Jepara'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Rembang'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Blora'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Grobogan'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Solo / Surakarta'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Sukoharjo'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Boyolali'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Klaten'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Karanganyar'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Wonogiri'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Sragen'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Magelang'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Purworejo'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Temanggung'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Wonosobo'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Kebumen'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Banyumas / Purwokerto'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Cilacap'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Purbalingga'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TENGAH', 'area' => 'Banjarnegara'],

            ['pulau' => 'JAWA', 'provinsi' => 'DAERAH ISTIMEWA YOGYAKARTA', 'area' => 'Yogyakarta'],
            ['pulau' => 'JAWA', 'provinsi' => 'DAERAH ISTIMEWA YOGYAKARTA', 'area' => 'Sleman'],
            ['pulau' => 'JAWA', 'provinsi' => 'DAERAH ISTIMEWA YOGYAKARTA', 'area' => 'Bantul'],
            ['pulau' => 'JAWA', 'provinsi' => 'DAERAH ISTIMEWA YOGYAKARTA', 'area' => 'Kulon Progo'],
            ['pulau' => 'JAWA', 'provinsi' => 'DAERAH ISTIMEWA YOGYAKARTA', 'area' => 'Gunungkidul'],

            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR', 'area' => 'Surabaya'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR', 'area' => 'Sidoarjo'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR', 'area' => 'Gresik'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR', 'area' => 'Mojokerto'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR', 'area' => 'Pasuruan'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR', 'area' => 'Malang'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR', 'area' => 'Batu'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR', 'area' => 'Probolinggo'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR', 'area' => 'Jember'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR', 'area' => 'Banyuwangi'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR', 'area' => 'Kediri'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR', 'area' => 'Blitar'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR', 'area' => 'Madiun'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR', 'area' => 'Tuban'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR', 'area' => 'Lamongan'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR', 'area' => 'Bojonegoro'],
            ['pulau' => 'JAWA', 'provinsi' => 'JAWA TIMUR', 'area' => 'Jombang'],

            // ==================== SUMATERA ====================
            ['pulau' => 'SUMATERA', 'provinsi' => 'ACEH', 'area' => 'Banda Aceh'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'ACEH', 'area' => 'Sabang'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'ACEH', 'area' => 'Lhokseumawe'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'ACEH', 'area' => 'Langsa'],

            ['pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA', 'area' => 'Medan'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA', 'area' => 'Binjai'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA', 'area' => 'Pematangsiantar'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA', 'area' => 'Deli Serdang'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA', 'area' => 'Kepulauan Nias'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA', 'area' => 'Gunungsitoli'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA', 'area' => 'Asahan'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA UTARA', 'area' => 'Karo'],

            ['pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT', 'area' => 'Padang'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT', 'area' => 'Bukittinggi'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT', 'area' => 'Padang Panjang'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT', 'area' => 'Payakumbuh'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA BARAT', 'area' => 'Pariaman'],

            ['pulau' => 'SUMATERA', 'provinsi' => 'RIAU', 'area' => 'Pekanbaru'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'RIAU', 'area' => 'Dumai'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'RIAU', 'area' => 'Kampar'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'RIAU', 'area' => 'Siak'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'RIAU', 'area' => 'Bengkalis'],

            ['pulau' => 'SUMATERA', 'provinsi' => 'KEPULAUAN RIAU', 'area' => 'Batam'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'KEPULAUAN RIAU', 'area' => 'Tanjungpinang'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'KEPULAUAN RIAU', 'area' => 'Bintan'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'KEPULAUAN RIAU', 'area' => 'Karimun'],

            ['pulau' => 'SUMATERA', 'provinsi' => 'JAMBI', 'area' => 'Jambi'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'JAMBI', 'area' => 'Muaro Jambi'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'JAMBI', 'area' => 'Bungo'],

            ['pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN', 'area' => 'Palembang'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN', 'area' => 'Prabumulih'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN', 'area' => 'Lubuklinggau'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'SUMATERA SELATAN', 'area' => 'Banyuasin'],

            ['pulau' => 'SUMATERA', 'provinsi' => 'BANGKA BELITUNG', 'area' => 'Pangkalpinang'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'BANGKA BELITUNG', 'area' => 'Bangka'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'BANGKA BELITUNG', 'area' => 'Belitung'],

            ['pulau' => 'SUMATERA', 'provinsi' => 'BENGKULU', 'area' => 'Bengkulu'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'BENGKULU', 'area' => 'Rejang Lebong'],

            ['pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG', 'area' => 'Bandar Lampung'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG', 'area' => 'Metro'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG', 'area' => 'Lampung Selatan'],
            ['pulau' => 'SUMATERA', 'provinsi' => 'LAMPUNG', 'area' => 'Lampung Tengah'],

            // ==================== KALIMANTAN ====================
            ['pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN BARAT', 'area' => 'Pontianak'],
            ['pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN BARAT', 'area' => 'Singkawang'],
            ['pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN BARAT', 'area' => 'Kubu Raya'],
            ['pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN BARAT', 'area' => 'Ketapang'],

            ['pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TENGAH', 'area' => 'Palangka Raya'],
            ['pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TENGAH', 'area' => 'Sampit'],
            ['pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TENGAH', 'area' => 'Pangkalan Bun'],

            ['pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN SELATAN', 'area' => 'Banjarmasin'],
            ['pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN SELATAN', 'area' => 'Banjarbaru'],
            ['pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN SELATAN', 'area' => 'Martapura'],
            ['pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN SELATAN', 'area' => 'Tabalong'],

            ['pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TIMUR', 'area' => 'Balikpapan'],
            ['pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TIMUR', 'area' => 'Samarinda'],
            ['pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TIMUR', 'area' => 'Bontang'],
            ['pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TIMUR', 'area' => 'Kutai Kartanegara'],
            ['pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TIMUR', 'area' => 'Penajam Paser Utara / IKN'],
            ['pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN TIMUR', 'area' => 'Berau'],

            ['pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN UTARA', 'area' => 'Tarakan'],
            ['pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN UTARA', 'area' => 'Tanjung Selor'],
            ['pulau' => 'KALIMANTAN', 'provinsi' => 'KALIMANTAN UTARA', 'area' => 'Nunukan'],

            // ==================== SULAWESI ====================
            ['pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN', 'area' => 'Makassar'],
            ['pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN', 'area' => 'Gowa'],
            ['pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN', 'area' => 'Maros'],
            ['pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN', 'area' => 'Parepare'],
            ['pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN', 'area' => 'Palopo'],
            ['pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN', 'area' => 'Malili'],
            ['pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN', 'area' => 'Luwu Timur'],
            ['pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI SELATAN', 'area' => 'Bone'],

            ['pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI UTARA', 'area' => 'Manado'],
            ['pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI UTARA', 'area' => 'Bitung'],
            ['pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI UTARA', 'area' => 'Tomohon'],
            ['pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI UTARA', 'area' => 'Kotamobagu'],

            ['pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGAH', 'area' => 'Palu'],
            ['pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGAH', 'area' => 'Morowali'],
            ['pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGAH', 'area' => 'Luwuk / Banggai'],

            ['pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGGARA', 'area' => 'Kendari'],
            ['pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGGARA', 'area' => 'Baubau'],
            ['pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI TENGGARA', 'area' => 'Kolaka'],

            ['pulau' => 'SULAWESI', 'provinsi' => 'GORONTALO', 'area' => 'Gorontalo'],
            ['pulau' => 'SULAWESI', 'provinsi' => 'SULAWESI BARAT', 'area' => 'Mamuju'],

            // ==================== BALI & NUSA TENGGARA ====================
            ['pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'BALI', 'area' => 'Denpasar'],
            ['pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'BALI', 'area' => 'Badung'],
            ['pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'BALI', 'area' => 'Gianyar'],
            ['pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'BALI', 'area' => 'Tabanan'],
            ['pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'BALI', 'area' => 'Buleleng'],

            ['pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA BARAT', 'area' => 'Mataram'],
            ['pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA BARAT', 'area' => 'Lombok Barat'],
            ['pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA BARAT', 'area' => 'Sumbawa'],
            ['pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA BARAT', 'area' => 'Bima'],

            ['pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR', 'area' => 'Kupang'],
            ['pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR', 'area' => 'Labuan Bajo'],
            ['pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR', 'area' => 'Maumere'],
            ['pulau' => 'BALI & NUSA TENGGARA', 'provinsi' => 'NUSA TENGGARA TIMUR', 'area' => 'Ende'],

            // ==================== PAPUA & MALUKU ====================
            ['pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU', 'area' => 'Ambon'],
            ['pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU', 'area' => 'Tual'],
            ['pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU UTARA', 'area' => 'Ternate'],
            ['pulau' => 'PAPUA & MALUKU', 'provinsi' => 'MALUKU UTARA', 'area' => 'Tidore'],

            ['pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA', 'area' => 'Jayapura'],
            ['pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA', 'area' => 'Biak'],
            ['pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA BARAT', 'area' => 'Manokwari'],
            ['pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA BARAT DAYA', 'area' => 'Sorong'],
            ['pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA BARAT DAYA', 'area' => 'Raja Ampat'],
            ['pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA TENGAH', 'area' => 'Timika'],
            ['pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA TENGAH', 'area' => 'Nabire'],
            ['pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA SELATAN', 'area' => 'Merauke'],
            ['pulau' => 'PAPUA & MALUKU', 'provinsi' => 'PAPUA PEGUNUNGAN', 'area' => 'Wamena'],
        ];

        $dataToInsert = [];
        foreach ($regions as $r) {
            $dataToInsert[] = [
                'pulau'      => $r['pulau'],
                'provinsi'   => $r['provinsi'],
                'area'       => $r['area'],
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Insert in chunks for performance
        foreach (array_chunk($dataToInsert, 50) as $chunk) {
            DB::table('costing_regions')->insert($chunk);
        }
    }
};
