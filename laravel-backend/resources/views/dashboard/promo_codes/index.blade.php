@extends('dashboard.layout')

@section('title', 'Kodet promocionale')
@section('page-title', 'Kodet promocionale')
@section('page-subtitle', 'Krijo dhe menaxho kodet e zbritjes')

@section('topbar-actions')
    <a href="{{ route('dashboard.promo-codes.create') }}" class="bg-gray-900 hover:bg-black text-white px-4 sm:px-6 py-2 rounded-lg font-medium text-sm">
        <i class="fas fa-plus mr-2"></i>Krijo Kod
    </a>
@endsection

@section('content')
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kodi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Lloji</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Vlera</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Vlefshmëria</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Përdorime</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Statusi</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase w-32">Veprime</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($codes as $code)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="font-mono font-semibold text-gray-900">{{ $code->code }}</div>
                                @if($code->description)
                                    <div class="text-xs text-gray-500 truncate max-w-xs">{{ $code->description }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($code->discount_type === 'percent')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-blue-50 text-blue-700 border border-blue-200"><i class="fas fa-percent mr-1"></i>Përqindje</span>
                                @elseif($code->discount_type === 'fixed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-purple-50 text-purple-700 border border-purple-200"><i class="fas fa-euro-sign mr-1"></i>Vlerë fikse</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-green-50 text-green-700 border border-green-200"><i class="fas fa-truck mr-1"></i>Transport falas</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                @if($code->discount_type === 'percent')
                                    -{{ rtrim(rtrim(number_format($code->discount_value, 2, '.', ''), '0'), '.') }}%
                                @elseif($code->discount_type === 'fixed')
                                    -{{ number_format($code->discount_value, 2) }}€
                                @else
                                    Falas
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                @if(!$code->starts_at && !$code->expires_at)
                                    <span class="text-green-700 font-medium">Përgjithmonë</span>
                                @else
                                    @if($code->starts_at) <div>Nga: {{ $code->starts_at->format('d.m.Y H:i') }}</div> @endif
                                    @if($code->expires_at) <div>Deri: {{ $code->expires_at->format('d.m.Y H:i') }}</div> @else <div>Deri: <span class="text-green-700">Përgjithmonë</span></div> @endif
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $code->times_used }}@if($code->max_uses) / {{ $code->max_uses }}@else / ∞ @endif
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('dashboard.promo-codes.toggle', $code->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $code->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                        <i class="fas fa-{{ $code->is_active ? 'check' : 'times' }} mr-1"></i>
                                        {{ $code->is_active ? 'Aktiv' : 'Joaktiv' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('dashboard.promo-codes.edit', $code->id) }}" class="text-gray-700 hover:text-blue-700"><i class="fas fa-edit"></i></a>
                                    <form method="POST" action="{{ route('dashboard.promo-codes.destroy', $code->id) }}" class="inline" onsubmit="return confirm('Fshini këtë kod?');">
                                        @csrf
                                        <button type="submit" class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500"><i class="fas fa-ticket-alt text-3xl mb-2"></i><p>Asnjë kod promocional i krijuar.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-gray-200">
            @forelse($codes as $code)
                <div class="p-4">
                    <div class="flex justify-between items-start gap-3">
                        <div>
                            <div class="font-mono font-bold text-gray-900">{{ $code->code }}</div>
                            <div class="text-xs text-gray-500">
                                @if($code->discount_type === 'percent') -{{ rtrim(rtrim(number_format($code->discount_value, 2, '.', ''), '0'), '.') }}%
                                @elseif($code->discount_type === 'fixed') -{{ number_format($code->discount_value, 2) }}€
                                @else Transport falas @endif
                            </div>
                            @if($code->description)<div class="text-xs text-gray-600 mt-1">{{ $code->description }}</div>@endif
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $code->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                            {{ $code->is_active ? 'Aktiv' : 'Joaktiv' }}
                        </span>
                    </div>
                    <div class="flex gap-3 mt-3 text-sm">
                        <a href="{{ route('dashboard.promo-codes.edit', $code->id) }}" class="text-gray-700"><i class="fas fa-edit mr-1"></i>Ndrysho</a>
                        <form method="POST" action="{{ route('dashboard.promo-codes.destroy', $code->id) }}" class="inline" onsubmit="return confirm('Fshini?');">
                            @csrf
                            <button type="submit" class="text-red-600"><i class="fas fa-trash mr-1"></i>Fshi</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-500"><i class="fas fa-ticket-alt text-3xl mb-2"></i><p>Asnjë kod promocional.</p></div>
            @endforelse
        </div>
    </div>

    @if($codes->hasPages())
        <div class="mt-6">{{ $codes->links() }}</div>
    @endif
@endsection
