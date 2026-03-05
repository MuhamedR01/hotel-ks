<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Hotel KS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="bg-blue-600 text-white w-16 h-16 rounded-full flex items-center justify-center font-bold text-2xl mx-auto mb-4">
                    KS
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Admin Panel</h1>
                <p class="text-gray-600 mt-2">Kyçuni për të vazhduar</p>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-start">
                    <i class="fas fa-check-circle mt-0.5 mr-2"></i>
                    <span class="text-sm">{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-lg flex items-start">
                    <i class="fas fa-exclamation-circle mt-0.5 mr-2"></i>
                    <span class="text-sm">{{ $errors->first() }}</span>
                </div>
            @endif

            @if(request('timeout'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-lg flex items-start">
                    <i class="fas fa-exclamation-circle mt-0.5 mr-2"></i>
                    <span class="text-sm">Sesioni juaj ka skaduar. Ju lutem kyçuni përsëri.</span>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('dashboard.login.submit') }}" class="space-y-6">
                @csrf
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-1"></i> Emri i Përdoruesit ose Email
                    </label>
                    <input type="text" id="username" name="username" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="admin" autocomplete="username" value="{{ old('username') }}">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-1"></i> Fjalëkalimi
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all pr-12"
                            placeholder="••••••••" autocomplete="current-password">
                        <button type="button" onclick="togglePassword()"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i id="password-icon" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-sign-in-alt mr-2"></i> Kyçu
                </button>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        }

        setTimeout(() => {
            document.querySelectorAll('.bg-green-50, .bg-red-50').forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>
