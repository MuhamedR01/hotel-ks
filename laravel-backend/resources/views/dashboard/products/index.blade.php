@extends('dashboard.layout')

@section('title', 'Produktet')
@section('page-title', 'Produktet')
@section('page-subtitle', 'Menaxho produktet e dyqanit')

@section('topbar-actions')
    <a href="{{ route('dashboard.products.create') }}" class="bg-gray-900 hover:bg-black text-white px-4 sm:px-6 py-2 rounded-lg font-medium transition-colors text-sm sm:text-base">
        <i class="fas fa-plus mr-2"></i><span class="hidden sm:inline">Shto </span>Produkt
    </a>
@endsection

@section('content')
    <!-- Category Pills -->
    @if($categories->isNotEmpty())
    <div class="mb-4">
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin scrollbar-thumb-gray-300">
            <a href="{{ route('dashboard.products.index', array_filter(['search' => $search])) }}"
               class="flex-shrink-0 px-4 py-1.5 rounded-full text-sm font-medium transition-all duration-150
                       {{ empty($category) ? 'bg-gray-800 text-white shadow' : 'bg-white border border-gray-300 text-gray-700 hover:border-gray-500' }}">
                Të gjitha
            </a>
            @foreach($categories as $cat)
            <a href="{{ route('dashboard.products.index', array_filter(['search' => $search, 'category' => $cat])) }}"
               class="flex-shrink-0 px-4 py-1.5 rounded-full text-sm font-medium transition-all duration-150
                       {{ $category === $cat ? 'bg-gray-800 text-white shadow' : 'bg-white border border-gray-300 text-gray-700 hover:border-gray-500' }}">
                {{ $cat }}
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Search -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            @if(!empty($category))
                <input type="hidden" name="category" value="{{ $category }}">
            @endif
            <div class="flex-1 min-w-48">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Kërko</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Kërko produkte..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent text-sm">
            </div>
            <button type="submit" class="bg-gray-900 hover:bg-black text-white px-5 py-2 rounded-lg font-medium transition-colors text-sm">
                <i class="fas fa-search mr-1"></i>Kërko
            </button>
            <a href="{{ route('dashboard.products.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors text-sm">
                <i class="fas fa-redo"></i>
            </a>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Produkti</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kategoria</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Çmimi</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Stoku</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-36">Madhesia</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase w-28">Veprime</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($product->category)
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">{{ $product->category }}</span>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-semibold text-gray-900">{{ number_format($product->price, 2) }}€</span>
                            </td>
                            <td class="px-6 py-4">
                                <form method="POST" action="{{ route('dashboard.products.toggleAvailability', $product->id) }}" class="inline-block">
                                    @csrf
                                    <input type="hidden" name="available" value="{{ $product->available ? '0' : '1' }}">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" {{ $product->available ? 'checked' : '' }} onchange="this.form.submit();">
                                        <div class="w-11 h-6 bg-gray-200 rounded-full peer-focus:ring-2 peer-focus:ring-green-300 peer-checked:bg-green-400 relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                                    </label>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <form method="POST" action="{{ route('dashboard.products.toggleSizes', $product->id) }}" class="inline-block">
                                    @csrf
                                    <input type="hidden" name="has_sizes" value="{{ $product->has_sizes ? '0' : '1' }}">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" {{ $product->has_sizes ? 'checked' : '' }} onchange="this.form.submit();">
                                        <div class="w-11 h-6 bg-gray-200 rounded-full peer-focus:ring-2 peer-focus:ring-indigo-300 peer-checked:bg-indigo-500 relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                                    </label>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-right align-middle">
                                <div class="flex items-center justify-end space-x-3">
                                    <a href="{{ route('dashboard.products.edit', $product->id) }}" class="text-gray-900 hover:text-blue-900 text-sm"><i class="fas fa-edit text-sm"></i></a>
                                    <form method="POST" action="{{ route('dashboard.products.destroy', $product->id) }}" class="inline" onsubmit="return confirm('A jeni të sigurt që doni të fshini këtë produkt?')">
                                        @csrf
                                        <button type="submit" class="text-red-600 hover:text-red-900 text-sm"><i class="fas fa-trash text-sm"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500"><i class="fas fa-box-open text-4xl mb-2"></i><p>Nuk u gjetën produkte.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Grid -->
        <div class="md:hidden">
            @forelse($products as $product)
                <div class="border-b border-gray-200 p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
                        <div class="flex-1 pr-4">
                            <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $product->name }}</h3>
                            @if($product->category)
                                <span class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">{{ $product->category }}</span>
                            @endif
                            <p class="text-sm text-gray-600 mt-1 sm:mt-0">{{ number_format($product->price, 2) }}€</p>
                        </div>
                        <div class="flex items-center space-x-3 mt-3 sm:mt-0 flex-shrink-0">
                            <form method="POST" action="{{ route('dashboard.products.toggleAvailability', $product->id) }}" class="inline-flex items-center">
                                @csrf
                                <input type="hidden" name="available" value="{{ $product->available ? '0' : '1' }}">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" {{ $product->available ? 'checked' : '' }} onchange="this.form.submit();">
                                    <div class="w-9 h-4 bg-gray-200 rounded-full peer-focus:ring-2 peer-focus:ring-green-300 peer-checked:bg-green-400 relative after:content-[''] after:absolute after:top-0.35 after:left-0.35 after:bg-white after:border after:border-gray-300 after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:after:translate-x-full"></div>
                                </label>
                            </form>
                            <div class="flex items-center space-x-2 ml-2">
                                <a href="{{ route('dashboard.products.edit', $product->id) }}" class="text-gray-900 hover:text-blue-900 text-sm"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="{{ route('dashboard.products.destroy', $product->id) }}" class="inline" onsubmit="return confirm('A jeni të sigurt?')">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500"><i class="fas fa-box-open text-4xl mb-2"></i><p>Nuk u gjetën produkte.</p></div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
        <div class="mt-6">{{ $products->appends(request()->query())->links() }}</div>
    @endif
@endsection
