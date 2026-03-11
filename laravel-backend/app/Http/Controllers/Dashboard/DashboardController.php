<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $admin = Auth::guard('admin')->user();
            $role = $this->normalizeRole($admin->role);

            if ($role !== 'super_admin') {
                return redirect($role === 'manager' ? route('dashboard.products') : route('dashboard.orders'));
            }

            $stats = [
                'total_products' => Product::count(),
                'total_orders' => Order::count(),
                'total_customers' => User::where('role', 'customer')->count(),
                'total_revenue' => Order::where('status', 'completed')->sum('total_amount') ?? 0,
                'pending_orders' => Order::where('status', 'pending')->count(),
            ];

            $recent_orders = Order::with('user')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(function ($order) {
                    $order->display_customer_name = $order->user?->name ?? $order->customer_name ?? 'Guest';
                    return $order;
                });

            $top_products = Product::select('products.*', DB::raw('COALESCE(SUM(order_items.quantity), 0) as total_sold'))
                ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
                ->groupBy('products.id')
                ->orderByDesc('total_sold')
                ->limit(5)
                ->get();

            $recent_products = Product::orderByDesc('created_at')->limit(6)->get();

            $html = view('dashboard.index', compact('stats', 'recent_orders', 'top_products', 'recent_products', 'admin'))->render();
            return response($html);
        } catch (\Throwable $e) {
            report($e);
            return response('Dashboard error: ' . $e->getMessage(), 500);
        }
    }

    private function normalizeRole(?string $role): string
    {
        $role = strtolower(trim($role ?? 'admin'));
        $role = str_replace([' ', '-'], '_', $role);
        return match (true) {
            in_array($role, ['admin', 'superadmin', 'super_admin']) => 'super_admin',
            $role === 'manager' => 'manager',
            $role === 'worker' => 'worker',
            default => 'super_admin',
        };
    }
}
