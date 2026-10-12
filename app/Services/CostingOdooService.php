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
}
