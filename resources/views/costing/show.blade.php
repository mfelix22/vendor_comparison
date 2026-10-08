@extends('layouts.app')

@section('title', 'Costing ' . $costing->costing_number)

@push('styles')
<style>
    .costing-view-table th, .costing-view-table td {
        vertical-align: middle;
        padding: 0.65rem 0.85rem;
    }
    .costing-label-col {
        width: 250px;
        background-color: #f8fafc;
        font-weight: 600;
    }
    @media print {
        .no-print {
            display: none !important;
        }
        body {
            background-color: #fff !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>
@endpush

@section('content')

    {{-- Actions Bar --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 no-print">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('costing.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
            <div>
                <h4 class="fw-bold mb-0">Costing Details</h4>
                <span class="text-muted small">{{ $costing->costing_number }}</span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Print
            </button>
            <a href="{{ route('costing.export', $costing) }}" class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel me-1"></i>Download Excel (.xlsx)
            </a>
            <a href="{{ route('costing.edit', $costing) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil me-1"></i>Edit Sheet
            </a>
        </div>
    </div>

    {{-- Sheet Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">

            {{-- Header info --}}
            <div class="border-bottom pb-3 mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h4 class="fw-bold text-dark mb-1">{{ $costing->customer_name }}</h4>
                        <div class="text-muted small">
                            <span class="fw-semibold text-primary">{{ $costing->costing_number }}</span>
                            &middot; Created on {{ $costing->created_at->format('d F Y, H:i') }}
                            by {{ $costing->creator->name ?? 'User' }}
                        </div>
                    </div>
                    @if ($costing->notes)
                        <div class="bg-light p-2 rounded border small text-muted" style="max-width: 350px;">
                            <span class="fw-semibold d-block text-dark">Notes:</span>
                            {{ $costing->notes }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Comparison Table --}}
            <div class="table-responsive">
                <table class="table table-bordered mb-0 costing-view-table">
                    <thead>
                        <tr class="table-light">
                            <th class="costing-label-col py-3">Vehicle Details</th>
                            @foreach ($costing->vehicles as $v)
                                <th class="text-center py-3 bg-white" style="min-width: 220px;">
                                    <div class="fw-bold text-primary">{{ $v->brand }}</div>
                                    <div class="text-dark small fw-semibold">{{ $v->vehicle_type }}</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="costing-label-col">Brand :</td>
                            @foreach ($costing->vehicles as $v)
                                <td class="text-center fw-semibold">{{ $v->brand ?: '—' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="costing-label-col">Type Kendaraan :</td>
                            @foreach ($costing->vehicles as $v)
                                <td class="text-center fw-semibold">{{ $v->vehicle_type ?: '—' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="costing-label-col">BBN :</td>
                            @foreach ($costing->vehicles as $v)
                                <td class="text-center">{{ $v->bbn ?: '—' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="costing-label-col">Pemakaian :</td>
                            @foreach ($costing->vehicles as $v)
                                <td class="text-center">{{ $v->pemakaian ?: '—' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="costing-label-col">Harga OTR :</td>
                            @foreach ($costing->vehicles as $v)
                                <td class="text-end font-monospace">Rp {{ number_format($v->harga_otr, 0, ',', '.') }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="costing-label-col">Diskon :</td>
                            @foreach ($costing->vehicles as $v)
                                <td class="text-end font-monospace text-danger">
                                    {{ $v->diskon > 0 ? '- Rp ' . number_format($v->diskon, 0, ',', '.') : 'Rp 0' }}
                                </td>
                            @endforeach
                        </tr>
                        <tr class="table-success fw-bold">
                            <td class="costing-label-col text-success">Harga Nett :</td>
                            @foreach ($costing->vehicles as $v)
                                <td class="text-end font-monospace text-success">Rp {{ number_format($v->harga_nett, 0, ',', '.') }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="costing-label-col">Estimasi kenaikan harga :</td>
                            @foreach ($costing->vehicles as $v)
                                <td class="text-end font-monospace">Rp {{ number_format($v->estimasi_kenaikan, 0, ',', '.') }}</td>
                            @endforeach
                        </tr>
                        <tr class="table-warning fw-bold">
                            <td class="costing-label-col text-dark">Harga setelah estimasi kenaikan :</td>
                            @foreach ($costing->vehicles as $v)
                                <td class="text-end font-monospace text-dark">Rp {{ number_format($v->harga_setelah_kenaikan, 0, ',', '.') }}</td>
                            @endforeach
                        </tr>

                        {{-- Section: Accessories --}}
                        <tr class="table-light">
                            <td class="costing-label-col fw-bold text-primary py-2" colspan="{{ $costing->vehicles->count() + 1 }}">
                                <i class="bi bi-wrench-adjustable me-1"></i>Tambahan (Accessories & Upgrades) :
                            </td>
                        </tr>

                        @forelse ($accRows as $accName => $accData)
                            <tr>
                                <td class="costing-label-col text-dark small ps-4">{{ $accName }}</td>
                                @foreach ($costing->vehicles as $v)
                                    @php
                                        $amt = $accData['amounts'][$v->id] ?? 0;
                                    @endphp
                                    <td class="text-end font-monospace small">
                                        Rp {{ number_format($amt, 0, ',', '.') }}
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $costing->vehicles->count() + 1 }}" class="text-center text-muted small py-2">
                                    No accessories added.
                                </td>
                            </tr>
                        @endforelse

                        {{-- Row: Total Acc --}}
                        <tr class="table-secondary fw-bold">
                            <td class="costing-label-col">Total Acc :</td>
                            @foreach ($costing->vehicles as $v)
                                <td class="text-end font-monospace">Rp {{ number_format($v->total_acc, 0, ',', '.') }}</td>
                            @endforeach
                        </tr>

                        {{-- Row: Grand Total --}}
                        <tr style="background-color: #ede9fe !important;" class="fw-bold fs-6">
                            <td class="costing-label-col text-primary">Harga Unit + Aksesoris :</td>
                            @foreach ($costing->vehicles as $v)
                                <td class="text-end font-monospace text-primary">
                                    Rp {{ number_format($v->grand_total, 0, ',', '.') }}
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

@endsection
