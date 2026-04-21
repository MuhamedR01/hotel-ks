@php
    $currentRoute = Route::currentRouteName();
    $role = Auth::guard('admin')->user()->role ?? 'super_admin';
@endphp

<aside id="sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full lg:translate-x-0 bg-gradient-to-b from-gray-900 to-gray-800 text-white">
    <div class="h-full px-3 py-4 overflow-y-auto">
        <!-- Logo -->
        <div class="flex items-center justify-between mb-8 px-3">
            <div class="flex items-center space-x-3">
                <div class="bg-blue-600 w-10 h-10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-hotel text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold">minimodaks</h2>
                    <p class="text-xs text-gray-400">Admin Panel</p>
                </div>
            </div>
            <button id="close-sidebar" class="lg:hidden text-gray-400 hover:text-white">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="space-y-2">
            @if($role === 'super_admin')
            <a href="{{ route('dashboard.index') }}" class="sidebar-link {{ str_starts_with($currentRoute, 'dashboard.index') ? 'active' : '' }} flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-home text-lg"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            @endif

            @if(in_array($role, ['super_admin', 'manager']))
            <a href="{{ route('dashboard.products.index') }}" class="sidebar-link {{ str_starts_with($currentRoute, 'dashboard.products') ? 'active' : '' }} flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-box text-lg"></i>
                <span class="font-medium">Produktet</span>
            </a>
            @endif

            <a href="{{ route('dashboard.orders.index') }}" class="sidebar-link {{ str_starts_with($currentRoute, 'dashboard.orders') ? 'active' : '' }} flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-shopping-cart text-lg"></i>
                <span class="font-medium">Porositë</span>
            </a>

            @if($role === 'super_admin')
            <a href="{{ route('dashboard.customers.index') }}" class="sidebar-link {{ str_starts_with($currentRoute, 'dashboard.customers') ? 'active' : '' }} flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-users text-lg"></i>
                <span class="font-medium">Klientët</span>
            </a>

            <a href="{{ route('dashboard.settings.index') }}" class="sidebar-link {{ str_starts_with($currentRoute, 'dashboard.settings') ? 'active' : '' }} flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-cog text-lg"></i>
                <span class="font-medium">Cilësimet</span>
            </a>
            @endif
        </nav>

        <!-- Logout Button -->
        <div class="absolute bottom-4 left-0 right-0 px-6">
            <form method="POST" action="{{ route('dashboard.logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                    <i class="fas fa-sign-out-alt text-lg"></i>
                    <span class="font-medium">Dilni</span>
                </button>
            </form>
        </div>
    </div>
</aside>
