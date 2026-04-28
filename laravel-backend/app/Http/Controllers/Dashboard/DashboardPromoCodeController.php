<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DashboardPromoCodeController extends Controller
{
    public function index()
    {
        $codes = PromoCode::orderByDesc('created_at')->paginate(20);
        return view('dashboard.promo_codes.index', compact('codes'));
    }

    public function create()
    {
        return view('dashboard.promo_codes.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        PromoCode::create($data);
        return redirect()->route('dashboard.promo-codes.index')->with('success', 'Kodi u krijua me sukses.');
    }

    public function edit(int $id)
    {
        $code = PromoCode::findOrFail($id);
        return view('dashboard.promo_codes.edit', compact('code'));
    }

    public function update(Request $request, int $id)
    {
        $code = PromoCode::findOrFail($id);
        $data = $this->validateData($request, $id);
        $code->update($data);
        return redirect()->route('dashboard.promo-codes.index')->with('success', 'Kodi u përditësua me sukses.');
    }

    public function destroy(int $id)
    {
        PromoCode::findOrFail($id)->delete();
        return redirect()->route('dashboard.promo-codes.index')->with('success', 'Kodi u fshi.');
    }

    public function toggle(int $id)
    {
        $code = PromoCode::findOrFail($id);
        $code->update(['is_active' => !$code->is_active]);
        return back()->with('success', 'Statusi u ndryshua.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('promo_codes', 'code')->ignore($ignoreId)],
            'discount_type' => 'required|in:percent,fixed,free_shipping',
            'discount_value' => 'nullable|numeric|min:0|max:100000',
            'min_subtotal' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'max_uses' => 'nullable|integer|min:1',
            'is_active' => 'nullable|in:0,1',
            'description' => 'nullable|string|max:255',
        ]);

        $type = $request->input('discount_type');
        $value = (float) $request->input('discount_value', 0);
        if ($type === 'percent') {
            $value = max(0, min(100, $value));
        } elseif ($type === 'free_shipping') {
            $value = 0;
        }

        return [
            'code'           => strtoupper(trim($request->input('code'))),
            'discount_type'  => $type,
            'discount_value' => $value,
            'min_subtotal'   => $request->filled('min_subtotal') ? (float) $request->input('min_subtotal') : null,
            'starts_at'      => $request->filled('starts_at') ? $request->input('starts_at') : null,
            'expires_at'     => $request->filled('expires_at') ? $request->input('expires_at') : null,
            'max_uses'       => $request->filled('max_uses') ? (int) $request->input('max_uses') : null,
            'is_active'      => $request->boolean('is_active', true),
            'description'    => $request->input('description') ?: null,
        ];
    }
}
