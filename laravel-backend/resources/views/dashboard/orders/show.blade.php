@extends('dashboard.layout')

@section('title', 'Porosia #' . $order->order_number)
@section('page-title', 'Porosia #' . $order->order_number)
@section('page-subtitle', 'Detajet e porosisë')

@section('topbar-actions')
    <div class="flex items-center space-x-2">
        <a href="{{ route('dashboard.orders.index') }}" class="sm:hidden text-gray-600 hover:text-gray-900 p-2"><i class="fas fa-arrow-left text-lg"></i></a>
        <button onclick="window.print()" class="bg-gray-900 text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-black transition-colors text-sm">
            <i class="fas fa-print mr-0 sm:mr-2"></i><span class="hidden sm:inline">Printo</span>
        </button>
    </div>
@endsection

@php
    $status_labels = ['pending' => 'Në Pritje', 'shipped' => 'Në Postë', 'processing' => 'Duke u Procesuar', 'completed' => 'E Kompletuar', 'cancelled' => 'E Anuluar'];
    $status_colors = ['pending' => 'bg-yellow-100 text-yellow-800', 'shipped' => 'bg-blue-100 text-blue-800', 'processing' => 'bg-blue-100 text-blue-800', 'completed' => 'bg-green-100 text-green-800', 'cancelled' => 'bg-red-100 text-red-800'];
    $status_icons = ['pending' => 'clock', 'shipped' => 'truck', 'processing' => 'spinner', 'completed' => 'check-circle', 'cancelled' => 'times-circle'];
@endphp

@section('content')
    <!-- Order Summary -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Përmbledhje e Porosisë</h2>
                <p class="text-sm text-gray-600">Detajet e porosisë dhe statusi</p>
            </div>
            <div class="mt-4 md:mt-0">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $status_colors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                    <i class="fas fa-{{ $status_icons[$order->status] ?? 'info-circle' }} mr-2"></i>
                    {{ $status_labels[$order->status] ?? 'Panjohur' }}
                </span>
            </div>
        </div>

        <!-- Status Update Form -->
        <form method="POST" action="{{ route('dashboard.orders.updateStatus', $order->id) }}" class="mb-6">
            @csrf
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <select name="status" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-blue-500 text-sm">
                    <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Duke u Procesuar</option>
                    <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Në Postë</option>
                    <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>E Kompletuar</option>
                    <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>E Anuluar</option>
                </select>
                <button type="submit" class="px-6 py-2 bg-gray-900 text-white rounded-lg hover:bg-black transition-colors text-sm">Përditëso Statusin</button>
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-medium text-gray-800 mb-3 flex items-center"><i class="fas fa-user mr-2 text-gray-900"></i>Informacioni i Klientit</h3>
                <div class="space-y-2 text-sm">
                    <p class="flex items-start"><span class="font-medium w-24">Emri:</span><span class="text-gray-700">{{ $order->customer_name ?? 'N/A' }}</span></p>
                    <p class="flex items-start"><span class="font-medium w-24">Email:</span><span class="text-gray-700 break-all">{{ $order->customer_email ?? 'N/A' }}</span></p>
                    <p class="flex items-start"><span class="font-medium w-24">Telefoni:</span><span class="text-gray-700">{{ $order->customer_phone ?? 'N/A' }}</span></p>
                </div>
            </div>
            <div>
                <h3 class="font-medium text-gray-800 mb-3 flex items-center"><i class="fas fa-map-marker-alt mr-2 text-gray-900"></i>Adresa e Dërgimit</h3>
                <div class="space-y-2 text-sm">
                    <p class="flex items-start"><span class="font-medium w-24">Adresa:</span><span class="text-gray-700">{{ $order->customer_address ?? 'N/A' }}</span></p>
                    <p class="flex items-start"><span class="font-medium w-24">Qyteti:</span><span class="text-gray-700">{{ $order->customer_city ?? 'N/A' }}</span></p>
                    <p class="flex items-start"><span class="font-medium w-24">Shteti:</span><span class="text-gray-700">{{ $order->customer_country ?? 'N/A' }}</span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-6">
        <h3 class="font-medium text-gray-800 mb-4 flex items-center"><i class="fas fa-box-open mr-2 text-gray-900"></i>Artikujt e Porosisë</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produkt</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Madhësia</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cmimi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sasia</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shuma</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($order->items as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($item->product && $item->product->image)
                                        <img class="h-10 w-10 rounded-md object-cover mr-3" src="{{ $item->product->getImageDataUrl(1) }}" alt="{{ $item->product_name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-md bg-gray-200 flex items-center justify-center mr-3"><i class="fas fa-image text-gray-500"></i></div>
                                    @endif
                                    <div class="text-sm font-medium text-gray-900">{{ $item->product_name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item->size ?: 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($item->product_price, 2, ',', '.') }}€</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ number_format($item->product_price * $item->quantity, 2, ',', '.') }}€</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Order Totals -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
        <h3 class="font-medium text-gray-800 mb-4 flex items-center"><i class="fas fa-calculator mr-2 text-gray-900"></i>Totali i Porosisë</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-md">
            <div class="flex justify-between"><span class="text-gray-600">Nën Total:</span><span class="font-medium">{{ number_format($order->subtotal, 2, ',', '.') }}€</span></div>
            <div class="flex justify-between"><span class="text-gray-600">Shuma e Transportit:</span><span class="font-medium">{{ number_format($order->shipping_cost, 2, ',', '.') }}€</span></div>
            <div class="flex justify-between"><span class="text-gray-600">Totali:</span><span class="font-bold text-lg">{{ number_format($order->total_amount, 2, ',', '.') }}€</span></div>
        </div>
    </div>
@endsection
