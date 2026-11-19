<?php

namespace App\Http\Controllers\Costing;

use App\Http\Controllers\Controller;
use App\Models\CostingBbnMaster;
use App\Models\CostingBrand;
use App\Models\CostingLokasiPemakaian;
use App\Models\CostingVehicleType;
use App\Models\CostingVehicleTypePriceHistory;
use App\Services\CostingOdooService;
use App\Services\CostingVehicleTypePriceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MasterDataController extends Controller
{
    public function __construct(
        private CostingOdooService $odooService,
        private CostingVehicleTypePriceService $priceService
    ) {}

    /**
     * Display the unified Master Data hub.
     */
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'brands');

        // 1. Brands
        $brandsQuery = CostingBrand::withCount('vehicleTypes')->orderBy('name');
        if ($searchBrand = $request->input('search_brand')) {
            $brandsQuery->where('name', 'like', "%{$searchBrand}%");
        }
        $brands = $brandsQuery->paginate(15, ['*'], 'brands_page')->withQueryString();

        // All active brands for dropdowns
        $allBrands = CostingBrand::where('is_active', true)->orderBy('name')->get();

        // 2. Vehicle Types (with active OTR, Diskon, and pending price changes)
        $pendingPriceCount = CostingVehicleTypePriceHistory::where('status', 'pending')->count();

        $typesQuery = CostingVehicleType::with(['brand', 'pendingPriceHistory.requester', 'priceHistories'])
            ->withCount('priceHistories')
            ->withCount(['priceHistories as pending_histories_count' => function ($q) {
                $q->where('status', 'pending');
            }])
            ->orderBy('brand_name')
            ->orderBy('name');

        if ($filterBrand = $request->input('filter_brand')) {
            $typesQuery->where('brand_name', $filterBrand);
        }
        if ($searchType = $request->input('search_type')) {
            $typesQuery->where(function ($q) use ($searchType) {
                $q->where('name', 'like', "%{$searchType}%")
                  ->orWhere('brand_name', 'like', "%{$searchType}%");
            });
        }
        if ($priceStatus = $request->input('price_status')) {
            if ($priceStatus === 'pending') {
                $typesQuery->whereHas('priceHistories', function ($q) {
                    $q->where('status', 'pending');
                });
            } elseif ($priceStatus === 'priced') {
                $typesQuery->where('harga_otr', '>', 0);
            } elseif ($priceStatus === 'unpriced') {
                $typesQuery->where(function ($q) {
                    $q->where('harga_otr', '<=', 0)->orWhereNull('harga_otr');
                });
            }
        }
        $vehicleTypes = $typesQuery->paginate(20, ['*'], 'types_page')->withQueryString();

        // 3. Lokasi Pemakaian (514 official Kota & Kabupaten baseline + custom additions)
        $pemakaianQuery = CostingLokasiPemakaian::orderBy('pulau')->orderBy('provinsi')->orderBy('name');
        if ($filterPulau = $request->input('filter_pulau')) {
            $pemakaianQuery->where('pulau', $filterPulau);
        }
        if ($filterProvinsi = $request->input('filter_provinsi')) {
            $pemakaianQuery->where('provinsi', $filterProvinsi);
        }
        if ($searchPemakaian = $request->input('search_pemakaian')) {
            $pemakaianQuery->where(function ($q) use ($searchPemakaian) {
                $q->where('name', 'like', "%{$searchPemakaian}%")
                  ->orWhere('pulau', 'like', "%{$searchPemakaian}%")
                  ->orWhere('provinsi', 'like', "%{$searchPemakaian}%");
            });
        }
        $pemakaianList = $pemakaianQuery->paginate(20, ['*'], 'pemakaian_page')->withQueryString();

        // 4. BBN Masters (Samsat vehicle tax registration areas)
        $bbnQuery = CostingBbnMaster::orderBy('name');
        if ($searchBbn = $request->input('search_bbn')) {
            $bbnQuery->where(function ($q) use ($searchBbn) {
                $q->where('kode', 'like', "%{$searchBbn}%")
                  ->orWhere('name', 'like', "%{$searchBbn}%")
                  ->orWhere('display_name', 'like', "%{$searchBbn}%");
            });
        }
        $bbnList = $bbnQuery->paginate(20, ['*'], 'bbn_page')->withQueryString();

        // Distinct Pulau and Provinsi for dropdown filters & modals
        $pulauList = CostingLokasiPemakaian::whereNotNull('pulau')->distinct()->orderBy('pulau')->pluck('pulau')->toArray();
        if (empty($pulauList)) {
            $pulauList = ['JAWA', 'SUMATERA', 'KALIMANTAN', 'SULAWESI', 'BALI & NUSA TENGGARA', 'PAPUA & MALUKU'];
        }
        $provinsiList = CostingLokasiPemakaian::whereNotNull('provinsi')->distinct()->orderBy('provinsi')->pluck('provinsi')->toArray();

        return view('costing.master.index', compact(
            'tab', 'brands', 'allBrands', 'vehicleTypes', 'pemakaianList', 'bbnList', 'pulauList', 'provinsiList', 'pendingPriceCount'
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

        $brand->update([
            'name'      => strtoupper(trim($validated['name'])),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('costing.master.index', ['tab' => 'brands'])
            ->with('success', "Brand {$brand->name} updated successfully.");
    }

    public function destroyBrand(CostingBrand $brand)
    {
        $name = $brand->name;
        $brand->delete();

        return redirect()->route('costing.master.index', ['tab' => 'brands'])
            ->with('success', "Brand {$name} deleted successfully.");
    }

    // ── Vehicle Type Actions ─────────────────────────────────────────────────

    public function storeVehicleType(Request $request)
    {
        $validated = $request->validate([
            'costing_brand_id' => ['required', 'exists:costing_brands,id'],
            'name'             => ['required', 'string', 'max:255'],
            'harga_otr'        => ['nullable', 'numeric', 'min:0'],
            'diskon'           => ['nullable', 'numeric', 'min:0'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ]);

        $brand = CostingBrand::findOrFail($validated['costing_brand_id']);
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $isManagerOrAdmin = $user->isManager() || $user->isAdmin();
        $newOtr = (float) ($request->input('harga_otr', 0) ?: 0);
        $newDiskon = (float) ($request->input('diskon', 0) ?: 0);
        $notes = $request->input('notes');

        if ($newOtr > 0 || $newDiskon > 0) {
            if ($isManagerOrAdmin) {
                $vehicleType = CostingVehicleType::create([
                    'costing_brand_id' => $brand->id,
                    'brand_name'       => $brand->name,
                    'name'             => trim($validated['name']),
                    'harga_otr'        => $newOtr,
                    'diskon'           => $newDiskon,
                    'is_active'        => true,
                    'created_by'       => $user->id,
                ]);

                CostingVehicleTypePriceHistory::create([
                    'costing_vehicle_type_id' => $vehicleType->id,
                    'old_harga_otr'           => 0,
                    'new_harga_otr'           => $newOtr,
                    'old_diskon'              => 0,
                    'new_diskon'              => $newDiskon,
                    'status'                  => 'approved',
                    'source'                  => 'manual_create',
                    'requested_by'            => $user->id,
                    'approved_by'             => $user->id,
                    'approved_at'             => now(),
                    'notes'                   => $notes,
                ]);

                $msg = "Vehicle Type {$vehicleType->name} ({$brand->name}) added with approved Harga OTR & Diskon.";
            } else {
                $vehicleType = CostingVehicleType::create([
                    'costing_brand_id' => $brand->id,
                    'brand_name'       => $brand->name,
                    'name'             => trim($validated['name']),
                    'harga_otr'        => 0,
                    'diskon'           => 0,
                    'is_active'        => true,
                    'created_by'       => $user->id,
                ]);

                CostingVehicleTypePriceHistory::create([
                    'costing_vehicle_type_id' => $vehicleType->id,
                    'old_harga_otr'           => 0,
                    'new_harga_otr'           => $newOtr,
                    'old_diskon'              => 0,
                    'new_diskon'              => $newDiskon,
                    'status'                  => 'pending',
                    'source'                  => 'manual_create',
                    'requested_by'            => $user->id,
                    'notes'                   => $notes,
                ]);

                $msg = "Vehicle Type {$vehicleType->name} added. Initial price submitted for Purchasing Manager approval.";
            }
        } else {
            $vehicleType = CostingVehicleType::create([
                'costing_brand_id' => $brand->id,
                'brand_name'       => $brand->name,
                'name'             => trim($validated['name']),
                'harga_otr'        => 0,
                'diskon'           => 0,
                'is_active'        => true,
                'created_by'       => $user->id,
            ]);

            $msg = "Vehicle Type {$vehicleType->name} ({$brand->name}) added successfully.";
        }

        return redirect()->route('costing.master.index', ['tab' => 'types'])
            ->with('success', $msg);
    }

    public function updateVehicleType(Request $request, CostingVehicleType $vehicleType)
    {
        $validated = $request->validate([
            'costing_brand_id' => ['required', 'exists:costing_brands,id'],
            'name'             => ['required', 'string', 'max:255'],
            'harga_otr'        => ['nullable', 'numeric', 'min:0'],
            'diskon'           => ['nullable', 'numeric', 'min:0'],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'is_active'        => ['nullable', 'boolean'],
        ]);

        $brand = CostingBrand::findOrFail($validated['costing_brand_id']);

        $vehicleType->update([
            'costing_brand_id' => $brand->id,
            'brand_name'       => $brand->name,
            'name'             => trim($validated['name']),
            'is_active'        => $request->has('is_active'),
        ]);

        $newOtr = isset($validated['harga_otr']) ? (float)$validated['harga_otr'] : (float)$vehicleType->harga_otr;
        $newDiskon = isset($validated['diskon']) ? (float)$validated['diskon'] : (float)$vehicleType->diskon;
        $notes = $request->input('notes');

        // Check if price changed
        if (abs($newOtr - (float)$vehicleType->harga_otr) > 0.01 || abs($newDiskon - (float)$vehicleType->diskon) > 0.01) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $history = $this->priceService->recordManualChange($vehicleType, $newOtr, $newDiskon, $notes, $user);

            $msg = ($history->status === 'approved')
                ? "Vehicle Type {$vehicleType->name} and prices updated successfully."
                : "Vehicle Type {$vehicleType->name} updated. New prices submitted for Purchasing Manager approval.";
        } else {
            $msg = "Vehicle Type {$vehicleType->name} updated successfully.";
        }

        return redirect()->route('costing.master.index', ['tab' => 'types'])
            ->with('success', $msg);
    }

    public function updateVehicleTypePrice(Request $request, CostingVehicleType $vehicleType)
    {
        $validated = $request->validate([
            'harga_otr' => ['required', 'numeric', 'min:0'],
            'diskon'    => ['nullable', 'numeric', 'min:0'],
            'notes'     => ['nullable', 'string', 'max:1000'],
        ]);

        $newOtr = (float) $validated['harga_otr'];
        $newDiskon = (float) ($validated['diskon'] ?? 0);
        $notes = $validated['notes'] ?? null;
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $history = $this->priceService->recordManualChange($vehicleType, $newOtr, $newDiskon, $notes, $user);

        $msg = ($history->status === 'approved')
            ? "Price for {$vehicleType->name} updated and approved."
            : "Price change for {$vehicleType->name} submitted. Awaiting Purchasing Manager approval.";

        return redirect()->route('costing.master.index', ['tab' => 'types'])
            ->with('success', $msg);
    }

    public function approvePriceHistory(CostingVehicleTypePriceHistory $history)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isManager() && !$user->isAdmin()) {
            abort(403, 'Only Purchasing Manager or Administrator can approve price changes.');
        }

        $this->priceService->approveHistory($history, $user);

        return redirect()->route('costing.master.index', ['tab' => 'types'])
            ->with('success', "Price revision for {$history->vehicleType->name} has been approved.");
    }

    public function rejectPriceHistory(Request $request, CostingVehicleTypePriceHistory $history)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isManager() && !$user->isAdmin()) {
            abort(403, 'Only Purchasing Manager or Administrator can reject price changes.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ]);

        $reason = $validated['rejection_reason'] ?? $validated['notes'] ?? null;

        $this->priceService->rejectHistory($history, $user, $reason);

        return redirect()->route('costing.master.index', ['tab' => 'types'])
            ->with('success', "Price revision for {$history->vehicleType->name} has been rejected.");
    }

    public function bulkApprovePrices(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isManager() && !$user->isAdmin()) {
            abort(403, 'Only Purchasing Manager or Administrator can approve price changes.');
        }

        $historyIds = $request->input('history_ids', []);
        if (empty($historyIds)) {
            // Approve all currently pending
            $historyIds = CostingVehicleTypePriceHistory::where('status', 'pending')->pluck('id')->toArray();
        }

        $count = $this->priceService->bulkApprove($historyIds, $user);

        return redirect()->route('costing.master.index', ['tab' => 'types'])
            ->with('success', "{$count} pending vehicle price revisions approved successfully.");
    }

    public function downloadPriceTemplate()
    {
        return $this->priceService->downloadTemplate();
    }

    public function importPrices(Request $request)
    {
        $request->validate([
            'file'         => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
            'import_notes' => ['nullable', 'string', 'max:500'],
            'auto_approve' => ['nullable', 'boolean'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $autoApprove = $request->boolean('auto_approve');
        $notes = $request->input('import_notes');

        try {
            $stats = $this->priceService->importFromExcel($request->file('file'), $user, $notes, $autoApprove);

            $msg = "Excel Import finished: {$stats['updated_count']} model price(s) processed ({$stats['approved_count']} approved immediately, {$stats['pending_count']} pending Manager approval). {$stats['skipped_count']} unchanged/skipped.";

            if (!empty($stats['errors'])) {
                $errSummary = implode(', ', array_slice($stats['errors'], 0, 3));
                if (count($stats['errors']) > 3) $errSummary .= ' and ' . (count($stats['errors']) - 3) . ' more.';
                return redirect()->route('costing.master.index', ['tab' => 'types'])
                    ->with('success', $msg)
                    ->with('warning', "Notice: " . $errSummary);
            }

            return redirect()->route('costing.master.index', ['tab' => 'types'])
                ->with('success', $msg);
        } catch (\Throwable $e) {
            return redirect()->route('costing.master.index', ['tab' => 'types'])
                ->with('error', "Import failed: " . $e->getMessage());
        }
    }

    public function previewPrices(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ]);

        try {
            $preview = $this->priceService->previewFromExcel($request->file('file'));

            return response()->json([
                'success' => true,
                'preview' => $preview,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file Excel: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function apiVehicleTypePriceHistory(CostingVehicleType $vehicleType)
    {
        $histories = $vehicleType->priceHistories()
            ->with(['requester:id,name,email', 'approver:id,name,email'])
            ->get();

        return response()->json([
            'vehicle_type' => [
                'id'         => $vehicleType->id,
                'name'       => $vehicleType->name,
                'brand_name' => $vehicleType->brand_name,
                'harga_otr'  => (float) $vehicleType->harga_otr,
                'diskon'     => (float) $vehicleType->diskon,
            ],
            'histories' => $histories,
        ]);
    }

    public function destroyVehicleType(CostingVehicleType $vehicleType)
    {
        $name = $vehicleType->name;
        $vehicleType->delete();

        return redirect()->route('costing.master.index', ['tab' => 'types'])
            ->with('success', "Vehicle Type {$name} deleted successfully.");
    }

    // ── Lokasi Pemakaian Actions ─────────────────────────────────────────────

    public function storePemakaian(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:150', 'unique:costing_lokasi_pemakaians,name'],
            'pulau'    => ['required', 'string', 'max:100'],
            'provinsi' => ['required', 'string', 'max:100'],
        ]);

        $name = trim($validated['name']);
        $pulau = strtoupper(trim($validated['pulau']));
        $provinsi = strtoupper(trim($validated['provinsi']));

        CostingLokasiPemakaian::create([
            'name'       => $name,
            'pulau'      => $pulau,
            'provinsi'   => $provinsi,
            'is_active'  => true,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('costing.master.index', ['tab' => 'pemakaian'])
            ->with('success', "Lokasi Pemakaian {$name} added successfully.");
    }

    public function updatePemakaian(Request $request, CostingLokasiPemakaian $pemakaian)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:150', 'unique:costing_lokasi_pemakaians,name,' . $pemakaian->id],
            'pulau'     => ['required', 'string', 'max:100'],
            'provinsi'  => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $name = trim($validated['name']);
        $pulau = strtoupper(trim($validated['pulau']));
        $provinsi = strtoupper(trim($validated['provinsi']));

        $pemakaian->update([
            'name'      => $name,
            'pulau'     => $pulau,
            'provinsi'  => $provinsi,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('costing.master.index', ['tab' => 'pemakaian'])
            ->with('success', "Lokasi Pemakaian {$name} updated successfully.");
    }

    public function destroyPemakaian(CostingLokasiPemakaian $pemakaian)
    {
        $name = $pemakaian->name;
        $pemakaian->delete();

        return redirect()->route('costing.master.index', ['tab' => 'pemakaian'])
            ->with('success', "Lokasi Pemakaian {$name} deleted successfully.");
    }

    // ── BBN Actions ──────────────────────────────────────────────────────────

    public function storeBbn(Request $request)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:20', 'unique:costing_bbn_masters,kode'],
            'name' => ['required', 'string', 'max:100'],
        ]);

        $code = strtoupper(trim($validated['kode']));
        $name = trim($validated['name']);

        CostingBbnMaster::create([
            'kode'         => $code,
            'name'         => $name,
            'display_name' => $name,
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
            'is_active' => ['nullable', 'boolean'],
        ]);

        $code = strtoupper(trim($validated['kode']));
        $name = trim($validated['name']);

        $bbn->update([
            'kode'         => $code,
            'name'         => $name,
            'display_name' => $name,
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

        $types = $query->get(['id', 'brand_name', 'name', 'odoo_product_id', 'harga_otr', 'diskon'])
            ->map(function ($t) {
                $otr = (float) $t->harga_otr;
                $disc = (float) $t->diskon;
                return [
                    'id'              => $t->id,
                    'brand_name'      => $t->brand_name,
                    'name'            => $t->name,
                    'odoo_product_id' => $t->odoo_product_id,
                    'harga_otr'       => $otr,
                    'diskon'          => $disc,
                    'harga_netto'     => max(0, $otr - $disc),
                ];
            });

        return response()->json($types);
    }

    public function apiLokasiPemakaian()
    {
        $pemakaianList = CostingLokasiPemakaian::where('is_active', true)
            ->orderBy('pulau')
            ->orderBy('provinsi')
            ->orderBy('name')
            ->get(['id', 'name', 'pulau', 'provinsi']);

        return response()->json($pemakaianList);
    }

    public function apiBbn()
    {
        $bbnList = CostingBbnMaster::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'kode', 'name', 'display_name']);

        return response()->json($bbnList);
    }
}
