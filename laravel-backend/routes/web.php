<?php

use App\Http\Controllers\Dashboard\DashboardAuthController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\DashboardCustomerController;
use App\Http\Controllers\Dashboard\DashboardOrderController;
use App\Http\Controllers\Dashboard\DashboardProductController;
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
    Route::post('/login', [DashboardAuthController::class, 'login'])->name('login.submit');
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
