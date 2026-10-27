<?php

namespace App\Http\Controllers\Costing;

use App\Http\Controllers\Controller;
use App\Models\Costing;
use App\Models\CostingAccessory;
use App\Models\CostingAccessoryTemplate;
use App\Models\CostingBbnMaster;
use App\Models\CostingBrand;
use App\Models\CostingLokasiPemakaian;
use App\Models\CostingVehicle;
use App\Models\CostingVehicleType;
use App\Services\CostingOdooService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CostingController extends Controller
{
    public function __construct(
        private CostingOdooService $odooService
    ) {}

    /**
     * Display a listing of costings.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Costing::with(['creator', 'vehicles'])
            ->orderByDesc('created_at');

        // Scoping: Managers and Admins can see all costings; other users can only see costings they created
        if (!$user->isAdmin() && !$user->isManager()) {
            $query->where('created_by', $user->id);
        }

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
        $bbnList       = CostingBbnMaster::where('is_active', true)->orderBy('name')->get();
        $pemakaianList = CostingLokasiPemakaian::where('is_active', true)->orderBy('pulau')->orderBy('provinsi')->orderBy('name')->get();
        $templates     = CostingAccessoryTemplate::orderBy('sort_order')->get();
        $brands        = CostingBrand::where('is_active', true)->orderBy('name')->pluck('name')->toArray();
        $products      = CostingVehicleType::where('is_active', true)->orderBy('name')->get()->map(function ($vt) {
            return [
                'id'               => $vt->odoo_product_id ?: $vt->id,
                'name'             => $vt->name,
                'display_name'     => $vt->name,
                'vehicle_brand_id' => [0, $vt->brand_name],
            ];
        })->toArray();
        $smdList       = $this->odooService->getSmdList();
        $pulauList     = CostingLokasiPemakaian::where('is_active', true)->whereNotNull('pulau')->distinct()->orderBy('pulau')->pluck('pulau')->toArray();
        $provinsiList  = CostingLokasiPemakaian::where('is_active', true)->whereNotNull('provinsi')->distinct()->orderBy('provinsi')->pluck('provinsi')->toArray();
        $regionsJson   = CostingLokasiPemakaian::where('is_active', true)->get(['pulau', 'provinsi', 'name as area'])->toJson();

        return view('costing.create', compact('bbnList', 'pemakaianList', 'templates', 'brands', 'products', 'smdList', 'pulauList', 'provinsiList', 'regionsJson'));
    }


    /**
     * Store a newly created costing in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name'             => ['required', 'string', 'max:255'],
            'smd'                       => ['nullable', 'string', 'max:255'],
            'masa_sewa'                 => ['nullable', 'string', 'max:50'],
            'request_delivery_date'     => ['nullable', 'date'],
            'odoo_partner_id'           => ['nullable', 'integer'],
            'notes'                     => ['nullable', 'string'],
            'vehicles'                  => ['required', 'array', 'min:1'],
            'vehicles.*.pulau'          => ['nullable', 'string', 'max:100'],
            'vehicles.*.provinsi'       => ['nullable', 'string', 'max:100'],
            'vehicles.*.brand'          => ['nullable', 'string', 'max:100'],
            'vehicles.*.vehicle_type'   => ['nullable', 'string', 'max:255'],
            'vehicles.*.odoo_product_id'=> ['nullable', 'integer'],
            'vehicles.*.bbn'            => ['nullable', 'string', 'max:100'],
            'vehicles.*.pemakaian'      => ['nullable', 'string', 'max:255'],
            'vehicles.*.note'           => ['nullable', 'string'],
            'vehicles.*.harga_otr'      => ['nullable', 'numeric', 'min:0'],
            'vehicles.*.diskon'         => ['nullable', 'numeric', 'min:0'],
            'vehicles.*.estimasi_kenaikan'=> ['nullable', 'numeric', 'min:0'],
            'accessories'               => ['nullable', 'array'],
            'accessories.*.name'        => ['nullable', 'string', 'max:255'],
            'accessories.*.amounts'     => ['nullable', 'array'],
        ]);

        $isTender = $request->boolean('is_tender');
        $fphType = $isTender ? 'tender' : 'retail';
        $isDraft = $request->input('action') === 'draft';
        $status = $isDraft ? 'draft' : 'saved';
        $approvalStatus = $isDraft ? 'draft' : ($isTender ? 'pending' : 'approved');

        return DB::transaction(function () use ($validated, $request, $fphType, $approvalStatus, $status, $isDraft) {
            // Generate unique Costing Number
            $prefix = 'CST/' . date('Y/m') . '/';
            $last = Costing::where('costing_number', 'like', $prefix . '%')
                ->orderByDesc('costing_number')
                ->lockForUpdate()
                ->first();

            $seq = 1;
            if ($last && preg_match('/(\d+)$/', $last->costing_number, $m)) {
                $seq = (int) $m[1] + 1;
            }
            $costingNumber = $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

            $costing = Costing::create([
                'costing_number'        => $costingNumber,
                'customer_name'         => $validated['customer_name'],
                'smd'                   => $validated['smd'] ?? null,
                'masa_sewa'             => $validated['masa_sewa'] ?? null,
                'request_delivery_date' => $validated['request_delivery_date'] ?? null,
                'odoo_partner_id'       => $validated['odoo_partner_id'] ?? null,
                'notes'                 => $validated['notes'] ?? null,
                'fph_type'              => $fphType,
                'approval_status'       => $approvalStatus,
                'status'                => $status,
                'created_by'            => Auth::id(),
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
                    'pulau'                  => $vData['pulau'] ?? '',
                    'provinsi'               => $vData['provinsi'] ?? '',
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
                    'note'                   => $vData['note'] ?? null,
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
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isManager() && $costing->created_by !== $user->id) {
            abort(403, 'Unauthorized. You can only view costings that you created.');
        }

        $costing->load(['creator', 'vehicles.accessories']);

        // Auto-sort vehicles by Pulau -> Provinsi -> BBN -> sort_order so grouping & horizontal cell merging are cohesive
        $sortedVehicles = $costing->vehicles->sortBy([
            ['pulau', 'asc'],
            ['provinsi', 'asc'],
            ['bbn', 'asc'],
            ['sort_order', 'asc'],
        ])->values();
        $costing->setRelation('vehicles', $sortedVehicles);

        // Group accessories by name across vehicles
        $accNames = [];
        $accRows = [];
        foreach ($sortedVehicles as $vIndex => $vehicle) {
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
        $user = Auth::user();
        if (!$user->isAdmin() && $costing->created_by !== $user->id) {
            abort(403, 'Unauthorized. You can only edit costings that you created.');
        }

        if ($costing->isTender()) {
            if ($costing->isPendingApproval()) {
                return redirect()->route('costing.show', $costing)
                    ->with('error', 'Costing ' . $costing->costing_number . ' is currently pending approval by the Purchasing Manager and cannot be edited.');
            }

            if ($costing->isApproved()) {
                return redirect()->route('costing.show', $costing)
                    ->with('error', 'Tender Costing ' . $costing->costing_number . ' has been approved and is locked from further editing.');
            }
        }

        $costing->load(['vehicles.accessories']);
        $bbnList   = CostingBbnMaster::where('is_active', true)->orderBy('name')->get();
        $templates = CostingAccessoryTemplate::orderBy('sort_order')->get();
        $brands    = CostingBrand::where('is_active', true)->orderBy('name')->pluck('name')->toArray();
        $products  = CostingVehicleType::where('is_active', true)->orderBy('name')->get()->map(function ($vt) {
            return [
                'id'               => $vt->odoo_product_id ?: $vt->id,
                'name'             => $vt->name,
                'display_name'     => $vt->name,
                'vehicle_brand_id' => [0, $vt->brand_name],
            ];
        })->toArray();


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

        $smdList       = $this->odooService->getSmdList();
        $pemakaianList = CostingLokasiPemakaian::where('is_active', true)->orderBy('pulau')->orderBy('provinsi')->orderBy('name')->get();
        $pulauList     = CostingLokasiPemakaian::where('is_active', true)->whereNotNull('pulau')->distinct()->orderBy('pulau')->pluck('pulau')->toArray();
        $provinsiList  = CostingLokasiPemakaian::where('is_active', true)->whereNotNull('provinsi')->distinct()->orderBy('provinsi')->pluck('provinsi')->toArray();
        $regionsJson   = CostingLokasiPemakaian::where('is_active', true)->get(['pulau', 'provinsi', 'name as area'])->toJson();

        return view('costing.edit', compact('costing', 'bbnList', 'pemakaianList', 'templates', 'brands', 'products', 'accRows', 'smdList', 'pulauList', 'provinsiList', 'regionsJson'));
    }

    /**
     * Update the specified costing in storage.
     */
    public function update(Request $request, Costing $costing)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $costing->created_by !== $user->id) {
            abort(403, 'Unauthorized. You can only edit costings that you created.');
        }

        if ($costing->isTender()) {
            if ($costing->isPendingApproval()) {
                return redirect()->route('costing.show', $costing)
                    ->with('error', 'Costing ' . $costing->costing_number . ' is currently pending approval by the Purchasing Manager and cannot be edited.');
            }

            if ($costing->isApproved()) {
                return redirect()->route('costing.show', $costing)
                    ->with('error', 'Tender Costing ' . $costing->costing_number . ' has been approved and is locked from further editing.');
            }
        }

        $validated = $request->validate([
            'customer_name'             => ['required', 'string', 'max:255'],
            'smd'                       => ['nullable', 'string', 'max:255'],
            'masa_sewa'                 => ['nullable', 'string', 'max:50'],
            'request_delivery_date'     => ['nullable', 'date'],
            'odoo_partner_id'           => ['nullable', 'integer'],
            'notes'                     => ['nullable', 'string'],
            'vehicles'                  => ['required', 'array', 'min:1'],
            'vehicles.*.pulau'          => ['nullable', 'string', 'max:100'],
            'vehicles.*.provinsi'       => ['nullable', 'string', 'max:100'],
            'vehicles.*.brand'          => ['nullable', 'string', 'max:100'],
            'vehicles.*.vehicle_type'   => ['nullable', 'string', 'max:255'],
            'vehicles.*.odoo_product_id'=> ['nullable', 'integer'],
            'vehicles.*.bbn'            => ['nullable', 'string', 'max:100'],
            'vehicles.*.pemakaian'      => ['nullable', 'string', 'max:255'],
            'vehicles.*.note'           => ['nullable', 'string'],
            'vehicles.*.harga_otr'      => ['nullable', 'numeric', 'min:0'],
            'vehicles.*.diskon'         => ['nullable', 'numeric', 'min:0'],
            'vehicles.*.estimasi_kenaikan'=> ['nullable', 'numeric', 'min:0'],
            'accessories'               => ['nullable', 'array'],
            'accessories.*.name'        => ['nullable', 'string', 'max:255'],
            'accessories.*.amounts'     => ['nullable', 'array'],
        ]);

        $isTender = $request->boolean('is_tender');
        $newFphType = $isTender ? 'tender' : 'retail';
        $isDraft = $request->input('action') === 'draft';

        return DB::transaction(function () use ($validated, $costing, $newFphType, $isDraft) {
            $updateData = [
                'customer_name'         => $validated['customer_name'],
                'smd'                   => $validated['smd'] ?? null,
                'masa_sewa'             => $validated['masa_sewa'] ?? null,
                'request_delivery_date' => $validated['request_delivery_date'] ?? null,
                'odoo_partner_id'       => $validated['odoo_partner_id'] ?? null,
                'notes'                 => $validated['notes'] ?? null,
                'fph_type'              => $newFphType,
            ];

            if ($isDraft) {
                $updateData['status']           = 'draft';
                $updateData['approval_status']  = 'draft';
                $updateData['approved_by']      = null;
                $updateData['approved_at']      = null;
                $updateData['rejection_reason'] = null;
            } elseif ($newFphType === 'tender') {
                $updateData['status']           = 'saved';
                $updateData['approval_status']  = 'pending';
                $updateData['approved_by']      = null;
                $updateData['approved_at']      = null;
                $updateData['rejection_reason'] = null;
            } else {
                $updateData['status']           = 'saved';
                $updateData['approval_status']  = 'approved';
                $updateData['approved_by']      = null;
                $updateData['approved_at']      = null;
                $updateData['rejection_reason'] = null;
            }

            $costing->update($updateData);

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
                    'pulau'                  => $vData['pulau'] ?? '',
                    'provinsi'               => $vData['provinsi'] ?? '',
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
                    'note'                   => $vData['note'] ?? null,
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
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return redirect()->route('costing.index')
                ->with('error', 'Only Administrators have permission to delete Costings.');
        }

        $num = $costing->costing_number;
        $costing->delete();

        return redirect()->route('costing.index')
            ->with('success', "Costing {$num} has been deleted.");
    }

    public function export(Costing $costing, \App\Services\CostingExportService $exportService)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isManager() && $costing->created_by !== $user->id) {
            abort(403, 'Unauthorized. You can only export costings that you created.');
        }

        if ($costing->isTender()) {
            if ($costing->isPendingApproval()) {
                return redirect()->route('costing.show', $costing)
                    ->with('error', 'Costing ' . $costing->costing_number . ' is currently pending approval and cannot be exported to Excel.');
            }

            if ($costing->isRejected()) {
                return redirect()->route('costing.show', $costing)
                    ->with('error', 'Costing ' . $costing->costing_number . ' was rejected and cannot be exported to Excel.');
            }
        }

        return $exportService->export($costing);
    }

    /**
     * Display the listing of Tender Costings requiring/having Manager approval.
     */
    public function approvals(Request $request)
    {
        $status = $request->input('status', 'pending');

        $query = Costing::with(['creator', 'approver', 'vehicles'])
            ->where('fph_type', 'tender')
            ->orderByDesc('created_at');

        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('approval_status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('costing_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $costings = $query->paginate(15)->withQueryString();

        $counts = [
            'all'      => Costing::where('fph_type', 'tender')->count(),
            'pending'  => Costing::where('fph_type', 'tender')->where('approval_status', 'pending')->count(),
            'approved' => Costing::where('fph_type', 'tender')->where('approval_status', 'approved')->count(),
            'rejected' => Costing::where('fph_type', 'tender')->where('approval_status', 'rejected')->count(),
        ];

        return view('costing.approvals', compact('costings', 'status', 'counts'));
    }

    /**
     * Approve a Tender Costing (Manager / Admin only).
     */
    public function approve(Request $request, Costing $costing)
    {
        $user = Auth::user();
        if (!$user->isManager() && !$user->isAdmin()) {
            return back()->with('error', 'Only Managers or Administrators can approve tender costings.');
        }

        $costing->update([
            'approval_status'  => 'approved',
            'approved_by'      => $user->id,
            'approved_at'      => now(),
            'rejection_reason' => null,
        ]);

        return back()->with('success', "Costing {$costing->costing_number} has been approved.");
    }

    /**
     * Reject a Tender Costing (Manager / Admin only).
     */
    public function reject(Request $request, Costing $costing)
    {
        $user = Auth::user();
        if (!$user->isManager() && !$user->isAdmin()) {
            return back()->with('error', 'Only Managers or Administrators can reject tender costings.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $costing->update([
            'approval_status'  => 'rejected',
            'approved_by'      => $user->id,
            'approved_at'      => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', "Costing {$costing->costing_number} has been rejected.");
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
        $query = CostingVehicleType::where('is_active', true)->orderBy('name');

        if ($brand) {
            $query->where('brand_name', $brand);
        }

        $filtered = $query->get()->map(function ($vt) {
            return [
                'id'               => $vt->odoo_product_id ?: $vt->id,
                'name'             => $vt->name,
                'display_name'     => $vt->name,
                'vehicle_brand_id' => [0, $vt->brand_name],
            ];
        });

        return response()->json($filtered);
    }

    /**
     * Refresh Costing vehicle catalog, BBN, and suppliers from Odoo (AJAX).
     * Isolated strictly to the Costing module without affecting RFQs.
     */
    public function apiRefreshCatalog()
    {
        $this->odooService->flushCostingCache();

        // Safe master refresh directly via service (no seeders)
        $result = $this->odooService->syncCostingMasters();

        return response()->json([
            'success'  => true,
            'message'  => 'Costing vehicle catalog and BBN refreshed from Odoo.',
            'brands'   => $result['brands'],
            'products' => $result['products'],
            'bbn'      => $result['bbn'],
            'counts'   => [
                'brands'    => $result['counts']['brands'],
                'products'  => $result['counts']['products'],
                'bbn'       => $result['counts']['bbn'],
            ],
        ]);
    }
}

