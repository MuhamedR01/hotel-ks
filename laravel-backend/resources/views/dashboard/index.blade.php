@extends('dashboard.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Mirë se vini përsëri, ' . (Auth::guard('admin')->user()->name ?? 'Admin') . '!')

@section('topbar-actions')
    <button class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
        <i class="fas fa-bell text-lg sm:text-xl"></i>
        @if($stats['pending_orders'] > 0)
            <span class="absolute top-0 right-0 w-4 h-4 sm:w-5 sm:h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                {{ $stats['pending_orders'] }}
            </span>
        @endif
    </button>
@endsection

@section('content')
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="stat-card bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div>
                    <p class="text-blue-100 text-xs sm:text-sm font-medium">Produktet</p>
                    <h3 class="text-2xl sm:text-3xl font-bold mt-1">{{ number_format($stats['total_products']) }}</h3>
                </div>
                <div class="bg-white bg-opacity-20 p-3 sm:p-4 rounded-lg"><i class="fas fa-box text-xl sm:text-2xl"></i></div>
            </div>
            <div class="flex items-center text-xs sm:text-sm text-blue-100"><i class="fas fa-arrow-up mr-1"></i><span>Total në inventar</span></div>
        </div>

        <div class="stat-card bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div>
                    <p class="text-green-100 text-xs sm:text-sm font-medium">Porositë</p>
                    <h3 class="text-2xl sm:text-3xl font-bold mt-1">{{ number_format($stats['total_orders']) }}</h3>
                </div>
                <div class="bg-white bg-opacity-20 p-3 sm:p-4 rounded-lg"><i class="fas fa-shopping-cart text-xl sm:text-2xl"></i></div>
            </div>
            <div class="flex items-center text-xs sm:text-sm text-green-100"><i class="fas fa-clock mr-1"></i><span>{{ $stats['pending_orders'] }} në pritje</span></div>
        </div>

        <div class="stat-card bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div>
                    <p class="text-purple-100 text-xs sm:text-sm font-medium">Klientët</p>
                    <h3 class="text-2xl sm:text-3xl font-bold mt-1">{{ number_format($stats['total_customers']) }}</h3>
                </div>
                <div class="bg-white bg-opacity-20 p-3 sm:p-4 rounded-lg"><i class="fas fa-users text-xl sm:text-2xl"></i></div>
            </div>
            <div class="flex items-center text-xs sm:text-sm text-purple-100"><i class="fas fa-user-plus mr-1"></i><span>Total të regjistruar</span></div>
        </div>

        <div class="stat-card bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-4 sm:p-6 text-white">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div>
                    <p class="text-orange-100 text-xs sm:text-sm font-medium">Të Ardhurat</p>
                    <h3 class="text-2xl sm:text-3xl font-bold mt-1">{{ number_format($stats['total_revenue'], 2) }}€</h3>
                </div>
                <div class="bg-white bg-opacity-20 p-3 sm:p-4 rounded-lg"><i class="fas fa-euro-sign text-xl sm:text-2xl"></i></div>
            </div>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Recent Orders -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-800"><i class="fas fa-shopping-bag mr-2 text-blue-600"></i>Porositë e Fundit</h2>
                    <a href="{{ route('dashboard.orders.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Shiko të gjitha <i class="fas fa-arrow-right ml-1"></i></a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Porosia</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Klienti</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Shuma</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Statusi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($recent_orders as $order)
                            @php
                                $status_colors = ['pending' => 'bg-yellow-100 text-yellow-800', 'processing' => 'bg-blue-100 text-blue-800', 'completed' => 'bg-green-100 text-green-800', 'cancelled' => 'bg-red-100 text-red-800'];
                                $status_labels = ['pending' => 'Në pritje', 'processing' => 'Duke u procesuar', 'completed' => 'E përfunduar', 'cancelled' => 'E anuluar'];
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">#{{ $order->order_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $order->created_at->format('d M Y') }}</div>
                                </td>
                                <td class="px-6 py-4"><div class="text-sm text-gray-900">{{ $order->customer_name ?? $order->user->name ?? 'Guest' }}</div></td>
                                <td class="px-6 py-4"><span class="text-sm font-semibold text-gray-900">{{ number_format($order->total_amount, 2) }}€</span></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $status_colors[$order->status] ?? '' }}">
                                        {{ $status_labels[$order->status] ?? $order->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500"><i class="fas fa-inbox text-4xl mb-2"></i><p>Nuk ka porosi të fundit</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Products -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-800"><i class="fas fa-trophy mr-2 text-yellow-600"></i>Produktet Më të Shpjetura</h2>
            </div>
            <div class="p-6">
                @forelse($top_products as $product)
                    <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
                        <div class="flex items-center space-x-3">
                            <img src="{{ $product->getImageDataUrl(1) }}" alt="{{ $product->name }}" class="w-12 h-12 product-image rounded-lg"
                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%23ddd%22 width=%22200%22 height=%22200%22/%3E%3Ctext fill=%22%23999%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3ENo Image%3C/text%3E%3C/svg%3E'">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                                <p class="text-xs text-gray-500">Shpenzuar: {{ $product->total_sold ?? 0 }} copë</p>
                            </div>
                        </div>
                        <span class="text-sm font-semibold text-blue-600">{{ number_format($product->price, 2) }}€</span>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500"><i class="fas fa-box-open text-4xl mb-2"></i><p>Nuk ka të dhëna</p></div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Products -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-800"><i class="fas fa-box-open mr-2 text-purple-600"></i>Produktet e Fundit</h2>
                <a href="{{ route('dashboard.products.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Shiko të gjitha <i class="fas fa-arrow-right ml-1"></i></a>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($recent_products as $product)
                    <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <img src="{{ $product->getImageDataUrl(1) }}" alt="{{ $product->name }}" class="w-full h-40 product-image rounded-lg mb-3"
                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%23ddd%22 width=%22200%22 height=%22200%22/%3E%3Ctext fill=%22%23999%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3ENo Image%3C/text%3E%3C/svg%3E'">
                        <h3 class="font-semibold text-gray-900 mb-1">{{ $product->name }}</h3>
                        <p class="text-sm text-gray-600 mb-2 line-clamp-2">{{ Str::limit($product->description, 80) }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-bold text-blue-600">{{ number_format($product->price, 2) }}€</span>
                            <div class="flex space-x-2">
                                <a href="{{ route('dashboard.products.edit', $product->id) }}" class="text-blue-600 hover:text-blue-700"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="{{ route('dashboard.products.destroy', $product->id) }}" class="inline" onsubmit="return confirm('Jeni të sigurt që dëshironi të fshini këtë produkt?')">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-700"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-12 text-gray-500">
                        <i class="fas fa-box text-5xl mb-3"></i>
                        <p>Nuk ka produkte të shtuar ende</p>
                        <a href="{{ route('dashboard.products.create') }}" class="inline-block mt-4 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Shto Produkt</a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
