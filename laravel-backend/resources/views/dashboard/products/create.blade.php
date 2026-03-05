@extends('dashboard.layout')

@section('title', 'Shto Produkt')
@section('page-title', 'Shto Produkt të Ri')
@section('page-subtitle', 'Plotëso të dhënat për produktin e ri')

@section('topbar-actions')
    <a href="{{ route('dashboard.products.index') }}" class="text-gray-600 hover:text-gray-900 px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors">
        <i class="fas fa-arrow-left mr-2"></i><span class="hidden sm:inline">Kthehu</span>
    </a>
@endsection

@push('styles')
<style>
    .preview-container { display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1rem; }
    .preview-item { position: relative; display: inline-block; }
    .image-preview { border-radius: 0.5rem; border: 2px solid #e5e7eb; object-fit: cover; }
    .remove-image { position: absolute; top: -8px; right: -8px; background: #ef4444; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; font-weight: bold; }
    .remove-image:hover { background: #dc2626; }
    .image-number { position: absolute; bottom: 4px; left: 4px; background: rgba(0,0,0,0.7); color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
</style>
@endpush

@section('content')
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 lg:p-8">
        <form method="POST" action="{{ route('dashboard.products.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Emri i Produktit <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        placeholder="Shënoni emrin e produktit" required>
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Çmimi (€) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" id="price" name="price" value="{{ old('price') }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        placeholder="0.00" required>
                </div>

                <div>
                    <label for="available" class="block text-sm font-medium text-gray-700 mb-1">Stoku</label>
                    <select id="available" name="available" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="1" {{ old('available', '1') == '1' ? 'selected' : '' }}>Ne stok</option>
                        <option value="0" {{ old('available') == '0' ? 'selected' : '' }}>Pa stok</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Përshkrimi</label>
                    <textarea id="description" name="description" rows="4" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" 
                        placeholder="Shkruani një përshkrim të produktit">{{ old('description') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Imazhet e Produktit <span class="text-red-500">*</span></label>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <div class="flex-1 w-full">
                            <input type="file" name="images[]" accept="image/*" id="imageInput" multiple required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="mt-2 text-sm text-gray-500">Formatet e lejuara: JPG, JPEG, PNG, GIF, WEBP (Max: 5MB per imazh)</p>
                        </div>
                        <div id="imagePreviews" class="preview-container"></div>
                    </div>
                </div>

                <div class="md:col-span-2 pt-4">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md transition-colors duration-200 flex items-center justify-center">
                        <i class="fas fa-plus-circle mr-2"></i>Shto Produktin
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('imageInput').addEventListener('change', function(e) {
        const files = e.target.files;
        const previewContainer = document.getElementById('imagePreviews');
        previewContainer.innerHTML = '';
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'preview-item';
                    previewItem.innerHTML = `
                        <img src="${e.target.result}" class="image-preview" alt="Preview" style="width:150px;height:150px;">
                        <div class="remove-image" onclick="this.parentElement.remove()">&times;</div>
                        <div class="image-number">${i + 1}</div>
                    `;
                    previewContainer.appendChild(previewItem);
                }
                reader.readAsDataURL(file);
            }
        }
    });
</script>
@endpush
