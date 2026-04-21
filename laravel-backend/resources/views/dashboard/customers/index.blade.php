@extends('dashboard.layout')

@section('title', 'Klientët')
@section('page-title', 'Klientët')
@section('page-subtitle', 'Menaxhoni dhe shikoni informacionin e klientëve')

@section('content')
    <!-- Search -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Kërko sipas emrit, email, ID ose telefoni..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>
            <button type="submit" class="px-6 py-2 bg-gray-900 text-white rounded-lg hover:bg-black transition-colors">
                <i class="fas fa-search mr-2"></i>Kërko
            </button>
            @if(request('search'))
                <a href="{{ route('dashboard.customers.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-center">
                    <i class="fas fa-times mr-2"></i>Pastro
                </a>
            @endif
        </form>
    </div>

    <!-- Customers Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Klienti</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontakti</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokacioni</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Regjistruar</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <span class="text-gray-900 font-semibold text-lg">{{ strtoupper(substr($customer->name ?? 'U', 0, 1)) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $customer->name ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500 font-mono">ID: {{ $customer->unique_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900"><i class="fas fa-envelope text-gray-400 mr-2"></i>{{ $customer->email }}</div>
                                @if($customer->phone)
                                    <div class="text-sm text-gray-500 mt-1"><i class="fas fa-phone text-gray-400 mr-2"></i>{{ $customer->phone }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $customer->address ?? '' }}</div>
                                <div class="text-sm text-gray-500">{{ $customer->city ?? 'N/A' }}@if($customer->country), {{ $customer->country }}@endif</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $customer->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500"><i class="fas fa-users text-4xl mb-2"></i><p>Nuk u gjetën klientë</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($customers->hasPages())
            <div class="bg-white px-6 py-4 border-t border-gray-200">
                {{ $customers->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
@endsection
