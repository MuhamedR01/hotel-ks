<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class DashboardOrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', '');
        $search = $request->input('search', '');

        $query = Order::with('user')
            ->withCount('items as item_count');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->orderByDesc('created_at')->get();

        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        return view('dashboard.orders', compact('orders', 'stats', 'status', 'search'));
    }

    public function show(int $id)
    {
        $order = Order::with(['user', 'items.product'])->findOrFail($id);

        $statusLabels = [
            'pending' => 'Në Pritje',
            'shipped' => 'Në Postë',
            'completed' => 'E Kompletuar',
            'cancelled' => 'E Anuluar',
            'processing' => 'Duke u Procesuar',
        ];

        return view('dashboard.order_details', compact('order', 'statusLabels'));
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:pending,shipped,completed,cancelled,processing',
        ]);

        Order::where('id', $id)->update(['status' => $request->status]);

        return redirect()->route('dashboard.order_details', $id)
            ->with('success_message', 'Statusi i porosisë u përditësua me sukses!');
    }
}
