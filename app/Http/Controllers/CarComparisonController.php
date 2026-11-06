<?php

namespace App\Http\Controllers;

use App\Models\CarComparisonLog;
use App\Models\CarVendorComparison;
use App\Models\MasterSupplier;
use App\Models\User;
use App\Services\OdooService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarComparisonController extends Controller
{
    public function __construct(private OdooService $odoo) {}

    /**
     * Ensure only Supervisor, Manager, and Admin can access Car Comparisons.
     */
    protected function authorizeCarAccess(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || (!$user->isSupervisor() && !$user->isManager() && !$user->isAdmin())) {
            abort(403, 'Unauthorized. Car Comparison is restricted to Supervisors and Managers.');
        }
    }

    /**
     * Dashboard for Car comparison approvals.
     */
    public function index()
    {
        $this->authorizeCarAccess();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = CarVendorComparison::with(['creator', 'supervisor', 'manager'])
            ->orderByDesc('created_at');

        if ($user->isSupervisor()) {
            $query->where(function ($q) use ($user) {
                $q->where('supervisor_id', $user->id)
                    ->orWhere('created_by', $user->id)
                    ->orWhereNull('supervisor_id');
            });
        }

        $comparisons = $query->get();

        $stats = [
            'pending_manager' => $comparisons->where('status', 'pending_manager')->count(),
            'approved'        => $comparisons->where('status', 'approved')->count(),
            'rejected'        => $comparisons->where('status', 'rejected')->count(),
            'cancelled'       => $comparisons->where('status', 'cancelled')->count(),
        ];

        return view('car_comparisons.index', compact('comparisons', 'stats'));
    }

    /**
     * Submit a new Car Vendor Comparison.
     */
    public function store(Request $request)
    {
        $this->authorizeCarAccess();

        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        $request->validate([
            'po_id'           => ['required', 'integer'],
            'po_name'         => ['required', 'string'],
            'po_vendor'       => ['nullable', 'string'],
            'car_model'       => ['nullable', 'string'],
            'qty'             => ['required', 'integer', 'min:1'],
            'uom'             => ['nullable', 'string'],
            'warna'           => ['nullable', 'string'],
            'bbn'             => ['nullable', 'string'],
            'vin_code'        => ['nullable', 'string'],
            'pricelist_ori'   => ['nullable', 'numeric'],
            'vendors'         => ['required', 'array', 'min:3', 'max:10'],
            'vendors.*.name'  => ['required', 'string'],
            'selected_vendor' => ['required', 'string', 'max:255'],
            'notes'           => ['nullable', 'string', 'max:2000'],
        ]);

        if (CarVendorComparison::where('po_id', $request->po_id)
            ->whereNotIn('status', ['rejected', 'cancelled'])->exists()
        ) {
            return back()->with('error', 'A comparison for this Car RFQ is already active. Please view it in Car Approvals.');
        }

        // Auto-assign supervisor based on Odoo purchase_spv_id or auth user
        $assignedSupervisorId = null;
        if ($authUser->isSupervisor()) {
            $assignedSupervisorId = $authUser->id;
        } else {
            try {
                $rfqData = $this->odoo->getCarRfq($request->po_id);
                if (!empty($rfqData['purchase_spv_id']) && is_array($rfqData['purchase_spv_id'])) {
                    $odooSpvName = $rfqData['purchase_spv_id'][1] ?? null;
                    if ($odooSpvName) {
                        $mapping  = config('odoo.spv_mapping', []);
                        $spvEmail = $mapping[$odooSpvName] ?? null;
                        if ($spvEmail) {
                            $spvUser = User::where('email', $spvEmail)->first();
                            if ($spvUser) {
                                $assignedSupervisorId = $spvUser->id;
                            }
                        }
                    }
                }
            } catch (\Throwable) {}

            if (!$assignedSupervisorId) {
                $assignedSupervisorId = User::where('role', 'supervisor')->value('id') ?: $authUser->id;
            }
        }

        // Generate comparison code: YYYY/CP-CAR/NNNNN
        $year     = now()->year;
        $lastCode = CarVendorComparison::where('comparison_code', 'like', "{$year}/CP-CAR/%")
            ->orderByDesc('comparison_code')
            ->value('comparison_code');
        $seq      = $lastCode ? ((int) substr($lastCode, -5)) + 1 : 1;
        $comparisonCode = $year . '/CP-CAR/' . str_pad($seq, 5, '0', STR_PAD_LEFT);

        $comparison = CarVendorComparison::create([
            'comparison_code'         => $comparisonCode,
            'po_id'                   => $request->po_id,
            'po_name'                 => $request->po_name,
            'po_vendor'               => $request->po_vendor,
            'car_model'               => $request->car_model,
            'qty'                     => $request->qty,
            'uom'                     => $request->uom ?: 'unit',
            'warna'                   => $request->warna,
            'bbn'                     => $request->bbn,
            'vin_code'                => $request->vin_code,
            'pricelist_ori'           => $request->pricelist_ori ?: 0,
            'vendors'                 => $request->vendors,
            'selected_vendor'         => $request->selected_vendor,
            'notes'                   => $request->notes,
            'status'                  => 'pending_manager', // Directly pending manager
            'supervisor_id'           => $assignedSupervisorId ?: $authUser->id,
            'supervisor_submitted_at' => now(),
            'created_by'              => $authUser->id,
        ]);

        CarComparisonLog::create([
            'car_comparison_id' => $comparison->id,
            'user_id'           => $authUser->id,
            'action'            => 'submitted_supervisor',
            'notes'             => $request->notes,
        ]);

        session()->flash('clear_draft_key', "car_clvp_draft_{$request->po_id}");

        return redirect()->route('car_comparisons.index')
            ->with('success', "Car Comparison for {$request->po_name} submitted successfully and is now pending Manager approval.");
    }

    /**
     * Edit a car comparison (while pending_manager).
     */
    public function edit(CarVendorComparison $comparison)
    {
        $this->authorizeCarAccess();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$comparison->isEditableBy($user)) {
            abort(403, 'This comparison cannot be edited at its current stage.');
        }

        $history = [];
        try {
            $rfq     = $this->odoo->getCarRfq($comparison->po_id);
            $vendors = $this->odoo->getVendors();
            if (!empty($rfq['car_line']) && is_array($rfq['car_line']['product_id'] ?? null)) {
                $history = $this->odoo->getProductVendorHistory([$rfq['car_line']['product_id'][0]]);
            }
        } catch (\Throwable) {
            $rfq     = null;
            $vendors = [];
        }

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
            'comparison'         => $comparison,
            'existingComparison' => null,
            'error'              => null,
        ]);
    }

    /**
     * Update an existing car comparison.
     */
    public function update(Request $request, CarVendorComparison $comparison)
    {
        $this->authorizeCarAccess();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$comparison->isEditableBy($user)) {
            abort(403, 'This comparison cannot be edited at its current stage.');
        }

        $request->validate([
            'car_model'       => ['nullable', 'string'],
            'qty'             => ['required', 'integer', 'min:1'],
            'uom'             => ['nullable', 'string'],
            'warna'           => ['nullable', 'string'],
            'bbn'             => ['nullable', 'string'],
            'vin_code'        => ['nullable', 'string'],
            'pricelist_ori'   => ['nullable', 'numeric'],
            'vendors'         => ['required', 'array', 'min:3', 'max:10'],
            'vendors.*.name'  => ['required', 'string'],
            'selected_vendor' => ['required', 'string', 'max:255'],
            'notes'           => ['nullable', 'string', 'max:2000'],
        ]);

        $comparison->update([
            'car_model'       => $request->car_model,
            'qty'             => $request->qty,
            'uom'             => $request->uom ?: 'unit',
            'warna'           => $request->warna,
            'bbn'             => $request->bbn,
            'vin_code'        => $request->vin_code,
            'pricelist_ori'   => $request->pricelist_ori ?: 0,
            'vendors'         => $request->vendors,
            'selected_vendor' => $request->selected_vendor,
            'notes'           => $request->notes,
        ]);

        CarComparisonLog::create([
            'car_comparison_id' => $comparison->id,
            'user_id'           => $user->id,
            'action'            => 'edited',
            'notes'             => 'Car comparison data updated before manager review.',
        ]);

        session()->flash('clear_draft_key', "car_clvp_draft_{$comparison->po_id}");

        return redirect()->route('car_comparisons.show', $comparison)
            ->with('success', 'Car comparison updated successfully.');
    }

    /**
     * Show a single Car comparison.
     */
    public function show(CarVendorComparison $comparison)
    {
        $this->authorizeCarAccess();

        $comparison->load(['creator', 'supervisor', 'manager', 'rejectedBy', 'cancelledBy', 'logs.user']);

        $rfq = null;
        $history = [];
        try {
            $rfq = $this->odoo->getCarRfq($comparison->po_id);
            if (!empty($rfq['car_line']) && is_array($rfq['car_line']['product_id'] ?? null)) {
                $history = $this->odoo->getProductVendorHistory([$rfq['car_line']['product_id'][0]]);
            }
        } catch (\Throwable) {}

        // Previous and Next navigation
        $statusFilter = request('status');
        $query = CarVendorComparison::query();

        if (Auth::user()->isSupervisor()) {
            $query->where(function ($q) {
                $q->where('supervisor_id', Auth::id())
                    ->orWhere('created_by', Auth::id());
            });
        }
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $items = $query->orderByDesc('created_at')->orderByDesc('id')->get(['id', 'created_at']);
        $currentIndex = $items->search(fn($item) => $item->id === $comparison->id);

        $prevId = null;
        $nextId = null;

        if ($currentIndex !== false) {
            if ($currentIndex > 0) {
                $prevId = $items[$currentIndex - 1]->id;
            }
            if ($currentIndex < $items->count() - 1) {
                $nextId = $items[$currentIndex + 1]->id;
            }
        } elseif ($statusFilter) {
            // Item not in list (e.g. status just changed). Find where it would be.
            $nextIndex = $items->search(function ($item) use ($comparison) {
                if ($item->created_at->lt($comparison->created_at)) return true;
                if ($item->created_at->eq($comparison->created_at) && $item->id < $comparison->id) return true;
                return false;
            });

            if ($nextIndex !== false) {
                $nextId = $items[$nextIndex]->id;
                if ($nextIndex > 0) {
                    $prevId = $items[$nextIndex - 1]->id;
                }
            } else {
                if ($items->count() > 0) {
                    $prevId = $items->last()->id;
                }
            }
        }

        return view('car_comparisons.show', compact('comparison', 'rfq', 'history', 'prevId', 'nextId', 'statusFilter'));
    }

    /**
     * Manager approves the Car comparison.
     */
    public function approve(Request $request, CarVendorComparison $comparison)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isManager() && !$user->isAdmin()) {
            return back()->with('error', 'Only the Manager or Admin may approve car comparisons.');
        }

        if (!$comparison->isPendingManager()) {
            return back()->with('error', 'This comparison is not pending manager approval.');
        }

        $request->validate(['notes' => ['nullable', 'string', 'max:2000']]);

        $comparison->update([
            'status'              => 'approved',
            'manager_id'          => $user->id,
            'manager_approved_at' => now(),
            'manager_notes'       => $request->notes,
        ]);

        CarComparisonLog::create([
            'car_comparison_id' => $comparison->id,
            'user_id'           => $user->id,
            'action'            => 'approved_manager',
            'notes'             => $request->notes,
        ]);

        return back()->with('success', 'Car comparison fully approved!');
    }

    /**
     * Reject a car comparison.
     */
    public function reject(Request $request, CarVendorComparison $comparison)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate(['rejection_reason' => ['required', 'string', 'max:2000']]);

        if (!$user->isManager() && !$user->isAdmin()) {
            return back()->with('error', 'Only managers or admins can reject car comparisons.');
        }

        if ($comparison->isApproved() || $comparison->isRejected()) {
            return back()->with('error', 'This comparison cannot be rejected in its current state.');
        }

        $comparison->update([
            'status'           => 'rejected',
            'rejected_by'      => $user->id,
            'rejected_at'      => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        CarComparisonLog::create([
            'car_comparison_id' => $comparison->id,
            'user_id'           => $user->id,
            'action'            => 'rejected',
            'notes'             => $request->rejection_reason,
        ]);

        return back()->with('success', 'Car comparison has been rejected.');
    }

    /**
     * Cancel an approved car comparison.
     */
    public function cancel(Request $request, CarVendorComparison $comparison)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate(['cancel_reason' => ['required', 'string', 'max:2000']]);

        if (!$comparison->isCancellableBy($user)) {
            return back()->with('error', 'You are not authorized to cancel this comparison.');
        }

        $comparison->update([
            'status'        => 'cancelled',
            'cancelled_by'  => $user->id,
            'cancelled_at'  => now(),
            'cancel_reason' => $request->cancel_reason,
        ]);

        CarComparisonLog::create([
            'car_comparison_id' => $comparison->id,
            'user_id'           => $user->id,
            'action'            => 'cancelled',
            'notes'             => $request->cancel_reason,
        ]);

        return back()->with('success', 'Car comparison has been cancelled.');
    }

    /**
     * Export Car CLVP as PDF (replicates physical [V] Unit Baru layout).
     */
    public function pdf(CarVendorComparison $comparison)
    {
        $this->authorizeCarAccess();

        $comparison->load(['creator', 'supervisor', 'manager']);

        $pdf = Pdf::loadView('car_comparisons.pdf', compact('comparison'))
            ->setPaper('a4', 'landscape')
            ->setOptions(['dpi' => 120, 'defaultFont' => 'Arial', 'isHtml5ParserEnabled' => true]);

        $filename = 'CLVP-CAR-' . str_replace('/', '-', $comparison->po_name) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Post CLVP Car PDF and chatter log note to Odoo PO.
     */
    public function odooPost(CarVendorComparison $comparison)
    {
        if (!$comparison->isApproved()) {
            return back()->with('error', 'Only approved comparisons can be posted to Odoo.');
        }

        $comparison->load(['creator', 'supervisor', 'manager']);

        try {
            $pdf = Pdf::loadView('car_comparisons.pdf', compact('comparison'))
                ->setPaper('a4', 'landscape')
                ->setOptions(['dpi' => 120, 'defaultFont' => 'Arial', 'isHtml5ParserEnabled' => true]);

            $pdfContent = $pdf->output();
            $pdfBase64  = base64_encode($pdfContent);
            $filename   = 'CLVP-CAR-' . str_replace('/', '-', $comparison->po_name) . '.pdf';

            // Attach PDF to Odoo /POU/ order
            $this->odoo->attachPdfToPO((int) $comparison->po_id, $pdfBase64, $filename);

            // Build chatter note HTML
            $vendors = collect($comparison->vendors ?? []);
            $vendorList = $vendors->map(fn($v) => htmlspecialchars($v['name'] ?? '—'))->implode(', ');
            $recommended = htmlspecialchars($comparison->selected_vendor ?? '—');
            $approvedBy  = htmlspecialchars($comparison->manager->name ?? '—');
            $approvedOn  = $comparison->manager_approved_at?->format('d M Y H:i') ?? '—';
            $supervisor  = htmlspecialchars($comparison->supervisor->name ?? '—');

            $customNote = trim(request('note', ''));
            $comparisonUrl = url(route('car_comparisons.show', $comparison, false));

            $body = <<<HTML
<p><strong>✅ CLVP (Comparison Local Vendor Price) — Unit Baru / Car Approved</strong></p>
<ul>
  <li><strong>Car Model:</strong> {$comparison->car_model} ({$comparison->qty} {$comparison->uom})</li>
  <li><strong>Color / BBN / Vin:</strong> {$comparison->warna} / {$comparison->bbn} / {$comparison->vin_code}</li>
  <li><strong>Recommended Dealer:</strong> {$recommended}</li>
  <li><strong>Dealers Compared:</strong> {$vendorList}</li>
  <li><strong>Prepared by Supervisor:</strong> {$supervisor}</li>
  <li><strong>Approved by Manager:</strong> {$approvedBy} on {$approvedOn}</li>
</ul>
<p>📎 <em>The CLVP Car document has been attached as a PDF file.</em></p>
<p>🔗 <a href="{$comparisonUrl}">View full Car CLVP on the Vendor Comparison portal</a></p>
HTML;

            if ($customNote !== '') {
                $safeNote = nl2br(htmlspecialchars($customNote));
                $body .= "<hr><p><strong>Note from " . htmlspecialchars(Auth::user()->name) . ":</strong><br>{$safeNote}</p>";
            }

            $this->odoo->postChatterNote((int) $comparison->po_id, $body);

            $comparison->update(['odoo_synced_at' => now()]);

            CarComparisonLog::create([
                'car_comparison_id' => $comparison->id,
                'user_id'           => Auth::id(),
                'action'            => 'odoo_posted',
                'notes'             => 'PDF attached and log note posted to Odoo PO ' . $comparison->po_name,
            ]);

            return back()->with('success', 'Car CLVP PDF attached and log note posted to Odoo successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to post to Odoo: ' . $e->getMessage());
        }
    }
}

