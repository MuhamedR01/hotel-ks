<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DashboardSettingsController extends Controller
{
    public function index()
    {
        $admins = Admin::orderByDesc('created_at')->get();

        $roleLabels = [
            'super_admin' => 'Super Admin',
            'manager' => 'Menaxher',
            'worker' => 'Punëtor',
        ];

        $roleColors = [
            'super_admin' => 'bg-purple-100 text-purple-800',
            'manager' => 'bg-blue-100 text-blue-800',
            'worker' => 'bg-green-100 text-green-800',
        ];

        return view('dashboard.settings', compact('admins', 'roleLabels', 'roleColors'));
    }

    public function addAdmin(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:admins,username',
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'password' => 'required|string|min:6',
            'role' => 'required|in:super_admin,manager,worker',
        ]);

        // Generate email if not provided
        $email = $request->email;
        if (empty($email)) {
            $base = preg_replace('/[^a-z0-9_.-]/', '', strtolower($request->username));
            $email = $base . '@hotelks.com';
            $i = 0;
            while (Admin::where('email', $email)->exists()) {
                $i++;
                $email = $base . '+' . $i . '@hotelks.com';
            }
        } else {
            if (Admin::where('email', $email)->exists()) {
                return back()->with('error_message', 'Ky email është i regjistruar tashmë!');
            }
        }

        Admin::create([
            'username' => $request->username,
            'name' => $request->name,
            'email' => $email,
            'password' => $request->password,
            'role' => $request->role,
        ]);

        return back()->with('success_message', 'Admini u shtua me sukses!');
    }

    public function deleteAdmin(int $id)
    {
        $currentAdmin = Auth::guard('admin')->user();

        if ($currentAdmin->id === $id) {
            return back()->with('error_message', 'Nuk mund të fshini llogarinë tuaj!');
        }

        Admin::where('id', $id)->delete();

        return back()->with('success_message', 'Admini u fshi me sukses!');
    }
}
