<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\MasterSupplierController;
use App\Http\Controllers\RfqController;
use Illuminate\Support\Facades\Route;

// ── Auth ───────────────────────────────────────────────
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── Protected routes ───────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/', fn() => redirect()->route('rfq.index'));

    // RFQ (read from Odoo)
    Route::get('/rfq', [RfqController::class, 'index'])->name('rfq.index');
    Route::get('/rfq-list', [RfqController::class, 'rfqList'])->name('rfq.list');
    Route::post('/rfq/refresh', [RfqController::class, 'refresh'])->name('rfq.refresh');
    Route::get('/rfq/{id}', [RfqController::class, 'show'])->name('rfq.show')->where('id', '[0-9]+');

    // Comparisons (stored locally)
    Route::get('/comparisons', [ComparisonController::class, 'index'])->name('comparisons.index');
    Route::post('/comparisons', [ComparisonController::class, 'store'])->name('comparisons.store');
    Route::get('/comparisons/{comparison}', [ComparisonController::class, 'show'])->name('comparisons.show');
    Route::get('/comparisons/{comparison}/edit', [ComparisonController::class, 'edit'])->name('comparisons.edit');
    Route::put('/comparisons/{comparison}', [ComparisonController::class, 'update'])->name('comparisons.update');
    Route::post('/comparisons/{comparison}/approve', [ComparisonController::class, 'approve'])->name('comparisons.approve');
    Route::post('/comparisons/{comparison}/bypass-approve', [ComparisonController::class, 'bypassApprove'])->name('comparisons.bypass-approve');
    Route::post('/comparisons/{comparison}/reject', [ComparisonController::class, 'reject'])->name('comparisons.reject');
    Route::post('/comparisons/{comparison}/cancel', [ComparisonController::class, 'cancel'])->name('comparisons.cancel');
    Route::get('/comparisons/{comparison}/pdf', [ComparisonController::class, 'pdf'])->name('comparisons.pdf');
    Route::post('/comparisons/{comparison}/odoo-post', [ComparisonController::class, 'odooPost'])->name('comparisons.odoo-post');
    Route::post('/comparisons/{comparison}/acknowledge', [ComparisonController::class, 'acknowledge'])->name('comparisons.acknowledge');
    Route::get('/karoseri', [ComparisonController::class, 'karoseriIndex'])->name('comparisons.karoseri');

    // Master Suppliers (local vendors not in Odoo)
    Route::get('/master-suppliers', [MasterSupplierController::class, 'index'])->name('master-suppliers.index');
    Route::post('/master-suppliers', [MasterSupplierController::class, 'store'])->name('master-suppliers.store');
    Route::put('/master-suppliers/{masterSupplier}', [MasterSupplierController::class, 'update'])->name('master-suppliers.update');
    Route::delete('/master-suppliers/{masterSupplier}', [MasterSupplierController::class, 'destroy'])->name('master-suppliers.destroy');

    // Admin: user management
    Route::get('/admin/users', [AdminController::class, 'index'])->name('admin.users');
    Route::post('/admin/users', [AdminController::class, 'store'])->name('admin.users.store');
    Route::put('/admin/users/{user}', [AdminController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [AdminController::class, 'destroy'])->name('admin.users.destroy');

    // ── Costing Module (Isolated) ──────────────────────────
    Route::middleware('costing.access')->prefix('costing')->name('costing.')->group(function () {
        Route::get('/', [App\Http\Controllers\Costing\CostingController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Costing\CostingController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Costing\CostingController::class, 'store'])->name('store');
        // Master Data Management (Isolated strictly to Costing - Defined before /{costing})
        Route::prefix('master')->name('master.')->group(function () {
            Route::get('/', [App\Http\Controllers\Costing\MasterDataController::class, 'index'])->name('index');
            Route::post('/sync-odoo', [App\Http\Controllers\Costing\MasterDataController::class, 'syncFromOdoo'])->name('sync-odoo');

            // Brands
            Route::post('/brands', [App\Http\Controllers\Costing\MasterDataController::class, 'storeBrand'])->name('brands.store');
            Route::put('/brands/{brand}', [App\Http\Controllers\Costing\MasterDataController::class, 'updateBrand'])->name('brands.update');
            Route::delete('/brands/{brand}', [App\Http\Controllers\Costing\MasterDataController::class, 'destroyBrand'])->name('brands.destroy');

            // Vehicle Types
            Route::post('/vehicle-types', [App\Http\Controllers\Costing\MasterDataController::class, 'storeVehicleType'])->name('vehicle-types.store');
            Route::put('/vehicle-types/{vehicleType}', [App\Http\Controllers\Costing\MasterDataController::class, 'updateVehicleType'])->name('vehicle-types.update');
            Route::delete('/vehicle-types/{vehicleType}', [App\Http\Controllers\Costing\MasterDataController::class, 'destroyVehicleType'])->name('vehicle-types.destroy');

            // BBN
            Route::post('/bbn', [App\Http\Controllers\Costing\MasterDataController::class, 'storeBbn'])->name('bbn.store');
            Route::put('/bbn/{bbn}', [App\Http\Controllers\Costing\MasterDataController::class, 'updateBbn'])->name('bbn.update');
            Route::delete('/bbn/{bbn}', [App\Http\Controllers\Costing\MasterDataController::class, 'destroyBbn'])->name('bbn.destroy');

            // Dynamic APIs for costing form
            Route::get('/api/brands', [App\Http\Controllers\Costing\MasterDataController::class, 'apiBrands'])->name('api.brands');
            Route::get('/api/vehicle-types', [App\Http\Controllers\Costing\MasterDataController::class, 'apiVehicleTypes'])->name('api.vehicle-types');
            Route::get('/api/bbn', [App\Http\Controllers\Costing\MasterDataController::class, 'apiBbn'])->name('api.bbn');
        });

        // APIs for auto-suggest / dynamic lookups
        Route::get('/api/products', [App\Http\Controllers\Costing\CostingController::class, 'apiProducts'])->name('api.products');
        Route::get('/api/customers', [App\Http\Controllers\Costing\CostingController::class, 'apiSearchCustomers'])->name('api.customers');
        Route::post('/api/refresh-catalog', [App\Http\Controllers\Costing\CostingController::class, 'apiRefreshCatalog'])->name('api.refresh');

        // Parameterized Costing routes (Constrained to numbers so they never conflict with static prefixes)
        Route::get('/{costing}', [App\Http\Controllers\Costing\CostingController::class, 'show'])->name('show')->where('costing', '[0-9]+');
        Route::get('/{costing}/edit', [App\Http\Controllers\Costing\CostingController::class, 'edit'])->name('edit')->where('costing', '[0-9]+');
        Route::put('/{costing}', [App\Http\Controllers\Costing\CostingController::class, 'update'])->name('update')->where('costing', '[0-9]+');
        Route::delete('/{costing}', [App\Http\Controllers\Costing\CostingController::class, 'destroy'])->name('destroy')->where('costing', '[0-9]+');
        Route::get('/{costing}/export', [App\Http\Controllers\Costing\CostingController::class, 'export'])->name('export')->where('costing', '[0-9]+');
    });
});

