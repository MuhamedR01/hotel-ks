<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardCustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $page = max(1, $request->integer('page', 1));
        $perPage = 10;

        $query = User::select('users.*')
            ->selectRaw('COUNT(DISTINCT orders.id) as total_orders')
            ->selectRaw('COALESCE(SUM(orders.total_amount), 0) as total_spent')
            ->selectRaw('MAX(orders.created_at) as last_order_date')
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->groupBy('users.id');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('users.unique_id', 'like', "%{$search}%")
                  ->orWhere('users.phone', 'like', "%{$search}%");
            });
        }

        $total = $query->getQuery()->getCountForPagination();
        $totalPages = ceil($total / $perPage);

        $customers = $query->orderByDesc('users.created_at')
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->get();

        return view('dashboard.customers', compact('customers', 'search', 'page', 'totalPages', 'total'));
    }
}
