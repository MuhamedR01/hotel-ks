<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class DashboardProductController extends Controller
{
    public function index(Request $request)
    {
        $search   = $request->input('search', '');
        $category = $request->input('category', '');
        $perPage  = 12;

        $query = Product::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($category)) {
            $query->where('category', $category);
        }

        $products   = $query->orderByDesc('created_at')->paginate($perPage);
        $categories = Product::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('dashboard.products.index', compact('products', 'search', 'category', 'categories'));
    }

    public function create()
    {
        $categories = Product::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('dashboard.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0.01',
            'sale_percent' => 'nullable|numeric|min:0|max:99',
            'description' => 'nullable|string',
            'available' => 'required|in:0,1',
            'images' => 'required|array|max:5',
            'images.*' => 'image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        $salePct = $request->filled('sale_percent') ? (float) $request->input('sale_percent') : 0;
        $data = [
            'name'        => $request->name,
            'price'       => $request->price,
            'sale_percent' => $salePct > 0 ? $salePct : null,
            'description' => $request->description ?? '',
            'available'   => (bool) $request->available,
            'category'    => $request->input('category', '') ?: null,
            'variant_label' => $request->filled('variant_label')
                ? trim($request->input('variant_label'))
                : null,
            'admin_note'  => $request->filled('admin_note')
                ? trim($request->input('admin_note'))
                : null,
        ];

        if ($request->hasFile('images')) {
            $files = $request->file('images');
            foreach ($files as $i => $file) {
                $fieldPrefix = $i === 0 ? 'image' : "image_" . ($i + 1);
                $data[$fieldPrefix] = file_get_contents($file->getRealPath());
                $data[$fieldPrefix . '_name'] = $file->getClientOriginalName();
                $data[$fieldPrefix . '_size'] = $file->getSize();
                $data[$fieldPrefix . '_type'] = $file->getMimeType();
            }
        }

        $product = Product::create($data);

        // Persist sizes/variant values if provided
        if ($request->filled('sizes')) {
            $sizes = array_values(array_filter(array_map('trim', explode(',', $request->sizes))));
            if (!empty($sizes)) {
                $product->update([
                    'sizes' => $sizes,
                    'has_sizes' => true,
                ]);
            }
        }

        return redirect()->route('dashboard.products.index')->with('success', 'added');
    }

    public function edit(int $id)
    {
        $product    = Product::findOrFail($id);
        $categories = Product::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('dashboard.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, int $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0.01',
            'sale_percent' => 'nullable|numeric|min:0|max:99',
            'description' => 'nullable|string',
            'available' => 'required|in:0,1',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        $salePct = $request->filled('sale_percent') ? (float) $request->input('sale_percent') : 0;
        $data = [
            'name'        => $request->name,
            'price'       => $request->price,
            'sale_percent' => $salePct > 0 ? $salePct : null,
            'description' => $request->description ?? '',
            'available'   => (bool) $request->available,
            'category'    => $request->input('category', '') ?: null,
            'variant_label' => $request->filled('variant_label')
                ? trim($request->input('variant_label'))
                : null,
            'admin_note'  => $request->filled('admin_note')
                ? trim($request->input('admin_note'))
                : null,
        ];

        if ($request->hasFile('images')) {
            $files = $request->file('images');
            foreach ($files as $i => $file) {
                $fieldPrefix = $i === 0 ? 'image' : "image_" . ($i + 1);
                $data[$fieldPrefix] = file_get_contents($file->getRealPath());
                $data[$fieldPrefix . '_name'] = $file->getClientOriginalName();
                $data[$fieldPrefix . '_size'] = $file->getSize();
                $data[$fieldPrefix . '_type'] = $file->getMimeType();
            }
        }

        $product->update($data);

        // Update sizes if provided
        if ($request->filled('sizes')) {
            $sizes = array_values(array_filter(array_map('trim', explode(',', $request->sizes))));
            $product->update([
                'sizes' => $sizes,
                'has_sizes' => !empty($sizes),
            ]);
        }

        return redirect()->route('dashboard.products.index')->with('success', 'updated');
    }

    public function destroy(int $id)
    {
        Product::findOrFail($id)->delete();
        return redirect()->route('dashboard.products.index')->with('success', 'deleted');
    }

    public function toggleAvailability(Request $request, int $id)
    {
        $request->validate([
            'available' => 'required|in:0,1',
        ]);

        Product::where('id', $id)
            ->update(['available' => (bool) $request->available]);

        return back()->with('success_message',
            $request->available ? 'Produkti u vendos në stok.' : 'Produkti u hoq nga stoku.'
        );
    }

    public function toggleSizes(Request $request, int $id)
    {
        $request->validate([
            'has_sizes' => 'required|in:0,1',
        ]);

        $data = ['has_sizes' => (bool) $request->has_sizes];

        // When enabling sizes, populate default sizes if empty
        if ($request->has_sizes) {
            $product = Product::findOrFail($id);
            if (empty($product->sizes)) {
                $data['sizes'] = ['S', 'M', 'L', 'XL'];
            }
        }

        Product::where('id', $id)->update($data);

        return back()->with('success_message',
            $request->has_sizes ? 'Madhësitë u aktivizuan.' : 'Madhësitë u çaktivizuan.'
        );
    }
}
