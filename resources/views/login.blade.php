<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Enuma Vault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .animate-gradient {
            background-size: 200% 200%;
            animation: gradient 6s ease infinite;
        }
    </style>
</head>
<body class="bg-linear-to-br from-indigo-100 via-purple-50 to-pink-100 animate-gradient min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <div class="bg-white rounded-3xl shadow-2xl p-8 border border-gray-100">
            {{-- Logo & Branding --}}
            <div class="text-center mb-8">
                    <div class="w-20 h-20 mx-auto mb-4 rounded-2xl flex items-center justify-center shadow-lg overflow-hidden">
                    <img src="{{ asset('logo.png') }}" alt="Logo" class="w-full h-full object-contain">
                </div>
                <h1 class="text-3xl font-bold bg-linear-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-2">
                    Enuma Vault
                </h1>
                <h2 class="text-xl font-semibold text-gray-800">Selamat Datang Kembali</h2>
                <p class="text-gray-500 mt-1">Silakan masuk ke akun Anda</p>
            </div>

            <form action="/login" method="POST" class="space-y-5">
                @csrf
                
                {{-- Email Input --}}
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" name="email" id="email" required 
                               class="block w-full pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all"
                               placeholder="you@example.com">
                    </div>
                </div>

                {{-- Password Input --}}
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password" id="password" required
                               class="block w-full pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all"
                               placeholder="••••••••">
                    </div>
                </div>

                {{-- Remember & Forgot --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" 
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="remember-me" class="ml-2 block text-sm text-gray-700">Ingat saya</label>
                    </div>

                    <div class="text-sm">
                        <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">Lupa password?</a>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div>
                    <button type="submit" 
                            class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-base font-semibold text-white bg-linear-to-r from-indigo-500 to-purple-500 hover:shadow-lg transition-all">
                        <i class="fa-solid fa-right-to-bracket mr-2"></i>
                        Masuk
                    </button>
                </div>
            </form>

            {{-- Register Link --}}
            <p class="mt-8 text-center text-sm text-gray-600">
                Belum punya akun?
                <a href="/register" class="font-semibold text-indigo-600 hover:text-indigo-500">Daftar di sini</a>
            </p>
        </div>

        {{-- Footer Info --}}
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                <i class="fa-solid fa-shield-halved mr-1"></i>
                Inventory Management System
            </p>
        </div>
    </div>

</body>
</html>