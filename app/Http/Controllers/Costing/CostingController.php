<?php

namespace App\Http\Controllers\Costing;

use App\Http\Controllers\Controller;
use App\Models\Costing;
use App\Models\CostingAccessory;
use App\Models\CostingAccessoryTemplate;
use App\Models\CostingBbnMaster;
use App\Models\CostingVehicle;
use App\Services\CostingOdooService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CostingController extends Controller
{
    public function __construct(
        private CostingOdooService $odooService,
        private \App\Services\OdooService $odoo
    ) {}

    /**
     * Display a listing of costings.
     */
    public function index(Request $request)
    {
        $query = Costing::with(['creator', 'vehicles'])
            ->orderByDesc('created_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('costing_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $costings = $query->paginate(15)->withQueryString();

        return view('costing.index', compact('costings'));
    }

    /**
     * Show the form for creating a new costing.
     */
    public function create()
    {
        $bbnList   = CostingBbnMaster::orderBy('kode')->get();
        $templates = CostingAccessoryTemplate::orderBy('sort_order')->get();
        $brands    = $this->odooService->getCarBrands();
        $products  = $this->odooService->getCarProducts();
        $vendors   = $this->odoo->getVendors();

        return view('costing.create', compact('bbnList', 'templates', 'brands', 'products', 'vendors'));
    }

    /**
     * Store a newly created costing in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name'             => ['required', 'string', 'max:255'],
            'odoo_partner_id'           => ['nullable', 'integer'],
            'notes'                     => ['nullable', 'string'],
            'vehicles'                  => ['required', 'array', 'min:1'],
            'vehicles.*.brand'          => ['nullable', 'string', 'max:100'],
            'vehicles.*.vehicle_type'   => ['nullable', 'string', 'max:255'],
            'vehicles.*.odoo_product_id'=> ['nullable', 'integer'],
            'vehicles.*.bbn'            => ['nullable', 'string', 'max:100'],
            'vehicles.*.pemakaian'      => ['nullable', 'string', 'max:255'],
            'vehicles.*.harga_otr'      => ['nullable', 'numeric', 'min:0'],
            'vehicles.*.diskon'         => ['nullable', 'numeric', 'min:0'],
            'vehicles.*.estimasi_kenaikan'=> ['nullable', 'numeric', 'min:0'],
            'accessories'               => ['nullable', 'array'],
            'accessories.*.name'        => ['nullable', 'string', 'max:255'],
            'accessories.*.amounts'     => ['nullable', 'array'],
        ]);

        return DB::transaction(function () use ($validated, $request) {
            // Generate unique Costing Number
            $prefix = 'CST/' . date('Y/m') . '/';
            $last = Costing::where('costing_number', 'like', $prefix . '%')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $seq = 1;
            if ($last && preg_match('/(\d+)$/', $last->costing_number, $m)) {
                $seq = (int) $m[1] + 1;
            }
            $costingNumber = $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

            $costing = Costing::create([
                'costing_number'  => $costingNumber,
                'customer_name'   => $validated['customer_name'],
                'odoo_partner_id' => $validated['odoo_partner_id'] ?? null,
                'notes'           => $validated['notes'] ?? null,
                'status'          => 'saved',
                'created_by'      => Auth::id(),
            ]);

            // Save vehicles
            $createdVehicles = [];
            $sortOrder = 0;
            foreach ($validated['vehicles'] as $vIndex => $vData) {
                $otr       = (float) ($vData['harga_otr'] ?? 0);
                $diskon    = (float) ($vData['diskon'] ?? 0);
                $nett      = max(0, $otr - $diskon);
                $kenaikan  = (float) ($vData['estimasi_kenaikan'] ?? 0);
                $afterInc  = $nett + $kenaikan;

                $vehicle = CostingVehicle::create([
                    'costing_id'             => $costing->id,
                    'sort_order'             => $sortOrder++,
                    'brand'                  => $vData['brand'] ?? '',
                    'vehicle_type'           => $vData['vehicle_type'] ?? '',
                    'odoo_product_id'        => !empty($vData['odoo_product_id']) ? (int) $vData['odoo_product_id'] : null,
                    'bbn'                    => $vData['bbn'] ?? '',
                    'pemakaian'              => $vData['pemakaian'] ?? '',
                    'harga_otr'              => $otr,
                    'diskon'                 => $diskon,
                    'harga_nett'             => $nett,
                    'estimasi_kenaikan'      => $kenaikan,
                    'harga_setelah_kenaikan' => $afterInc,
                    'total_acc'              => 0,
                    'grand_total'            => $afterInc,
                ]);

                $createdVehicles[$vIndex] = $vehicle;
            }

            // Save accessories
            $vehicleAccTotals = array_fill_keys(array_keys($createdVehicles), 0.0);
            if (!empty($validated['accessories'])) {
                foreach ($validated['accessories'] as $aIndex => $acc) {
                    $accName = trim($acc['name'] ?? '');
                    if (!$accName) continue;

                    $amounts = $acc['amounts'] ?? [];
                    foreach ($createdVehicles as $vIndex => $vehicle) {
                        $amt = (float) ($amounts[$vIndex] ?? 0);
                        $vehicleAccTotals[$vIndex] += $amt;

                        CostingAccessory::create([
                            'costing_id'         => $costing->id,
                            'costing_vehicle_id' => $vehicle->id,
                            'accessory_name'     => $accName,
                            'amount'             => $amt,
                            'sort_order'         => $aIndex,
                        ]);
                    }
                }
            }

            // Update vehicle totals
            foreach ($createdVehicles as $vIndex => $vehicle) {
                $accTot = $vehicleAccTotals[$vIndex] ?? 0.0;
                $vehicle->update([
                    'total_acc'   => $accTot,
                    'grand_total' => $vehicle->harga_setelah_kenaikan + $accTot,
                ]);
            }

            return redirect()->route('costing.show', $costing)
                ->with('success', "Costing {$costing->costing_number} successfully saved.");
        });
    }

    /**
     * Display the specified costing.
     */
    public function show(Costing $costing)
    {
        $costing->load(['creator', 'vehicles.accessories']);

        // Group accessories by name across vehicles
        $accNames = [];
        $accRows = [];
        foreach ($costing->vehicles as $vIndex => $vehicle) {
            foreach ($vehicle->accessories as $acc) {
                $name = $acc->accessory_name;
                if (!isset($accRows[$name])) {
                    $accRows[$name] = [
                        'name' => $name,
                        'amounts' => [],
                    ];
                    $accNames[] = $name;
                }
                $accRows[$name]['amounts'][$vehicle->id] = (float) $acc->amount;
            }
        }

        return view('costing.show', compact('costing', 'accRows', 'accNames'));
    }

    /**
     * Show the form for editing the specified costing.
     */
    public function edit(Costing $costing)
    {
        $costing->load(['vehicles.accessories']);
        $bbnList   = CostingBbnMaster::orderBy('kode')->get();
        $templates = CostingAccessoryTemplate::orderBy('sort_order')->get();
        $brands    = $this->odooService->getCarBrands();
        $products  = $this->odooService->getCarProducts();

        // Organize accessories rows across vehicles
        $accRows = [];
        foreach ($costing->vehicles as $vehicle) {
            foreach ($vehicle->accessories as $acc) {
                $name = $acc->accessory_name;
                if (!isset($accRows[$name])) {
                    $accRows[$name] = [
                        'name' => $name,
                        'amounts' => [],
                    ];
                }
                $accRows[$name]['amounts'][$vehicle->id] = (float) $acc->amount;
            }
        }

        $vendors   = $this->odoo->getVendors();

        return view('costing.edit', compact('costing', 'bbnList', 'templates', 'brands', 'products', 'accRows', 'vendors'));
    }

    /**
     * Update the specified costing in storage.
     */
    public function update(Request $request, Costing $costing)
    {
        $validated = $request->validate([
            'customer_name'             => ['required', 'string', 'max:255'],
            'odoo_partner_id'           => ['nullable', 'integer'],
            'notes'                     => ['nullable', 'string'],
            'vehicles'                  => ['required', 'array', 'min:1'],
            'vehicles.*.brand'          => ['nullable', 'string', 'max:100'],
            'vehicles.*.vehicle_type'   => ['nullable', 'string', 'max:255'],
            'vehicles.*.odoo_product_id'=> ['nullable', 'integer'],
            'vehicles.*.bbn'            => ['nullable', 'string', 'max:100'],
            'vehicles.*.pemakaian'      => ['nullable', 'string', 'max:255'],
            'vehicles.*.harga_otr'      => ['nullable', 'numeric', 'min:0'],
            'vehicles.*.diskon'         => ['nullable', 'numeric', 'min:0'],
            'vehicles.*.estimasi_kenaikan'=> ['nullable', 'numeric', 'min:0'],
            'accessories'               => ['nullable', 'array'],
            'accessories.*.name'        => ['nullable', 'string', 'max:255'],
            'accessories.*.amounts'     => ['nullable', 'array'],
        ]);

        return DB::transaction(function () use ($validated, $costing) {
            $costing->update([
                'customer_name'   => $validated['customer_name'],
                'odoo_partner_id' => $validated['odoo_partner_id'] ?? null,
                'notes'           => $validated['notes'] ?? null,
            ]);

            // Clear old vehicles and accessories (cascade deletes accessories)
            $costing->vehicles()->delete();

            // Re-create vehicles
            $createdVehicles = [];
            $sortOrder = 0;
            foreach ($validated['vehicles'] as $vIndex => $vData) {
                $otr       = (float) ($vData['harga_otr'] ?? 0);
                $diskon    = (float) ($vData['diskon'] ?? 0);
                $nett      = max(0, $otr - $diskon);
                $kenaikan  = (float) ($vData['estimasi_kenaikan'] ?? 0);
                $afterInc  = $nett + $kenaikan;

                $vehicle = CostingVehicle::create([
                    'costing_id'             => $costing->id,
                    'sort_order'             => $sortOrder++,
                    'brand'                  => $vData['brand'] ?? '',
                    'vehicle_type'           => $vData['vehicle_type'] ?? '',
                    'odoo_product_id'        => !empty($vData['odoo_product_id']) ? (int) $vData['odoo_product_id'] : null,
                    'bbn'                    => $vData['bbn'] ?? '',
                    'pemakaian'              => $vData['pemakaian'] ?? '',
                    'harga_otr'              => $otr,
                    'diskon'                 => $diskon,
                    'harga_nett'             => $nett,
                    'estimasi_kenaikan'      => $kenaikan,
                    'harga_setelah_kenaikan' => $afterInc,
                    'total_acc'              => 0,
                    'grand_total'            => $afterInc,
                ]);

                $createdVehicles[$vIndex] = $vehicle;
            }

            // Re-create accessories
            $vehicleAccTotals = array_fill_keys(array_keys($createdVehicles), 0.0);
            if (!empty($validated['accessories'])) {
                foreach ($validated['accessories'] as $aIndex => $acc) {
                    $accName = trim($acc['name'] ?? '');
                    if (!$accName) continue;

                    $amounts = $acc['amounts'] ?? [];
                    foreach ($createdVehicles as $vIndex => $vehicle) {
                        $amt = (float) ($amounts[$vIndex] ?? 0);
                        $vehicleAccTotals[$vIndex] += $amt;

                        CostingAccessory::create([
                            'costing_id'         => $costing->id,
                            'costing_vehicle_id' => $vehicle->id,
                            'accessory_name'     => $accName,
                            'amount'             => $amt,
                            'sort_order'         => $aIndex,
                        ]);
                    }
                }
            }

            // Update vehicle totals
            foreach ($createdVehicles as $vIndex => $vehicle) {
                $accTot = $vehicleAccTotals[$vIndex] ?? 0.0;
                $vehicle->update([
                    'total_acc'   => $accTot,
                    'grand_total' => $vehicle->harga_setelah_kenaikan + $accTot,
                ]);
            }

            return redirect()->route('costing.show', $costing)
                ->with('success', "Costing {$costing->costing_number} updated successfully.");
        });
    }

    /**
     * Remove the specified costing from storage.
     */
    public function destroy(Costing $costing)
    {
        $num = $costing->costing_number;
        $costing->delete();

        return redirect()->route('costing.index')
            ->with('success', "Costing {$num} has been deleted.");
    }

    /**
     * Export the specified costing to Excel (.xlsx).
     */
    public function export(Costing $costing, \App\Services\CostingExportService $exportService)
    {
        return $exportService->export($costing);
    }

    /**
     * API for searching customers from Odoo.
     */
    public function apiSearchCustomers(Request $request)
    {
        $q = (string) $request->input('q', '');
        return response()->json($this->odooService->searchCustomers($q));
    }

    /**
     * API for getting products filtered by brand.
     */
    public function apiProducts(Request $request)
    {
        $brand = (string) $request->input('brand', '');
        $all = $this->odooService->getCarProducts();

        if (!$brand) {
            return response()->json($all);
        }

        $filtered = array_values(array_filter($all, function ($p) use ($brand) {
            if (empty($p['vehicle_brand_id']) || !is_array($p['vehicle_brand_id'])) return false;
            return stripos($p['vehicle_brand_id'][1], $brand) !== false;
        }));

        return response()->json($filtered);
    }

    /**
     * Refresh Costing vehicle catalog, BBN, and suppliers from Odoo (AJAX).
     * Isolated strictly to the Costing module without affecting RFQs.
     */
    public function apiRefreshCatalog()
    {
        $this->odooService->flushCostingCache();
        \Illuminate\Support\Facades\Cache::forget('odoo_vendors');

        $products = $this->odooService->getCarProducts();
        $brands   = $this->odooService->getCarBrands();
        $bbnList  = $this->odooService->syncBbnMaster();
        $vendors  = $this->odoo->getVendors();

        return response()->json([
            'success'  => true,
            'message'  => 'Costing vehicle catalog, BBN, and suppliers refreshed from Odoo.',
            'brands'   => $brands,
            'products' => $products,
            'bbn'      => $bbnList,
            'vendors'  => $vendors,
            'counts'   => [
                'brands'    => count($brands),
                'products'  => count($products),
                'bbn'       => count($bbnList),
                'suppliers' => count($vendors),
            ],
        ]);
    }
}

