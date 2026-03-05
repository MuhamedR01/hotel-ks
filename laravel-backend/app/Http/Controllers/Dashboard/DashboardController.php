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

        $recentOrders = Order::with('user')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                $order->display_customer_name = $order->user?->name ?? $order->customer_name ?? 'Guest';
                return $order;
            });

        $topProducts = Product::select('products.*', DB::raw('COALESCE(SUM(order_items.quantity), 0) as total_sold'))
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->groupBy('products.id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $recentProducts = Product::orderByDesc('created_at')->limit(6)->get();

        return view('dashboard.index', compact('stats', 'recentOrders', 'topProducts', 'recentProducts', 'admin'));
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
