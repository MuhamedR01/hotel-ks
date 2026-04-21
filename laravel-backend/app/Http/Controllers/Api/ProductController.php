<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * GET /api/products/categories — distinct non-null categories
     */
    public function categories(): JsonResponse
    {
        $categories = Product::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return response()->json(['success' => true, 'categories' => $categories]);
    }

    /**
     * GET /api/products  — list all products
     */
    public function index(Request $request): JsonResponse
    {
        $query    = Product::query();
        $limit    = $request->integer('limit');
        $exclude  = $request->integer('exclude');
        $category = $request->input('category', '');

        if ($exclude) {
            $query->where('id', '!=', $exclude);
        }

        if ($category !== '') {
            $query->where('category', $category);
        }

        $query->orderByDesc('created_at');

        if ($limit) {
            $query->limit($limit);
        }

        $products = $query->get()->map(function (Product $product) {
            $imageUrl = $product->getImageDataUrl(1);
            if (!$imageUrl) {
                $svg = '%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22%3E%3Crect fill=%22%23ddd%22 width=%22400%22 height=%22300%22/%3E%3Ctext fill=%22%23999%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3ENo Image%3C/text%3E%3C/svg%3E';
                $imageUrl = 'data:image/svg+xml,' . $svg;
            }

            return [
                'id'          => (int) $product->id,
                'name'        => $product->name,
                'description' => $product->description,
                'price'       => (float) $product->price,
                'available'   => $product->available ? 1 : 0,
                'has_sizes'   => $product->has_sizes ? 1 : 0,
                'category'    => $product->category ?? '',
                'image'       => $imageUrl,
                'created_at'  => $product->created_at?->toDateTimeString(),
            ];
        });

        return response()->json(['success' => true, 'products' => $products]);
    }

    /**
     * GET /api/products/{id}  — single product detail (used by get_product.php)
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'error' => 'Product not found',
            ], 404);
        }

        // Gather all images
        $images = $product->getAllImageUrls();
        if (empty($images)) {
            $images[] = 'https://via.placeholder.com/800x600?text=No+Image';
        }

        $sizes = [];
        if ($product->has_sizes && !empty($product->sizes)) {
            $sizes = is_array($product->sizes) ? $product->sizes : [];
        }

        $productData = [
            'id' => (int) $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => (float) $product->price,
            'available' => (bool) $product->available,
            'category' => $product->category ?? '',
            'is_active' => (bool) $product->is_active,
            'featured' => (bool) $product->featured,
            'rating' => $product->rating ? (float) $product->rating : 4.5,
            'reviews' => (int) $product->reviews,
            'created_at' => $product->created_at?->toDateTimeString(),
            'updated_at' => $product->updated_at?->toDateTimeString(),
            'has_sizes' => (bool) $product->has_sizes,
            'sizes' => $sizes,
            'images' => $images,
            'image' => $images[0],
            'features' => [
                'Cilësi e lartë',
                'Garanci 1 vit',
                'Transport falas për porosi mbi 50€',
            ],
            'specifications' => [
                'Material' => 'Premium',
                'Origjina' => 'Kosovë',
                'Garancia' => '1 vit',
            ],
        ];

        // Related products
        $relatedQuery = Product::where('id', '!=', $id)
            ->where('is_active', true);

        if (!empty($product->category)) {
            $relatedQuery->where('category', $product->category);
        }

        $relatedProducts = $relatedQuery->inRandomOrder()
            ->limit(4)
            ->get()
            ->map(function (Product $related) {
                $relatedImage = $related->getImageDataUrl(1)
                    ?? $related->getImageDataUrl(2)
                    ?? 'https://via.placeholder.com/400x300?text=No+Image';

                return [
                    'id' => (int) $related->id,
                    'name' => $related->name,
                    'price' => (float) $related->price,
                    'category' => $related->category ?? '',
                    'image' => $relatedImage,
                ];
            });

        return response()->json([
            'success' => true,
            'product' => $productData,
            'related_products' => $relatedProducts,
        ]);
    }

    /**
     * POST /api/products — create product (API)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description ?? '',
            'image' => $request->image ?? '',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product added successfully',
            'id' => $product->id,
        ]);
    }

    /**
     * PUT /api/products/{id} — update product (API)
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        $product->update([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description ?? '',
            'image' => $request->image ?? $product->image,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
        ]);
    }

    /**
     * DELETE /api/products/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'error' => 'Product not found',
            ], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }
}
