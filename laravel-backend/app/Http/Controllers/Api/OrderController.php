<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PromoCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * POST /api/orders — create order (mirrors create_order.php)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string|max:255',
            'customer_city' => 'required|string|max:100',
            'customer_country' => 'required|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|numeric',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ], 400);
        }

        // Compute subtotal server-side from current product prices (sale-aware)
        // — never trust client-supplied prices.
        $subtotal = 0;
        $serverItems = [];
        foreach ($request->items as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $quantity  = max(1, intval($item['quantity'] ?? 1));
            $product   = $productId ? Product::find($productId) : null;

            // Use current sale price if a sale is active, else regular price.
            // Fall back to client price as last resort (e.g. deleted product).
            $unitPrice = $product
                ? (float) $product->sale_price
                : (float) ($item['price'] ?? 0);

            $subtotal += $unitPrice * $quantity;
            $serverItems[] = [
                'product'   => $product,
                'product_id' => $productId,
                'product_name' => $item['product_name'] ?? ($product->name ?? ''),
                'quantity'  => $quantity,
                'unit_price' => $unitPrice,
                'selected_size' => $item['selected_size'] ?? null,
            ];
        }

        // Determine shipping by country
        $rawCountry = $request->input('customer_country', '');
        $normalizedCountry = $this->normalizeCountryForShipping($rawCountry);

        if (str_contains($normalizedCountry, 'kosov')) {
            $shippingCost = 2.0;
        } elseif (str_contains($normalizedCountry, 'alban') || str_contains($normalizedCountry, 'north macedonia') || str_contains($normalizedCountry, 'maced') || str_contains($normalizedCountry, 'maqed')) {
            $shippingCost = 5.0;
        } else {
            $shippingCost = 5.0;
        }

        $tax = 0.0;

        // ---- Apply promo code (server-side validation) ----
        $rawPromoInput = trim((string) $request->input('promo_code', ''));
        $promoCodeStored = null;
        $discountAmount = 0.0;
        $promoModel = null;
        if ($rawPromoInput !== '') {
            $promoModel = PromoCode::where('code', strtoupper($rawPromoInput))->first();
            if ($promoModel && $promoModel->isUsable($subtotal)) {
                if ($promoModel->discount_type === 'percent') {
                    $discountAmount = round($subtotal * ((float) $promoModel->discount_value / 100), 2);
                } elseif ($promoModel->discount_type === 'fixed') {
                    $discountAmount = min((float) $promoModel->discount_value, $subtotal);
                } elseif ($promoModel->discount_type === 'free_shipping') {
                    $shippingCost = 0.0;
                }
                $promoCodeStored = $promoModel->code;
            }
            // Silently ignore invalid promo codes — frontend already validated.
        }

        $totalAmount = max(0, $subtotal + $shippingCost - $discountAmount);

        try {
            DB::beginTransaction();

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $request->user()?->id,
                'customer_name' => $request->customer_name,
                'customer_email' => $request->input('customer_email', ''),
                'customer_phone' => $request->customer_phone,
                'customer_address' => $request->customer_address,
                'customer_city' => $request->customer_city,
                'customer_country' => $request->customer_country,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'tax' => $tax,
                'total_amount' => $totalAmount,
                'promo_code' => $promoCodeStored,
                'discount_amount' => $discountAmount,
                'payment_method' => $request->input('payment_method', 'cash'),
                'payment_status' => 'pending',
                'notes' => $request->input('notes', ''),
                'status' => 'processing',
            ]);

            foreach ($serverItems as $line) {
                $product = $line['product'];

                // Snapshot the admin_note from the product so it remains
                // visible on the order even if the product is later edited.
                $adminNote = $product && !empty($product->admin_note)
                    ? $product->admin_note
                    : null;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product_id'],
                    'product_name' => $line['product_name'],
                    'product_price' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                    'subtotal' => $line['unit_price'] * $line['quantity'],
                    'size' => $line['selected_size'],
                    'admin_note' => $adminNote,
                ]);
            }

            // Increment usage counter only after the order is committed below.
            if ($promoModel) {
                $promoModel->increment('times_used');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount_amount' => $discountAmount,
                'promo_code' => $promoCodeStored,
                'total_amount' => $totalAmount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    private function normalizeCountryForShipping(string $str): string
    {
        $s = trim($str);
        if ($s === '') return '';
        $s = mb_strtolower($s, 'UTF-8');
        $trans = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        if ($trans !== false && $trans !== null) {
            $s = $trans;
        }
        $s = preg_replace('/[^a-z0-9\s]/', ' ', $s);
        $s = preg_replace('/\s+/', ' ', $s);
        return trim($s);
    }
}
