<?php

use App\Http\Controllers\Dashboard\DashboardAuthController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\DashboardCustomerController;
use App\Http\Controllers\Dashboard\DashboardOrderController;
use App\Http\Controllers\Dashboard\DashboardProductController;
use App\Http\Controllers\Dashboard\DashboardPromoCodeController;
use App\Http\Controllers\Dashboard\DashboardSaleController;
use App\Http\Controllers\Dashboard\DashboardSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| DASHBOARD_PATH env controls the URL prefix:
|   - "dashboard"  → /dashboard/login  (combined deployment)
|   - ""           → /login             (standalone dashboard.hotel-ks.com)
|
*/

$dashboardPrefix = config('app.dashboard_path', 'dashboard');

// Redirect root to dashboard login (only when dashboard is at a sub-path)
if ($dashboardPrefix !== '') {
    Route::get('/', function () {
        return redirect()->route('dashboard.login');
    });
}

// Dashboard auth routes
Route::prefix($dashboardPrefix)->name('dashboard.')->group(function () {
    Route::get('/login', [DashboardAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [DashboardAuthController::class, 'login'])->middleware('throttle:10,1')->name('login.submit');
    Route::post('/logout', [DashboardAuthController::class, 'logout'])->name('logout');

    // Protected dashboard routes
    Route::middleware('admin.auth')->group(function () {
        // Dashboard home — all roles
        Route::get('/', [DashboardController::class, 'index'])->name('index');

        // Products — super_admin and manager
        Route::middleware('admin.role:super_admin,manager')->group(function () {
            Route::get('/products', [DashboardProductController::class, 'index'])->name('products.index');
            Route::get('/products/add', [DashboardProductController::class, 'create'])->name('products.create');
            Route::post('/products/add', [DashboardProductController::class, 'store'])->name('products.store');
            Route::get('/products/edit/{id}', [DashboardProductController::class, 'edit'])->name('products.edit');
            Route::post('/products/edit/{id}', [DashboardProductController::class, 'update'])->name('products.update');
            Route::post('/products/delete/{id}', [DashboardProductController::class, 'destroy'])->name('products.destroy');
            Route::post('/products/toggle-availability/{id}', [DashboardProductController::class, 'toggleAvailability'])->name('products.toggleAvailability');
            Route::post('/products/toggle-sizes/{id}', [DashboardProductController::class, 'toggleSizes'])->name('products.toggleSizes');

            // Sales / discounts
            Route::get('/sales', [DashboardSaleController::class, 'index'])->name('sales.index');
            Route::post('/sales/bulk-update', [DashboardSaleController::class, 'bulkUpdate'])->name('sales.bulkUpdate');
            Route::post('/sales/bulk-apply', [DashboardSaleController::class, 'bulkApply'])->name('sales.bulkApply');
            Route::post('/sales/{id}', [DashboardSaleController::class, 'updateOne'])->name('sales.updateOne')->where('id', '[0-9]+');
            Route::post('/sales/{id}/remove', [DashboardSaleController::class, 'removeOne'])->name('sales.removeOne')->where('id', '[0-9]+');

            // Promo codes
            Route::get('/promo-codes', [DashboardPromoCodeController::class, 'index'])->name('promo-codes.index');
            Route::get('/promo-codes/create', [DashboardPromoCodeController::class, 'create'])->name('promo-codes.create');
            Route::post('/promo-codes', [DashboardPromoCodeController::class, 'store'])->name('promo-codes.store');
            Route::get('/promo-codes/{id}/edit', [DashboardPromoCodeController::class, 'edit'])->name('promo-codes.edit');
            Route::post('/promo-codes/{id}', [DashboardPromoCodeController::class, 'update'])->name('promo-codes.update');
            Route::post('/promo-codes/{id}/delete', [DashboardPromoCodeController::class, 'destroy'])->name('promo-codes.destroy');
            Route::post('/promo-codes/{id}/toggle', [DashboardPromoCodeController::class, 'toggle'])->name('promo-codes.toggle');
        });

        // Orders — all roles
        Route::get('/orders', [DashboardOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [DashboardOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{id}/status', [DashboardOrderController::class, 'updateStatus'])->name('orders.updateStatus');

        // Customers — super_admin and manager
        Route::middleware('admin.role:super_admin,manager')->group(function () {
            Route::get('/customers', [DashboardCustomerController::class, 'index'])->name('customers.index');
        });

        // Settings — super_admin only
        Route::middleware('admin.role:super_admin')->group(function () {
            Route::get('/settings', [DashboardSettingsController::class, 'index'])->name('settings.index');
            Route::post('/settings/add-admin', [DashboardSettingsController::class, 'addAdmin'])->name('settings.addAdmin');
            Route::post('/settings/delete-admin/{id}', [DashboardSettingsController::class, 'deleteAdmin'])->name('settings.deleteAdmin');
        });
    });
});
