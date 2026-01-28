<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Enuma Vault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        /* Smooth transitions */
        * {
            transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }
    </style>
</head>

<body class="h-screen bg-white overflow-hidden">

    <div class="h-screen flex">
        <!-- Left Side - Form -->
        <div class="flex-1 flex items-center justify-center p-6 lg:p-8 overflow-y-auto">
            <div class="w-full max-w-md">
                <!-- Logo -->
                <div class="mb-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center overflow-hidden mb-3">
                        <img src="{{ asset('logo.png') }}" alt="Logo" class="w-12 h-12 object-contain">
                    </div>

                    <h1 class="text-2xl font-bold text-gray-900 mb-1">Selamat Datang !</h1>
                    <p class="text-sm text-gray-600">Daftar untuk mulai meminjam barang.</p>
                </div>

                <form action="/register" method="POST" class="space-y-3">
                    @csrf

                    <!-- Name Input -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-900 mb-1">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" required value="{{ old('name') }}"
                            class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-gray-900 placeholder-gray-400 text-sm"
                            placeholder="Enter your full name">
                    </div>

                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-900 mb-1">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" id="email" required value="{{ old('email') }}"
                            class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-gray-900 placeholder-gray-400 text-sm"
                            placeholder="Enter your email address">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone Input -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-900 mb-1">
                            Nomor Telepon
                        </label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                            class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-gray-900 placeholder-gray-400 text-sm"
                            placeholder="Enter your phone number">
                        @error('phone')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-900 mb-1">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="password" id="password" required
                                class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-gray-900 placeholder-gray-400 pr-12 text-sm"
                                placeholder="Minimum 8 characters">
                            <button type="button" onclick="togglePassword('password', 'password-icon')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i id="password-icon" class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-900 mb-1">
                            Konfirmasi Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-gray-900 placeholder-gray-400 pr-12 text-sm"
                                placeholder="Ulangi Password">
                            <button type="button"
                                onclick="togglePassword('password_confirmation', 'password-confirmation-icon')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i id="password-confirmation-icon" class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-1">
                        <button type="submit"
                            class="w-full flex justify-center items-center py-2.5 px-4 rounded-lg shadow-md text-base font-semibold text-white hover:shadow-lg transform hover:-translate-y-0.5 transition-all"
                            style="background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);">
                            Register
                        </button>
                    </div>
                </form>

                <!-- Login Link -->
                <div class="mt-3 text-center">
                    <p class="text-gray-600">
                        Already have an account ?
                        <a href="/login" class="font-semibold text-indigo-600 hover:text-indigo-500">Login here</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Side - Illustration -->
        <div class="hidden lg:flex flex-1 relative overflow-hidden">
            <img src="{{ asset('auth.png') }}" alt="Illustration" class="w-full h-full object-cover">
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const passwordIcon = document.getElementById(iconId);

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
    </script>

</body>

</html>