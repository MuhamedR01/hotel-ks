<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DashboardAuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('dashboard.index');
        }

        return view('dashboard.login', [
            'err' => session('err', ''),
            'success' => session('success', ''),
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = trim($request->username);
        $password = $request->password;

        $admin = Admin::where('username', $username)
            ->orWhere('email', $username)
            ->first();

        if (!$admin || !Hash::check($password, $admin->password)) {
            return redirect()->route('dashboard.login')
                ->with('err', 'Emri i përdoruesit ose fjalëkalimi është i gabuar.');
        }

        try {
            Auth::guard('admin')->login($admin);
            $request->session()->regenerate();

            $admin->update(['last_login' => now()]);

            $landing = match (strtolower($admin->role)) {
                'manager' => route('dashboard.products'),
                'worker' => route('dashboard.orders'),
                default => route('dashboard.index'),
            };

            return redirect($landing);
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('dashboard.login')
                ->withErrors(['login' => 'Login error: ' . $e->getMessage()]);
        }
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('dashboard.login')
            ->with('success', 'Jeni çkyçur me sukses.');
    }
}
