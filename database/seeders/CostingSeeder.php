<?php

namespace Database\Seeders;

use App\Models\CostingAccessoryTemplate;
use App\Models\CostingBbnMaster;
use App\Services\OdooService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class CostingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed accessory templates
        $templates = [
            ['name' => 'Wall Charger', 'default_amount' => 11500000, 'sort_order' => 1],
            ['name' => 'Biaya lepas Wall Charger', 'default_amount' => 2000000, 'sort_order' => 2],
            ['name' => "KF Antigores 80%\nBaris Penumpang & Bagasi", 'default_amount' => 600000, 'sort_order' => 3],
            ['name' => 'Talang Air', 'default_amount' => 1000000, 'sort_order' => 4],
        ];

        foreach ($templates as $t) {
            CostingAccessoryTemplate::updateOrCreate(
                ['name' => $t['name']],
                ['default_amount' => $t['default_amount'], 'sort_order' => $t['sort_order']]
            );
        }

        // 2. Fetch and seed BBN from Odoo (with fallback defaults)
        try {
            $odoo = app(OdooService::class);
            $bbnRecords = $odoo->call('vehicle.bbn', 'search_read', [[]], [
                'fields' => ['id', 'kode', 'alamat', 'display_name'],
                'order' => 'kode asc'
            ]);

            if (!empty($bbnRecords)) {
                foreach ($bbnRecords as $rec) {
                    CostingBbnMaster::updateOrCreate(
                        ['kode' => $rec['kode'] ?: $rec['display_name']],
                        [
                            'odoo_bbn_id'  => $rec['id'],
                            'alamat'       => $rec['alamat'] ?: null,
                            'display_name' => $rec['display_name'] ?: $rec['kode'],
                        ]
                    );
                }
            }
        } catch (\Throwable $e) {
            Log::warning('CostingSeeder: Could not sync BBN from Odoo: ' . $e->getMessage());

            // Fallback list of common BBNs
            $defaults = [
                ['kode' => 'JKT', 'display_name' => 'JKT - Jakarta'],
                ['kode' => 'BDG', 'display_name' => 'BDG - Bandung'],
                ['kode' => 'DPS', 'display_name' => 'DPS - Denpasar'],
                ['kode' => 'BPN', 'display_name' => 'BPN - Balikpapan'],
                ['kode' => 'CLG', 'display_name' => 'CLG - Cilegon'],
                ['kode' => 'CRB', 'display_name' => 'CRB - Cirebon'],
                ['kode' => 'BTM', 'display_name' => 'BTM - Batam'],
                ['kode' => 'BKU', 'display_name' => 'BKU - Bengkulu'],
                ['kode' => 'AMB', 'display_name' => 'AMB - Ambon'],
                ['kode' => 'ACH', 'display_name' => 'ACH - Aceh'],
            ];

            foreach ($defaults as $d) {
                CostingBbnMaster::firstOrCreate(
                    ['kode' => $d['kode']],
                    ['display_name' => $d['display_name']]
                );
            }
        }
    }
}
