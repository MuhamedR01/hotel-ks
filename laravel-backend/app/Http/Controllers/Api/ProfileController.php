<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * GET /api/profile
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name ?? '',
                'phone' => $user->phone ?? '',
                'address' => $user->address ?? '',
                'city' => $user->city ?? '',
                'country' => $user->country ?? '',
            ],
        ]);
    }

    /**
     * POST /api/profile
     */
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ], 400);
        }

        $user = $request->user();

        $user->update([
            'name' => $request->input('name', $user->name),
            'phone' => $request->input('phone', $user->phone),
            'address' => $request->input('address', $user->address),
            'city' => $request->input('city', $user->city),
            'country' => $request->input('country', $user->country),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name ?? '',
                'phone' => $user->phone ?? '',
                'address' => $user->address ?? '',
                'city' => $user->city ?? '',
                'country' => $user->country ?? '',
            ],
        ]);
    }
}
