<?php

namespace App\Http\Controllers;

use App\Models\CarVendorComparison;
use App\Models\MasterSupplier;
use App\Services\OdooService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CarRfqController extends Controller
{
    public function __construct(private OdooService $odoo) {}

    /**
     * Ensure only authorized users (Supervisor, Manager, Admin, or users granted Car Access) can access Car RFQs.
     */
    protected function authorizeCarAccess(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->canAccessCar()) {
            abort(403, 'Unauthorized. You do not have access to Car Comparison.');
        }
    }

    /**
     * List all Car RFQs from Odoo (/POU/).
     */
    public function index()
    {
        $this->authorizeCarAccess();
        try {
            $rfqs = $this->odoo->getCarRfqs();
            $odooError = null;
        } catch (\Throwable $e) {
            $rfqs = [];
            $odooError = $e->getMessage();
        }

        // Map po_id -> latest comparison for status badge display
        $existingComparisons = [];
        if (!empty($rfqs)) {
            $existingComparisons = CarVendorComparison::whereIn('po_id', array_column($rfqs, 'id'))
                ->orderByDesc('id')
                ->get(['id', 'po_id', 'status', 'comparison_code'])
                ->unique('po_id')
                ->keyBy('po_id');
        }

        $cachedAt = Cache::get('odoo_car_rfqs_cached_at');

        return view('car_rfq.index', compact('rfqs', 'odooError', 'existingComparisons', 'cachedAt'));
    }

    /**
     * Flush cache and re-sync Car RFQs from Odoo.
     */
    public function refresh()
    {
        $this->authorizeCarAccess();
        $this->odoo->flushCarCache();
        return redirect()->route('car_rfq.index')->with('success', 'Car RFQ data refreshed from Odoo.');
    }

    /**
     * Show the Car comparison matrix form for a single RFQ.
     */
    public function show(int $id)
    {
        $this->authorizeCarAccess();
        $history = [];
        try {
            $rfq = $this->odoo->getCarRfq($id);

            if (!$rfq) {
                abort(404, 'Car RFQ not found in Odoo.');
            }

            $vendors = $this->odoo->getVendors();

            $productIds = [];
            if (!empty($rfq['car_line']) && is_array($rfq['car_line']['product_id'] ?? null)) {
                $productIds[] = $rfq['car_line']['product_id'][0];
            }
            if (!empty($productIds)) {
                $history = $this->odoo->getProductVendorHistory($productIds);
            }
        } catch (\Throwable $e) {
            return view('car_rfq.show', [
                'rfq'                => null,
                'history'            => [],
                'vendors'            => [],
                'masterSuppliers'    => [],
                'existingComparison' => null,
                'comparison'         => null,
                'error'              => $e->getMessage(),
            ]);
        }

        $existingComparison = CarVendorComparison::where('po_id', $id)
            ->latest()
            ->first();

        $masterSuppliers = MasterSupplier::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'street', 'street2', 'city', 'phone', 'mobile', 'email'])
            ->map(fn($s) => [
                'id'      => 'local_' . $s->id,
                'name'    => $s->name,
                'street'  => $s->street,
                'street2' => $s->street2,
                'city'    => $s->city,
                'phone'   => $s->phone,
                'mobile'  => $s->mobile,
                'email'   => $s->email,
                'source'  => 'local',
            ])
            ->toArray();

        return view('car_rfq.show', [
            'rfq'                => $rfq,
            'history'            => $history,
            'vendors'            => $vendors,
            'masterSuppliers'    => $masterSuppliers,
            'existingComparison' => $existingComparison,
            'comparison'         => null,
            'error'              => null,
        ]);
    }
}

