<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return redirect()->route('dashboard.login');
        }

        $adminRole = $this->normalizeRole($admin->role);

        if (!in_array($adminRole, $roles)) {
            $landing = match ($adminRole) {
                'manager' => route('dashboard.products'),
                'worker' => route('dashboard.orders'),
                default => route('dashboard.index'),
            };
            return redirect($landing);
        }

        return $next($request);
    }

    private function normalizeRole(?string $role): string
    {
        $role = strtolower(trim($role ?? 'admin'));
        $role = str_replace([' ', '-'], '_', $role);
        return match (true) {
            in_array($role, ['admin', 'superadmin', 'super_admin']) => 'super_admin',
            $role === 'manager' => 'manager',
            $role === 'worker' => 'worker',
            default => 'super_admin',
        };
    }
}
