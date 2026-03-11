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
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Çmimi *</label>
                    <input type="number" step="0.01" id="price" name="price" value="{{ old('price', $product->price) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>

                <div>
                    <label for="available" class="block text-sm font-medium text-gray-700 mb-1">Stoku</label>
                    <select id="available" name="available" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="1" {{ old('available', $product->available) ? 'selected' : '' }}>Ne stok</option>
                        <option value="0" {{ !old('available', $product->available) ? 'selected' : '' }}>Pa stok</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Përshkrimi</label>
                    <textarea id="description" name="description" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label for="sizes" class="block text-sm font-medium text-gray-700 mb-1">Madhësitë (ndarë me presje)</label>
                    <input type="text" id="sizes" name="sizes" value="{{ old('sizes', $product->sizes ? implode(', ', $product->sizes) : '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="S, M, L, XL">
                    <p class="text-xs text-gray-500 mt-1">Shkruani madhësitë e ndara me presje. Lëre bosh për madhësi standarde (S, M, L, XL).</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Imazhet</label>
                    <div class="mb-3">
                        <input type="file" id="images" name="images[]" multiple accept="image/*"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">Përditëso Produktin</button>
            </div>
        </form>
    </div>
@endsection
