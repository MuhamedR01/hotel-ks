<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('dashboard.login');
        }

        // Session timeout (30 minutes)
        if (session('admin_last_activity') && (time() - session('admin_last_activity')) > 1800) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            return redirect()->route('dashboard.login')->with('err', 'Sesioni juaj ka skaduar. Ju lutem kyçuni përsëri.');
        }

        session(['admin_last_activity' => time()]);

        return $next($request);
    }
}
