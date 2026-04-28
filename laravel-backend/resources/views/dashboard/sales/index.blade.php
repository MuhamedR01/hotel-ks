@extends('dashboard.layout')

@section('title', 'Zbritjet')
@section('page-title', 'Zbritjet')
@section('page-subtitle', 'Menaxho zbritjet (sale) për produktet')

@section('content')
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Filter / search bar --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-semibold text-gray-700 mb-1">Kërko produkt</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Emri i produktit..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-500">
            </div>
            <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
                <input type="checkbox" name="only_on_sale" value="1" {{ $onlyOnSale ? 'checked' : '' }}>
                Vetëm produktet në zbritje
            </label>
            <button type="submit" class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg text-sm font-medium">
                <i class="fas fa-filter mr-1"></i>Filtro
            </button>
            <a href="{{ route('dashboard.sales.index') }}" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm">
                <i class="fas fa-redo"></i>
            </a>
        </form>
    </div>

    {{-- Bulk apply panel --}}
    <form method="POST" action="{{ route('dashboard.sales.bulkApply') }}" id="bulkApplyForm" class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
        @csrf
        <div class="flex flex-wrap items-center gap-3">
            <div class="text-sm text-amber-900 font-semibold flex items-center">
                <i class="fas fa-tags mr-2"></i>Aplikim masiv:
            </div>
            <input type="number" step="0.01" min="0" max="99" name="apply_percent" placeholder="% zbritje"
                class="w-32 px-3 py-2 border border-amber-300 rounded-lg text-sm" required>
            <button type="submit"
                class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                Apliko te të zgjedhurat
            </button>
            <span class="text-xs text-amber-800">Vendos <strong>0</strong> për të hequr zbritjen.</span>
        </div>
    </form>

    {{-- Per-product table with inline editor --}}
    <form method="POST" action="{{ route('dashboard.sales.bulkUpdate') }}" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        @csrf
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left">
                            <input type="checkbox" id="selectAll" class="rounded">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Produkti</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kategoria</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Çmimi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-32">Zbritja %</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Çmimi pas zbritjes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($products as $product)
                        @php
                            $pct = (float) ($product->sale_percent ?? 0);
                            $finalPrice = $pct > 0 ? round((float) $product->price * (1 - $pct / 100), 2) : (float) $product->price;
                        @endphp
                        <tr class="hover:bg-gray-50 {{ $pct > 0 ? 'bg-amber-50/40' : '' }}">
                            <td class="px-4 py-3">
                                <input type="checkbox" form="bulkApplyForm" name="product_ids[]" value="{{ $product->id }}" class="row-check rounded">
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                            <td class="px-4 py-3">
                                @if($product->category)
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">{{ $product->category }}</span>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ number_format($product->price, 2) }}€</td>
                            <td class="px-4 py-3">
                                <div class="relative">
                                    <input type="number" step="0.01" min="0" max="99"
                                        name="sales[{{ $product->id }}]"
                                        value="{{ $pct > 0 ? rtrim(rtrim(number_format($pct, 2, '.', ''), '0'), '.') : '' }}"
                                        placeholder="0"
                                        class="w-24 px-3 py-1.5 pr-7 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-amber-400">
                                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold {{ $pct > 0 ? 'text-amber-700' : 'text-gray-700' }}">
                                @if($pct > 0)
                                    <span class="line-through text-gray-400 mr-1">{{ number_format($product->price, 2) }}€</span>
                                    {{ number_format($finalPrice, 2) }}€
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500"><i class="fas fa-tags text-3xl mb-2"></i><p>Nuk u gjet asnjë produkt.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile fallback --}}
        <div class="md:hidden divide-y divide-gray-200">
            @forelse($products as $product)
                @php $pct = (float) ($product->sale_percent ?? 0); @endphp
                <div class="p-4 {{ $pct > 0 ? 'bg-amber-50/40' : '' }}">
                    <div class="flex items-start gap-2">
                        <input type="checkbox" form="bulkApplyForm" name="product_ids[]" value="{{ $product->id }}" class="row-check rounded mt-1">
                        <div class="flex-1">
                            <div class="font-medium text-gray-900 text-sm">{{ $product->name }}</div>
                            <div class="text-xs text-gray-500">{{ number_format($product->price, 2) }}€</div>
                            <div class="mt-2 flex items-center gap-2">
                                <input type="number" step="0.01" min="0" max="99"
                                    name="sales[{{ $product->id }}]"
                                    value="{{ $pct > 0 ? rtrim(rtrim(number_format($pct, 2, '.', ''), '0'), '.') : '' }}"
                                    placeholder="0"
                                    class="w-20 px-2 py-1 border border-gray-300 rounded text-sm">
                                <span class="text-xs text-gray-500">%</span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-500"><i class="fas fa-tags text-3xl mb-2"></i><p>Nuk u gjet asnjë produkt.</p></div>
            @endforelse
        </div>

        <div class="bg-gray-50 border-t border-gray-200 px-4 py-3 flex items-center justify-between">
            <p class="text-xs text-gray-500">Lëre bosh ose vendos <strong>0</strong> për të hequr zbritjen.</p>
            <button type="submit" class="bg-gray-900 hover:bg-black text-white px-5 py-2 rounded-lg font-medium text-sm">
                <i class="fas fa-save mr-1"></i>Ruaj zbritjet
            </button>
        </div>
    </form>

    @if($products->hasPages())
        <div class="mt-6">{{ $products->appends(request()->query())->links() }}</div>
    @endif

    <script>
        document.getElementById('selectAll')?.addEventListener('change', function (e) {
            document.querySelectorAll('.row-check').forEach(cb => cb.checked = e.target.checked);
        });
    </script>
@endsection
