<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
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

        // Compute subtotal server-side to prevent tampering
        $subtotal = 0;
        foreach ($request->items as $item) {
            $subtotal += floatval($item['price']) * intval($item['quantity']);
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
        $totalAmount = $subtotal + $shippingCost;

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
                'payment_method' => $request->input('payment_method', 'cash'),
                'payment_status' => 'pending',
                'notes' => $request->input('notes', ''),
                'status' => 'processing',
            ]);

            foreach ($request->items as $item) {
                $productPrice = floatval($item['price']);
                $quantity = intval($item['quantity']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'] ?? '',
                    'product_price' => $productPrice,
                    'quantity' => $quantity,
                    'subtotal' => $productPrice * $quantity,
                    'size' => $item['selected_size'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
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
