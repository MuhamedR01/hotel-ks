@extends('dashboard.layout')

@section('title', 'Cilësimet')
@section('page-title', 'Cilësimet')
@section('page-subtitle', 'Menaxhoni administratorët dhe rolet e tyre')

@php
    $role_labels = ['super_admin' => 'Super Admin', 'manager' => 'Menaxher', 'worker' => 'Punëtor'];
    $role_colors = ['super_admin' => 'bg-purple-100 text-purple-800', 'manager' => 'bg-blue-100 text-blue-800', 'worker' => 'bg-green-100 text-green-800'];
@endphp

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Add Admin Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-600">
                    <h3 class="text-xl font-semibold text-white"><i class="fas fa-user-plus mr-2"></i>Shto Admin</h3>
                </div>
                <form method="POST" action="{{ route('dashboard.settings.addAdmin') }}" class="p-6">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-2"><i class="fas fa-user mr-2"></i>Emri i Përdoruesit <span class="text-red-500">*</span></label>
                            <input type="text" id="username" name="username" required value="{{ old('username') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="username">
                            <p class="mt-1 text-xs text-gray-500">Përdoret për kyçje</p>
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2"><i class="fas fa-id-card mr-2"></i>Emri i Plotë <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" required value="{{ old('name') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Emri Mbiemri">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2"><i class="fas fa-envelope mr-2"></i>Email <span class="text-gray-400">(Opsionale)</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="email@example.com">
                            <p class="mt-1 text-xs text-gray-500">Mund të lihet bosh</p>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2"><i class="fas fa-lock mr-2"></i>Fjalëkalimi <span class="text-red-500">*</span></label>
                            <input type="password" id="password" name="password" required minlength="6"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Min. 6 karaktere">
                        </div>

                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-2"><i class="fas fa-user-tag mr-2"></i>Roli <span class="text-red-500">*</span></label>
                            <select id="role" name="role" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Zgjidhni rolin</option>
                                <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin - Qasje e plotë</option>
                                <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Menaxher - Produktet & Porositë</option>
                                <option value="worker" {{ old('role') == 'worker' ? 'selected' : '' }}>Punëtor - Vetëm Porositë</option>
                            </select>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                                <i class="fas fa-plus mr-2"></i>Shto Admin
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Role Descriptions -->
            <div class="mt-6 bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6 bg-gray-50 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-info-circle mr-2"></i>Përshkrimi i Roleve</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <div class="flex items-center mb-2"><span class="px-3 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">Super Admin</span></div>
                        <p class="text-sm text-gray-600">Qasje e plotë në të gjitha funksionet: Dashboard, Produktet, Porositë, Klientët, Cilësimet</p>
                    </div>
                    <div>
                        <div class="flex items-center mb-2"><span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">Menaxher</span></div>
                        <p class="text-sm text-gray-600">Mund të menaxhojë produktet (shto, edito, fshi) dhe porositë. Nuk ka qasje në Dashboard dhe Cilësimet</p>
                    </div>
                    <div>
                        <div class="flex items-center mb-2"><span class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Punëtor</span></div>
                        <p class="text-sm text-gray-600">Vetëm mund të shohë porositë. Nuk ka qasje në produktet, dashboard apo cilësimet.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6 bg-gray-50 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-900">Administratorët</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Emri i Përdoruesit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Emri</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roli</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data e Krijimit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Veprime</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($admins as $admin)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $admin->username }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $admin->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $admin->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $role_colors[$admin->role] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $role_labels[$admin->role] ?? ucfirst(str_replace('_', ' ', $admin->role)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $admin->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if($admin->id !== Auth::guard('admin')->id())
                                            <form method="POST" action="{{ route('dashboard.settings.deleteAdmin', $admin->id) }}" class="inline" onsubmit="return confirm('Jeni i sigurt që doni të fshini këtë admin?')">
                                                @csrf
                                                <button type="submit" class="text-red-600 hover:text-red-900"><i class="fas fa-trash mr-1"></i>Fshi</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
