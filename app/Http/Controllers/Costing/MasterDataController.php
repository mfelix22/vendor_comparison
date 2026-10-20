<?php

namespace App\Http\Controllers\Costing;

use App\Http\Controllers\Controller;
use App\Models\CostingBbnMaster;
use App\Models\CostingBrand;
use App\Models\CostingRegion;
use App\Models\CostingVehicleType;
use App\Services\CostingOdooService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MasterDataController extends Controller
{
    public function __construct(
        private CostingOdooService $odooService
    ) {}

    /**
     * Display the unified Master Data hub.
     */
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'brands');

        // Brands
        $brandsQuery = CostingBrand::withCount('vehicleTypes')->orderBy('name');
        if ($searchBrand = $request->input('search_brand')) {
            $brandsQuery->where('name', 'like', "%{$searchBrand}%");
        }
        $brands = $brandsQuery->paginate(15, ['*'], 'brands_page')->withQueryString();

        // All active brands for dropdowns
        $allBrands = CostingBrand::where('is_active', true)->orderBy('name')->get();

        // Vehicle Types
        $typesQuery = CostingVehicleType::with('brand')->orderBy('brand_name')->orderBy('name');
        if ($filterBrand = $request->input('filter_brand')) {
            $typesQuery->where('brand_name', $filterBrand);
        }
        if ($searchType = $request->input('search_type')) {
            $typesQuery->where(function ($q) use ($searchType) {
                $q->where('name', 'like', "%{$searchType}%")
                  ->orWhere('brand_name', 'like', "%{$searchType}%");
            });
        }
        $vehicleTypes = $typesQuery->paginate(20, ['*'], 'types_page')->withQueryString();

        // BBN Masters
        $bbnQuery = CostingBbnMaster::orderBy('pulau')->orderBy('provinsi')->orderBy('name');
        if ($searchBbn = $request->input('search_bbn')) {
            $bbnQuery->where(function ($q) use ($searchBbn) {
                $q->where('kode', 'like', "%{$searchBbn}%")
                  ->orWhere('name', 'like', "%{$searchBbn}%")
                  ->orWhere('pulau', 'like', "%{$searchBbn}%")
                  ->orWhere('provinsi', 'like', "%{$searchBbn}%");
            });
        }
        $bbnList = $bbnQuery->paginate(20, ['*'], 'bbn_page')->withQueryString();

        // Regions (Data Wilayah: Pulau & Provinsi)
        $regionsQuery = CostingRegion::orderBy('pulau')->orderBy('provinsi')->orderBy('area');
        if ($filterPulau = $request->input('filter_pulau')) {
            $regionsQuery->where('pulau', $filterPulau);
        }
        if ($filterProvinsi = $request->input('filter_provinsi')) {
            $regionsQuery->where('provinsi', $filterProvinsi);
        }
        if ($searchArea = $request->input('search_area')) {
            $regionsQuery->where(function ($q) use ($searchArea) {
                $q->where('area', 'like', "%{$searchArea}%")
                  ->orWhere('provinsi', 'like', "%{$searchArea}%")
                  ->orWhere('pulau', 'like', "%{$searchArea}%");
            });
        }
        $regionsList = $regionsQuery->paginate(25, ['*'], 'regions_page')->withQueryString();
        $pulauList = CostingRegion::distinct()->orderBy('pulau')->pluck('pulau')->toArray();
        $provinsiList = CostingRegion::distinct()->orderBy('provinsi')->pluck('provinsi')->toArray();

        return view('costing.master.index', compact(
            'tab', 'brands', 'allBrands', 'vehicleTypes', 'bbnList',
            'regionsList', 'pulauList', 'provinsiList'
        ));
    }

    // ── Brand Actions ────────────────────────────────────────────────────────

    public function storeBrand(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:costing_brands,name'],
        ]);

        CostingBrand::create([
            'name'       => strtoupper(trim($validated['name'])),
            'is_active'  => true,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('costing.master.index', ['tab' => 'brands'])
            ->with('success', "Brand {$validated['name']} added successfully.");
    }

    public function updateBrand(Request $request, CostingBrand $brand)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:100', 'unique:costing_brands,name,' . $brand->id],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldName = $brand->name;
        $newName = strtoupper(trim($validated['name']));

        $brand->update([
            'name'      => $newName,
            'is_active' => $request->has('is_active'),
        ]);

        // Keep cached brand_name in sync on children
        if ($oldName !== $newName) {
            $brand->vehicleTypes()->update(['brand_name' => $newName]);
        }

        return redirect()->route('costing.master.index', ['tab' => 'brands'])
            ->with('success', "Brand {$newName} updated successfully.");
    }

    public function destroyBrand(CostingBrand $brand)
    {
        $name = $brand->name;
        $brand->delete();

        return redirect()->route('costing.master.index', ['tab' => 'brands'])
            ->with('success', "Brand {$name} deleted successfully.");
    }

    // ── Vehicle Type Actions ──────────────────────────────────────────────────

    public function storeVehicleType(Request $request)
    {
        $validated = $request->validate([
            'costing_brand_id' => ['required', 'exists:costing_brands,id'],
            'name'             => ['required', 'string', 'max:255'],
        ]);

        $brand = CostingBrand::findOrFail($validated['costing_brand_id']);

        CostingVehicleType::create([
            'costing_brand_id' => $brand->id,
            'brand_name'       => $brand->name,
            'name'             => trim($validated['name']),
            'is_active'        => true,
            'created_by'       => Auth::id(),
        ]);

        return redirect()->route('costing.master.index', ['tab' => 'types', 'filter_brand' => $brand->name])
            ->with('success', "Vehicle Type {$validated['name']} added for {$brand->name}.");
    }

    public function updateVehicleType(Request $request, CostingVehicleType $vehicleType)
    {
        $validated = $request->validate([
            'costing_brand_id' => ['required', 'exists:costing_brands,id'],
            'name'             => ['required', 'string', 'max:255'],
            'is_active'        => ['nullable', 'boolean'],
        ]);

        $brand = CostingBrand::findOrFail($validated['costing_brand_id']);

        $vehicleType->update([
            'costing_brand_id' => $brand->id,
            'brand_name'       => $brand->name,
            'name'             => trim($validated['name']),
            'is_active'        => $request->has('is_active'),
        ]);

        return redirect()->route('costing.master.index', ['tab' => 'types'])
            ->with('success', "Vehicle Type {$vehicleType->name} updated successfully.");
    }

    public function destroyVehicleType(CostingVehicleType $vehicleType)
    {
        $name = $vehicleType->name;
        $vehicleType->delete();

        return redirect()->route('costing.master.index', ['tab' => 'types'])
            ->with('success', "Vehicle Type {$name} deleted successfully.");
    }

    // ── BBN Actions ──────────────────────────────────────────────────────────

    public function storeBbn(Request $request)
    {
        $validated = $request->validate([
            'kode'     => ['required', 'string', 'max:20', 'unique:costing_bbn_masters,kode'],
            'name'     => ['required', 'string', 'max:100'],
            'pulau'    => ['nullable', 'string', 'max:100'],
            'provinsi' => ['nullable', 'string', 'max:100'],
        ]);

        $code = strtoupper(trim($validated['kode']));
        $name = trim($validated['name']);
        $pulau = !empty($validated['pulau']) ? strtoupper(trim($validated['pulau'])) : null;
        $provinsi = !empty($validated['provinsi']) ? strtoupper(trim($validated['provinsi'])) : null;

        CostingBbnMaster::create([
            'kode'         => $code,
            'name'         => $name,
            'display_name' => $name,
            'pulau'        => $pulau,
            'provinsi'     => $provinsi,
            'is_active'    => true,
            'created_by'   => Auth::id(),
        ]);

        return redirect()->route('costing.master.index', ['tab' => 'bbn'])
            ->with('success', "BBN {$name} ({$code}) added successfully.");
    }

    public function updateBbn(Request $request, CostingBbnMaster $bbn)
    {
        $validated = $request->validate([
            'kode'      => ['required', 'string', 'max:20', 'unique:costing_bbn_masters,kode,' . $bbn->id],
            'name'      => ['required', 'string', 'max:100'],
            'pulau'     => ['nullable', 'string', 'max:100'],
            'provinsi'  => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $code = strtoupper(trim($validated['kode']));
        $name = trim($validated['name']);
        $pulau = !empty($validated['pulau']) ? strtoupper(trim($validated['pulau'])) : null;
        $provinsi = !empty($validated['provinsi']) ? strtoupper(trim($validated['provinsi'])) : null;

        $bbn->update([
            'kode'         => $code,
            'name'         => $name,
            'display_name' => $name,
            'pulau'        => $pulau,
            'provinsi'     => $provinsi,
            'is_active'    => $request->has('is_active'),
        ]);

        return redirect()->route('costing.master.index', ['tab' => 'bbn'])
            ->with('success', "BBN {$name} updated successfully.");
    }

    public function destroyBbn(CostingBbnMaster $bbn)
    {
        $name = $bbn->name ?: $bbn->kode;
        $bbn->delete();

        return redirect()->route('costing.master.index', ['tab' => 'bbn'])
            ->with('success', "BBN {$name} deleted successfully.");
    }

    // ── Region Actions (Data Wilayah: Pulau & Provinsi) ───────────────────────

    public function storeRegion(Request $request)
    {
        $validated = $request->validate([
            'pulau'    => ['required', 'string', 'max:100'],
            'provinsi' => ['required', 'string', 'max:100'],
            'area'     => ['required', 'string', 'max:150'],
        ]);

        $pulau = strtoupper(trim($validated['pulau']));
        $provinsi = strtoupper(trim($validated['provinsi']));
        $area = trim($validated['area']);

        CostingRegion::create([
            'pulau'      => $pulau,
            'provinsi'   => $provinsi,
            'area'       => $area,
            'is_active'  => true,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('costing.master.index', [
            'tab'             => 'regions',
            'filter_pulau'    => $pulau,
            'filter_provinsi' => $provinsi,
        ])->with('success', "Wilayah {$area} ({$provinsi}, {$pulau}) added successfully.");
    }

    public function updateRegion(Request $request, CostingRegion $region)
    {
        $validated = $request->validate([
            'pulau'     => ['required', 'string', 'max:100'],
            'provinsi'  => ['required', 'string', 'max:100'],
            'area'      => ['required', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $pulau = strtoupper(trim($validated['pulau']));
        $provinsi = strtoupper(trim($validated['provinsi']));
        $area = trim($validated['area']);

        $region->update([
            'pulau'     => $pulau,
            'provinsi'  => $provinsi,
            'area'      => $area,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('costing.master.index', [
            'tab'             => 'regions',
            'filter_pulau'    => $pulau,
            'filter_provinsi' => $provinsi,
        ])->with('success', "Wilayah {$area} updated successfully.");
    }

    public function destroyRegion(CostingRegion $region)
    {
        $area = $region->area;
        $region->delete();

        return redirect()->route('costing.master.index', ['tab' => 'regions'])
            ->with('success', "Wilayah {$area} deleted successfully.");
    }

    // ── Safe One-Click Odoo Sync ─────────────────────────────────────────────

    public function syncFromOdoo(Request $request)
    {
        $tab = $request->input('tab', 'brands');

        try {
            $result = $this->odooService->syncCostingMasters();

            return redirect()->route('costing.master.index', ['tab' => $tab])
                ->with('success', "Master data refreshed from Odoo ({$result['counts']['brands']} brands, {$result['counts']['products']} types, {$result['counts']['bbn']} BBN regions). Local custom entries safely preserved.");
        } catch (\Throwable $e) {
            return redirect()->route('costing.master.index', ['tab' => $tab])
                ->with('error', 'Sync encountered an error: ' . $e->getMessage());
        }
    }

    // ── APIs for Costing Create/Edit Dynamic Dropdowns ────────────────────────

    public function apiBrands()
    {
        $brands = CostingBrand::where('is_active', true)->orderBy('name')->pluck('name');
        return response()->json($brands);
    }

    public function apiVehicleTypes(Request $request)
    {
        $brand = (string) $request->input('brand', '');
        $query = CostingVehicleType::where('is_active', true)->orderBy('name');

        if ($brand) {
            $query->where('brand_name', $brand);
        }

        $types = $query->get(['id', 'brand_name', 'name', 'odoo_product_id']);
        return response()->json($types);
    }

    public function apiBbn()
    {
        $bbnList = CostingBbnMaster::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'kode', 'name', 'display_name', 'pulau', 'provinsi']);

        return response()->json($bbnList);
    }

    public function apiRegions(Request $request)
    {
        $query = CostingRegion::where('is_active', true)->orderBy('pulau')->orderBy('provinsi')->orderBy('area');

        if ($pulau = $request->input('pulau')) {
            $query->where('pulau', $pulau);
        }
        if ($provinsi = $request->input('provinsi')) {
            $query->where('provinsi', $provinsi);
        }

        $regions = $query->get(['id', 'pulau', 'provinsi', 'area']);
        return response()->json($regions);
    }
}
