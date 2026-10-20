<?php

namespace Database\Seeders;

use App\Models\CostingBbnMaster;
use App\Models\CostingBrand;
use App\Models\CostingVehicleType;
use App\Services\CostingOdooService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class CostingMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Map BBN codes to full names (no abbreviation)
        $bbnMapping = [
            'ACH' => 'Aceh',
            'AMB' => 'Ambon',
            'BDG' => 'Bandung',
            'BGK' => 'Bangka Belitung',
            'BKU' => 'Bengkulu',
            'BPN' => 'Balikpapan',
            'BTM' => 'Batam',
            'CLG' => 'Cilegon',
            'CRB' => 'Cirebon',
            'DPS' => 'Denpasar / Bali',
            'JKT' => 'Jakarta',
            'JYA' => 'Jayapura',
            'KRW' => 'Karawang',
            'LMP' => 'Lampung',
            'MES' => 'Medan',
            'MKS' => 'Makassar',
            'MLL' => 'Malili',
            'MND' => 'Manado',
            'MTM' => 'Mataram / Lombok',
            'PDG' => 'Padang',
            'PKU' => 'Pekanbaru',
            'PLB' => 'Palembang',
            'PTA' => 'Pontianak',
            'RMB' => 'Rembang',
            'SMG' => 'Semarang',
            'SRG' => 'Sorong',
            'SUB' => 'Surabaya',
            'TRK' => 'Tarakan',
            'YOG' => 'Yogyakarta',
        ];

        foreach (CostingBbnMaster::all() as $bbn) {
            $code = strtoupper(trim($bbn->kode));
            $fullName = $bbnMapping[$code] ?? $bbn->display_name ?: $bbn->kode;
            $bbn->update([
                'name'         => $fullName,
                'display_name' => $fullName,
                'is_active'    => true,
            ]);
        }

        // 2. Seed Brands and Vehicle Types from Odoo
        try {
            $odooService = app(CostingOdooService::class);
            $products = $odooService->getCarProducts();

            foreach ($products as $p) {
                // Determine brand
                $brandName = '';
                $odooBrandId = null;
                if (!empty($p['vehicle_brand_id']) && is_array($p['vehicle_brand_id'])) {
                    $odooBrandId = (int) $p['vehicle_brand_id'][0];
                    $rawBrand = explode(' - ', $p['vehicle_brand_id'][1])[0] ?? $p['vehicle_brand_id'][1];
                    $brandName = strtoupper(trim($rawBrand));
                }

                if (!$brandName) {
                    continue;
                }

                $brand = CostingBrand::firstOrCreate(
                    ['name' => $brandName],
                    [
                        'odoo_brand_id' => $odooBrandId,
                        'is_active'     => true,
                    ]
                );

                // Vehicle Type Name (clean model / name)
                $typeName = trim($p['name'] ?? $p['display_name'] ?? '');
                if (!$typeName) {
                    continue;
                }

                CostingVehicleType::firstOrCreate(
                    [
                        'costing_brand_id' => $brand->id,
                        'name'             => $typeName,
                    ],
                    [
                        'brand_name'      => $brand->name,
                        'odoo_product_id' => !empty($p['id']) ? (int) $p['id'] : null,
                        'is_active'       => true,
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::warning('CostingMasterSeeder: Odoo vehicle sync notice: ' . $e->getMessage());
        }
    }
}
