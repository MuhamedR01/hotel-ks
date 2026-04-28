@extends('dashboard.layout')

@section('title', $code ?? null ? 'Ndrysho Kodin' : 'Krijo Kod')
@section('page-title', $code ?? null ? 'Ndrysho Kodin Promocional' : 'Krijo Kod Promocional')

@php $isEdit = isset($code); @endphp

@section('content')
    @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
            <ul class="list-disc list-inside">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ $isEdit ? route('dashboard.promo-codes.update', $code->id) : route('dashboard.promo-codes.store') }}"
          class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-5 max-w-3xl">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kodi <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $isEdit ? $code->code : '') }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md font-mono uppercase focus:ring-2 focus:ring-gray-500"
                    placeholder="WELCOME10">
                <p class="text-xs text-gray-500 mt-1">Klienti do ta shkruajë këtë në checkout (shndërrohet automatikisht në UPPERCASE).</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lloji i zbritjes <span class="text-red-500">*</span></label>
                <select name="discount_type" id="discount_type" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-gray-500">
                    @php $type = old('discount_type', $isEdit ? $code->discount_type : 'percent'); @endphp
                    <option value="percent"        {{ $type === 'percent' ? 'selected' : '' }}>Përqindje (%)</option>
                    <option value="fixed"          {{ $type === 'fixed' ? 'selected' : '' }}>Vlerë fikse (€)</option>
                    <option value="free_shipping"  {{ $type === 'free_shipping' ? 'selected' : '' }}>Transport falas</option>
                </select>
            </div>

            <div id="value_field">
                <label class="block text-sm font-medium text-gray-700 mb-1">Vlera <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" min="0" name="discount_value"
                    value="{{ old('discount_value', $isEdit ? $code->discount_value : '') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-gray-500"
                    placeholder="10">
                <p class="text-xs text-gray-500 mt-1"><span id="value_hint">% e zbritjes (0–100)</span></p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Shuma minimale e shportës (€)</label>
                <input type="number" step="0.01" min="0" name="min_subtotal"
                    value="{{ old('min_subtotal', $isEdit ? $code->min_subtotal : '') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-gray-500"
                    placeholder="Lëre bosh për të mos pasur kufizim">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vlen prej</label>
                <input type="datetime-local" name="starts_at"
                    value="{{ old('starts_at', $isEdit && $code->starts_at ? $code->starts_at->format('Y-m-d\TH:i') : '') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-gray-500">
                <p class="text-xs text-gray-500 mt-1">Lëre bosh për ta aktivizuar menjëherë.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Skadon më</label>
                <input type="datetime-local" name="expires_at"
                    value="{{ old('expires_at', $isEdit && $code->expires_at ? $code->expires_at->format('Y-m-d\TH:i') : '') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-gray-500">
                <p class="text-xs text-gray-500 mt-1">Lëre bosh për <strong>përgjithmonë</strong>.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Maksimumi i përdorimeve</label>
                <input type="number" min="1" name="max_uses"
                    value="{{ old('max_uses', $isEdit ? $code->max_uses : '') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-gray-500"
                    placeholder="Lëre bosh për pakufizim">
            </div>

            <div class="flex items-center mt-7">
                <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                        {{ old('is_active', $isEdit ? $code->is_active : true) ? 'checked' : '' }}
                        class="rounded">
                    Aktiv
                </label>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Përshkrim (opsional)</label>
                <input type="text" name="description" value="{{ old('description', $isEdit ? $code->description : '') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-gray-500"
                    placeholder="P.sh. Promocion verës 2026">
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2 border-t border-gray-200">
            <button type="submit" class="bg-gray-900 hover:bg-black text-white px-5 py-2 rounded-lg font-medium text-sm">
                <i class="fas fa-save mr-1"></i>{{ $isEdit ? 'Ruaj ndryshimet' : 'Krijo kodin' }}
            </button>
            <a href="{{ route('dashboard.promo-codes.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm">Anulo</a>
        </div>
    </form>

    <script>
        (function() {
            const sel = document.getElementById('discount_type');
            const field = document.getElementById('value_field');
            const hint = document.getElementById('value_hint');
            const input = field.querySelector('input[name="discount_value"]');
            function update() {
                if (sel.value === 'free_shipping') {
                    field.style.display = 'none';
                    input.removeAttribute('required');
                } else {
                    field.style.display = '';
                    input.setAttribute('required', 'required');
                    hint.textContent = sel.value === 'percent' ? '% e zbritjes (0–100)' : 'Shuma në Euro për të zbritur';
                }
            }
            sel.addEventListener('change', update);
            update();
        })();
    </script>
@endsection
