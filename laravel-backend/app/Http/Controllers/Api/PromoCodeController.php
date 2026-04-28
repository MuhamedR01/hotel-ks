<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    /**
     * POST /api/promo-codes/validate
     * Body: { code: "WELCOME10", subtotal: 49.99 }
     *
     * Returns the discount that would be applied. Does NOT increment usage —
     * usage is incremented only when the order is created.
     */
    public function validateCode(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'subtotal' => 'nullable|numeric|min:0',
        ]);

        $code = strtoupper(trim($request->input('code')));
        $subtotal = (float) $request->input('subtotal', 0);

        $promo = PromoCode::where('code', $code)->first();
        if (!$promo) {
            return response()->json([
                'success' => false,
                'message' => 'Kodi promocional nuk u gjet.',
            ], 404);
        }

        if (!$promo->isUsable($subtotal)) {
            return response()->json([
                'success' => false,
                'message' => $promo->reasonNotUsable($subtotal) ?? 'Ky kod nuk mund të përdoret.',
            ], 422);
        }

        // Compute discount preview (NOT applied to DB)
        $discountAmount = 0.0;
        $freeShipping = false;

        if ($promo->discount_type === 'percent') {
            $discountAmount = round($subtotal * ((float) $promo->discount_value / 100), 2);
        } elseif ($promo->discount_type === 'fixed') {
            $discountAmount = min((float) $promo->discount_value, $subtotal);
        } elseif ($promo->discount_type === 'free_shipping') {
            $freeShipping = true;
        }

        return response()->json([
            'success' => true,
            'code' => $promo->code,
            'discount_type' => $promo->discount_type,
            'discount_value' => (float) $promo->discount_value,
            'discount_amount' => $discountAmount,
            'free_shipping' => $freeShipping,
            'description' => $promo->description,
        ]);
    }
}
