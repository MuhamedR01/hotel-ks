<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class DashboardSaleController extends Controller
{
    /**
     * List all products with their current sale_percent so the admin can
     * mass-edit which products are on sale.
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $onlyOnSale = $request->boolean('only_on_sale');

        $query = Product::query();
        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($onlyOnSale) {
            $query->where('sale_percent', '>', 0);
        }

        $products = $query->orderByDesc('sale_percent')->orderBy('name')->paginate(30);

        return view('dashboard.sales.index', compact('products', 'search', 'onlyOnSale'));
    }

    /**
     * Bulk update sale_percent for multiple products at once.
     * Form sends an associative array `sales[product_id] = percent`.
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'sales'   => 'nullable|array',
            'sales.*' => 'nullable|numeric|min:0|max:99',
        ]);

        $updated = 0;
        foreach ((array) $request->input('sales', []) as $productId => $percent) {
            $product = Product::find($productId);
            if (!$product) continue;
            $pct = $percent === '' || $percent === null ? null : (float) $percent;
            if ($pct !== null && $pct <= 0) $pct = null;
            $product->update(['sale_percent' => $pct]);
            $updated++;
        }

        return back()->with('success', "Zbritjet u përditësuan për {$updated} produkte.");
    }

    /**
     * Apply the same sale_percent to every selected product (checkboxes).
     */
    public function bulkApply(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'integer',
            'apply_percent' => 'required|numeric|min:0|max:99',
        ]);

        $pct = (float) $request->input('apply_percent');
        $value = $pct > 0 ? $pct : null;

        Product::whereIn('id', $request->input('product_ids'))
            ->update(['sale_percent' => $value]);

        $msg = $value
            ? "Zbritja prej {$pct}% u aplikua te produktet e zgjedhura."
            : "Zbritjet u hoqën nga produktet e zgjedhura.";

        return back()->with('success', $msg);
    }
}
