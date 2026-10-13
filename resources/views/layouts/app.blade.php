<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Vendor Comparison') – Harent</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @stack('styles')

    <style>
        :root {
            --brand: #6d28d9;
            --brand-light: #ede9fe;
        }

        body {
            background-color: #f8f7ff;
            font-size: 0.875rem;
        }

        /* Navbar */
        .navbar-brand {
            font-weight: 700;
            letter-spacing: .03em;
        }

        .navbar {
            background: var(--brand) !important;
        }

        .navbar .navbar-brand,
        .navbar .nav-link {
            color: #fff !important;
        }

        .navbar .nav-link:hover {
            opacity: .8;
        }

        /* Cards */
        .card {
            border: 1px solid #e5e7eb;
            border-radius: .75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .06);
        }

        .card-header {
            background: var(--brand-light);
            border-bottom: 1px solid #d8d4f8;
            border-radius: .75rem .75rem 0 0 !important;
        }

        .card-header h5 {
            margin: 0;
            font-weight: 600;
            color: var(--brand);
        }

        /* Tables */
        .table th {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            background: #f3f4f6;
        }

        .table-hover tbody tr:hover td {
            background: var(--brand-light);
        }

        /* Badges */
        .badge-rfq {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-sent {
            background: #d1fae5;
            color: #065f46;
        }

        /* Price comparison */
        .price-best {
            background: #dcfce7 !important;
            font-weight: 600;
            color: #15803d;
        }

        .price-worst {
            background: #fee2e2 !important;
            color: #b91c1c;
        }

        .price-current {
            background: #fef9c3 !important;
            color: #854d0e !important;
        }

        /* Sticky product header within comparison card */
        .product-block {
            margin-bottom: 2rem;
        }

        .product-name {
            font-weight: 600;
            font-size: .95rem;
        }

        /* Footer */
        footer {
            font-size: .75rem;
            color: #9ca3af;
            margin-top: 3rem;
            padding-bottom: 1.5rem;
            text-align: center;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg mb-4 d-print-none">
        <div class="container-xl">
            @php
                $isCostingModule = request()->is('costing*');
                $hasCostingAccess = Auth::check() && Auth::user()->canAccessCosting();
            @endphp

            @if ($hasCostingAccess)
                <div class="dropdown me-3">
                    <button class="btn btn-link text-white text-decoration-none dropdown-toggle fw-bold p-0 fs-6 d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        @if ($isCostingModule)
                            <i class="bi bi-calculator me-2 fs-5"></i>Costing
                        @else
                            <i class="bi bi-bar-chart-steps me-2 fs-5"></i>Vendor Comparison
                        @endif
                    </button>
                    <ul class="dropdown-menu shadow">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 {{ !$isCostingModule ? 'active' : '' }}" href="{{ Auth::check() && Auth::user()->isViewer() ? route('rfq.list') : route('rfq.index') }}">
                                <i class="bi bi-bar-chart-steps {{ !$isCostingModule ? '' : 'text-primary' }}"></i>
                                <div>
                                    <div class="fw-semibold">Vendor Comparison</div>
                                    <div class="small {{ !$isCostingModule ? 'text-white-50' : 'text-muted' }}">RFQ & Approvals</div>
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 {{ $isCostingModule ? 'active' : '' }}" href="{{ route('costing.index') }}">
                                <i class="bi bi-calculator {{ $isCostingModule ? '' : 'text-success' }}"></i>
                                <div>
                                    <div class="fw-semibold">Costing</div>
                                    <div class="small {{ $isCostingModule ? 'text-white-50' : 'text-muted' }}">Vehicle Costing & Estimation</div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            @else
                <a class="navbar-brand" href="{{ Auth::check() && Auth::user()->isViewer() ? route('rfq.list') : route('rfq.index') }}">
                    <i class="bi bi-bar-chart-steps me-2"></i>Vendor Comparison
                </a>
            @endif

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto">
                    @if ($isCostingModule)
                        {{-- Costing Module Navigation --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('costing.index') ? 'active fw-bold' : '' }}" href="{{ route('costing.index') }}">
                                <i class="bi bi-table me-1"></i>Costing List
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('costing.create') ? 'active fw-bold' : '' }}" href="{{ route('costing.create') }}">
                                <i class="bi bi-plus-circle me-1"></i>New Costing
                            </a>
                        </li>
                        @if (Auth::user()->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.users') ? 'active fw-bold' : '' }}" href="{{ route('admin.users') }}">
                                    <i class="bi bi-people me-1"></i>Users
                                </a>
                            </li>
                        @endif
                    @else
                        {{-- Vendor Comparison Module Navigation (100% Unchanged) --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('rfq.list') }}">
                                <i class="bi bi-file-earmark-text me-1"></i>RFQ List
                            </a>
                        </li>
                        @auth
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('rfq.index') }}">
                                    <i class="bi bi-list-ul me-1"></i>Comparison List
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('comparisons.index') }}">
                                    <i class="bi bi-check2-square me-1"></i>Approvals
                                </a>
                            </li>
                            @if (!Auth::user()->isController() && !Auth::user()->isViewer())
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('master-suppliers.index') }}">
                                        <i class="bi bi-building me-1"></i>Master Supplier
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->isController() || Auth::user()->isManager() || Auth::user()->isAdmin())
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center gap-1" href="{{ route('comparisons.karoseri') }}">
                                        <i class="bi bi-eye-fill me-1"></i>Karoseri Ack.
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->isAdmin())
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.users') }}">
                                        <i class="bi bi-people me-1"></i>Users
                                    </a>
                                </li>
                            @endif
                        @endauth
                    @endif
                </ul>
                @auth
                    <ul class="navbar-nav ms-auto align-items-center gap-2">
                        <li class="nav-item">
                            <span class="navbar-text text-white-50 small">
                                <i class="bi bi-person-circle me-1"></i>
                                {{ Auth::user()->name }}
                                <span class="badge bg-white text-dark ms-1" style="font-size:.7rem">
                                    {{ Auth::user()->roleBadge() }}
                                </span>
                            </span>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-light">
                                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                @endauth
            </div>
        </div>
    </nav>

    <main class="@yield('main_container_class', 'container-xl') pb-4">
        {{-- Flash messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="d-print-none">
        &copy; {{ date('Y') }} Harent &middot; Vendor Comparison Portal
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
