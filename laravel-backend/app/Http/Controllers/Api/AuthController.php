<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * POST /api/login
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Email dhe fjalëkalimi janë të detyrueshëm',
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'error' => 'Email ose fjalëkalimi është i gabuar',
            ], 401);
        }

        // Create Sanctum token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Kyçja u krye me sukses!',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'unique_id' => $user->unique_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'city' => $user->city,
                'country' => $user->country,
            ],
        ]);
    }

    /**
     * POST /api/register
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:50',
        ], [
            'email.required' => 'Email dhe fjalëkalimi janë të detyrueshëm',
            'email.email' => 'Email nuk është i vlefshëm',
            'email.unique' => 'Ky email është tashmë i regjistruar',
            'password.required' => 'Email dhe fjalëkalimi janë të detyrueshëm',
            'password.min' => 'Fjalëkalimi duhet të ketë të paktën 6 karaktere',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $user = User::create([
            'unique_id' => User::generateUniqueId(),
            'name' => $request->name ?? '',
            'email' => $request->email,
            'password' => $request->password, // Auto-hashed by cast
            'phone' => $request->phone ?? '',
            'address' => $request->address ?? '',
            'city' => $request->city ?? '',
            'country' => $request->country ?? '',
            'role' => 'customer',
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Regjistrimi u krye me sukses!',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'unique_id' => $user->unique_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'city' => $user->city,
                'country' => $user->country,
            ],
        ]);
    }

    /**
     * POST /api/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
