@extends('dashboard.layout')

@section('title', 'Ndrysho Produktin')
@section('page-title', 'Ndrysho Produktin')
@section('page-subtitle', 'Përditëso informacionet e produktit')

@section('topbar-actions')
    <a href="{{ route('dashboard.products.index') }}" class="text-gray-600 hover:text-gray-900 px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors">
        <i class="fas fa-arrow-left mr-2"></i><span class="hidden sm:inline">Kthehu</span>
    </a>
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 lg:p-8">
        <form method="POST" action="{{ route('dashboard.products.update', $product->id) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Emri i Produktit *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500" required>
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Çmimi *</label>
                    <input type="number" step="0.01" id="price" name="price" value="{{ old('price', $product->price) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500" required>
                </div>

                <div>
                    <label for="available" class="block text-sm font-medium text-gray-700 mb-1">Stoku</label>
                    <select id="available" name="available" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500">
                        <option value="1" {{ old('available', $product->available) ? 'selected' : '' }}>Ne stok</option>
                        <option value="0" {{ !old('available', $product->available) ? 'selected' : '' }}>Pa stok</option>
                    </select>
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Kategoria</label>
                    <input type="text" id="category" name="category" value="{{ old('category', $product->category) }}"
                        list="categories-list"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500"
                        placeholder="p.sh. Ushqim, Pije, Aksesore..."
                        autocomplete="off">
                    <datalist id="categories-list">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">
                        @endforeach
                    </datalist>
                    <p class="text-xs text-gray-500 mt-1">Zgjidhni nga lista ose shkruani kategorinë e re.</p>
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Përshkrimi</label>
                    <textarea id="description" name="description" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500">{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label for="variant_label" class="block text-sm font-medium text-gray-700 mb-1">Etiketa e variantit (opsionale)</label>
                    <input type="text" id="variant_label" name="variant_label" value="{{ old('variant_label', $product->variant_label) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500"
                        placeholder="p.sh. Madhësia, Marka, Ngjyra">
                    <p class="text-xs text-gray-500 mt-1">Si do të quhet zgjedhja në faqen e produktit dhe në shportë. Lëre bosh për “<strong>Madhësia</strong>”.</p>
                </div>

                <div>
                    <label for="sale_percent" class="block text-sm font-medium text-amber-700 mb-1">
                        <i class="fas fa-percent mr-1"></i> Zbritja (%)
                    </label>
                    <input type="number" step="0.01" min="0" max="99" id="sale_percent" name="sale_percent"
                        value="{{ old('sale_percent', $product->sale_percent) }}"
                        class="w-full px-3 py-2 border border-amber-300 bg-amber-50 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-400"
                        placeholder="0">
                    <p class="text-xs text-gray-500 mt-1">Lëre bosh ose 0 për asnjë zbritje. Produktet në zbritje shfaqen të parët.</p>
                </div>

                <div class="md:col-span-2">
                    <label for="admin_note" class="block text-sm font-medium text-amber-700 mb-1">
                        <i class="fas fa-lock mr-1"></i> Shënim i brendshëm (vetëm për adminë)
                    </label>
                    <input type="text" id="admin_note" name="admin_note" value="{{ old('admin_note', $product->admin_note) }}"
                        class="w-full px-3 py-2 border border-amber-300 bg-amber-50 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-400"
                        placeholder="p.sh. Ngjyrë e kuqe / Variant 2 / Lloji A”">
                    <p class="text-xs text-gray-500 mt-1">Shënim i brendshëm i dukshm vetëm në dashboard — NUK shfaqet në faqen publike. Shfrytezohet për të dalluar produktet e ngjashme (p.sh. ngjyra) gjatë paketimit të porosive.</p>
                </div>

                <div class="md:col-span-2">
                    <label for="sizes" class="block text-sm font-medium text-gray-700 mb-1">Vlerat e variantit (madhësi / marka / ngjyra) — ndarë me presje</label>
                    <input type="text" id="sizes" name="sizes" value="{{ old('sizes', $product->sizes ? implode(', ', $product->sizes) : '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500"
                        placeholder="p.sh. S, M, L, XL  ose  Mercedes, Audi, BMW">
                    <p class="text-xs text-gray-500 mt-1">Shkruani vlerat e ndara me presje. Lëre bosh për produkt pa variante.</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Imazhet</label>
                    <div class="mb-3">
                        <input type="file" id="images" name="images[]" multiple accept="image/*"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500">
                        <p class="text-xs text-gray-500 mt-1">Ngarko deri në 5 imazhe (jpg, jpeg, png, gif, webp)</p>
                    </div>

                    <!-- Current Images Preview -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        @for($i = 1; $i <= 5; $i++)
                            @php $imgUrl = $product->getImageDataUrl($i); @endphp
                            @if($imgUrl)
                                <div class="relative">
                                    <img src="{{ $imgUrl }}" alt="Preview {{ $i }}" class="w-full h-32 object-cover rounded-md border border-gray-200">
                                </div>
                            @endif
                        @endfor
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('dashboard.products.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">Anulo</a>
                <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-md hover:bg-black transition-colors">Përditëso Produktin</button>
            </div>
        </form>
    </div>
@endsection
