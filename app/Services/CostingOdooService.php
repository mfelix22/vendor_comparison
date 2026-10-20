<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CostingOdooService
{
    public function __construct(
        private OdooService $odoo
    ) {}

    /**
     * Get list of car products from Odoo (where is_car = true).
     * Cached for 10 minutes.
     */
    public function getCarProducts(): array
    {
        return Cache::remember('costing_car_products', 600, function () {
            try {
                $products = $this->odoo->call('product.product', 'search_read', [
                    [['is_car', '=', true]]
                ], [
                    'fields' => ['id', 'name', 'display_name', 'vehicle_brand_id', 'vehicle_model_id'],
                    'order'  => 'display_name asc',
                    'limit'  => 1000,
                ]);

                return $products ?? [];
            } catch (\Throwable) {
                return [];
            }
        });
    }

    /**
     * Flush only the costing car products cache.
     * Isolated strictly to the Costing module without affecting RFQs or vendors.
     */
    public function flushCostingCache(): void
    {
        Cache::forget('costing_car_products');
    }

    /**
     * Get distinct list of vehicle brands from car products.
     */
    public function getCarBrands(): array
    {
        $products = $this->getCarProducts();
        $brands = [];

        foreach ($products as $p) {
            if (!empty($p['vehicle_brand_id']) && is_array($p['vehicle_brand_id'])) {
                $brandName = explode(' - ', $p['vehicle_brand_id'][1])[0] ?? $p['vehicle_brand_id'][1];
                $brandName = trim($brandName);
                if ($brandName && !in_array($brandName, $brands, true)) {
                    $brands[] = $brandName;
                }
            }
        }

        sort($brands);
        return $brands;
    }



    /**
     * BBN master is 100% locally unified with all Indonesian cities, Pulau, and Provinsi.
     * Preserves local master without overwriting from Odoo.
     */
    public function syncBbnMaster(): array
    {
        return \App\Models\CostingBbnMaster::where('is_active', true)->orderBy('name')->get()->toArray();
    }

    /**
     * Complete, safe sync of Costing Masters (Brands, Vehicle Types) from Odoo.
     * Pure service method: ZERO seeders required, safe for fresh Portainer deployments.
     * Preserves all local custom records and unified BBN master.
     */
    public function syncCostingMasters(): array
    {
        $this->flushCostingCache();

        // 1. Unified BBN list is preserved from local database
        $bbnList = $this->syncBbnMaster();

        // 2. Sync Brands & Vehicle Types from Odoo Products
        $products = $this->getCarProducts();
        $syncedBrands = 0;
        $syncedTypes = 0;

        foreach ($products as $p) {
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

            $brand = \App\Models\CostingBrand::firstOrCreate(
                ['name' => $brandName],
                [
                    'odoo_brand_id' => $odooBrandId,
                    'is_active'     => true,
                ]
            );
            $syncedBrands++;

            $typeName = trim($p['name'] ?? $p['display_name'] ?? '');
            if (!$typeName) {
                continue;
            }

            \App\Models\CostingVehicleType::firstOrCreate(
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
            $syncedTypes++;
        }

        return [
            'brands'   => \App\Models\CostingBrand::where('is_active', true)->orderBy('name')->pluck('name')->toArray(),
            'products' => \App\Models\CostingVehicleType::where('is_active', true)->orderBy('name')->get()->toArray(),
            'bbn'      => $bbnList,
            'counts'   => [
                'brands'   => \App\Models\CostingBrand::count(),
                'products' => \App\Models\CostingVehicleType::count(),
                'bbn'      => \App\Models\CostingBbnMaster::count(),
            ],
        ];
    }

    /**
     * Search Odoo partners for customer auto-suggest.
     */
    public function searchCustomers(string $query): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        try {
            $partners = $this->odoo->call('res.partner', 'search_read', [
                [['|', ['name', 'ilike', $query], ['display_name', 'ilike', $query]]]
            ], [
                'fields' => ['id', 'name', 'display_name'],
                'limit'  => 20,
            ]);

            return $partners ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Get list of SMD Sales Managers from Odoo (excluding Ahmad Sofwan Qudsy and Wahyu Iskandar).
     */
    public function getSmdList(): array
    {
        return Cache::remember('costing_smd_list', 3600, function () {
            $excluded = ['Ahmad Sofwan Qudsy', 'Wahyu Iskandar'];
            try {
                $teams = $this->odoo->call('crm.team', 'search_read', [
                    [['active', '=', true]]
                ], [
                    'fields' => ['id', 'user_id'],
                    'limit'  => 50,
                ]);

                $list = [];
                foreach ($teams as $t) {
                    if (!empty($t['user_id']) && is_array($t['user_id'])) {
                        $name = trim($t['user_id'][1]);
                        if ($name && !in_array($name, $excluded, true) && !in_array($name, $list, true)) {
                            $list[] = $name;
                        }
                    }
                }

                sort($list);
                if (!empty($list)) {
                    return $list;
                }
            } catch (\Throwable) {
                // Fallback to verified active Sales Managers
            }

            return [
                'Adhitya',
                'Bambang Sumantri',
                'Fuji',
                'Kiki',
                'Suryanti',
                'Wahyu Waraka',
            ];
        });
    }
}
