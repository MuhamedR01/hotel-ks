@extends('dashboard.layout')

@section('title', 'Porositë')
@section('page-title', 'Porositë')
@section('page-subtitle', 'Menaxho porositë e klientëve')

@section('content')
    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 sm:p-4">
            <div class="flex items-center justify-between">
                <div><p class="text-xs sm:text-sm text-gray-600">Total</p><p class="text-lg sm:text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p></div>
                <div class="bg-blue-100 p-2 sm:p-3 rounded-lg"><i class="fas fa-shopping-cart text-gray-900 text-sm sm:text-lg"></i></div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 sm:p-4">
            <div class="flex items-center justify-between">
                <div><p class="text-xs sm:text-sm text-gray-600">Në pritje</p><p class="text-lg sm:text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p></div>
                <div class="bg-yellow-100 p-2 sm:p-3 rounded-lg"><i class="fas fa-clock text-yellow-600 text-sm sm:text-lg"></i></div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 sm:p-4">
            <div class="flex items-center justify-between">
                <div><p class="text-xs sm:text-sm text-gray-600">Duke procesuar</p><p class="text-lg sm:text-2xl font-bold text-gray-900">{{ $stats['processing'] }}</p></div>
                <div class="bg-blue-100 p-2 sm:p-3 rounded-lg"><i class="fas fa-spinner text-gray-900 text-sm sm:text-lg"></i></div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 sm:p-4">
            <div class="flex items-center justify-between">
                <div><p class="text-xs sm:text-sm text-gray-600">Të përfunduara</p><p class="text-lg sm:text-2xl font-bold text-green-600">{{ $stats['completed'] }}</p></div>
                <div class="bg-green-100 p-2 sm:p-3 rounded-lg"><i class="fas fa-check-circle text-green-600 text-sm sm:text-lg"></i></div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 sm:p-4">
            <div class="flex items-center justify-between">
                <div><p class="text-xs sm:text-sm text-gray-600">Të anuluara</p><p class="text-lg sm:text-2xl font-bold text-red-600">{{ $stats['cancelled'] }}</p></div>
                <div class="bg-red-100 p-2 sm:p-3 rounded-lg"><i class="fas fa-times-circle text-red-600 text-sm sm:text-lg"></i></div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 mb-6">
        <form method="GET" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Kërko</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Kërko porosi..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent text-sm sm:text-base">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Statusi</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent text-sm sm:text-base">
                        <option value="">Të gjitha</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Në pritje</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Duke procesuar</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Të përfunduara</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Të anuluara</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-gray-900 text-white px-4 py-2 rounded-lg hover:bg-black transition-colors text-sm sm:text-base">
                        <i class="fas fa-filter mr-2"></i>Filtro
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Numri</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Artikuj</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Totali</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Statusi</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Data</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Veprime</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $status_classes = ['pending' => 'bg-yellow-100 text-yellow-800', 'processing' => 'bg-blue-100 text-blue-800', 'completed' => 'bg-green-100 text-green-800', 'cancelled' => 'bg-red-100 text-red-800'];
                        $status_labels = ['pending' => 'Në pritje', 'processing' => 'Duke procesuar', 'completed' => 'Të përfunduara', 'cancelled' => 'Të anuluara'];
                    @endphp
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap"><span class="font-semibold text-gray-900">#{{ $order->order_number }}</span></td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap"><span class="text-gray-900">{{ $order->items_count ?? $order->items->count() }} artikuj</span></td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap"><span class="font-bold text-gray-900">{{ number_format($order->total_amount, 2) }}€</span></td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $status_classes[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $status_labels[$order->status] ?? $order->status }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('dashboard.orders.show', $order->id) }}" class="text-gray-900 hover:text-blue-800 font-semibold"><i class="fas fa-eye mr-1"></i>Shiko</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 sm:px-6 py-12 text-center"><div class="text-gray-400"><i class="fas fa-shopping-cart text-4xl mb-3"></i><p class="text-lg">Nuk ka porosi për këtë filtrim</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
