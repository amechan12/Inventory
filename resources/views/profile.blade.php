@extends('layout')

@section('title', 'Profil Saya')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <div class="lg:col-span-2 space-y-8">
        {{-- Profile Photo Section --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-lg bg-linear-to-br from-indigo-500 to-purple-500 flex items-center justify-center">
                    <i class="fa-solid fa-image text-white"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Foto Profil</h2>
                    <p class="text-sm text-gray-500">Update foto profil Anda</p>
                </div>
            </div>
            
            <form action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="flex items-center space-x-6">
                    <img id="photo-preview" class="h-24 w-24 rounded-2xl object-cover border-4 border-indigo-100 shadow-lg" 
                         src="{{ Auth::user()->profile_photo_path ? asset('storage/' . Auth::user()->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=6366f1&color=fff&size=200' }}" 
                         alt="{{ Auth::user()->name }}">

                    <div class="flex-1">
                        <input type="file" name="photo" id="photo" class="hidden" accept="image/*">
                        <div class="flex gap-3">
                            <button type="button" id="select-photo-btn" 
                                class="px-4 py-2 bg-white border-2 border-indigo-200 text-indigo-600 rounded-xl font-semibold hover:bg-indigo-50 transition-all">
                                <i class="fa-solid fa-upload mr-2"></i>Pilih Foto
                            </button>
                            <button type="submit" 
                                class="px-4 py-2 bg-linear-to-r from-indigo-500 to-purple-500 text-white rounded-xl font-semibold hover:shadow-lg transition-all">
                                <i class="fa-solid fa-save mr-2"></i>Simpan
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">JPG, JPEG, PNG. Maksimal 2MB.</p>
                    </div>
                </div>
                @error('photo')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
            </form>
        </div>

        {{-- Profile Info Section --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-lg bg-linear-to-br from-blue-500 to-cyan-500 flex items-center justify-center">
                    <i class="fa-solid fa-user-edit text-white"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Informasi Profil</h2>
                    <p class="text-sm text-gray-500">Perbarui informasi akun Anda</p>
                </div>
            </div>
            
            @if (session('success_profile'))
                <div class="mb-6 p-4 bg-linear-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-xl">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-check-circle text-green-500 text-xl"></i>
                        <p class="text-green-700 font-medium">{{ session('success_profile') }}</p>
                    </div>
                </div>
            @endif

            <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required 
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                </div>
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Alamat Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                </div>
                <div>
                    <label for="role" class="block text-sm font-semibold text-gray-700 mb-2">Role</label>
                    <div class="relative">
                        <input type="text" id="role" name="role" value="{{ ucfirst($user->role) }}" readonly
                               class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-linear-to-r from-gray-50 to-indigo-50 cursor-not-allowed">
                        <span class="absolute right-4 top-1/2 transform -translate-y-1/2 px-3 py-1 bg-indigo-500 text-white text-xs font-semibold rounded-full">
                            {{ ucfirst($user->role) }}
                        </span>
                    </div>
                </div>
                <div class="flex justify-end pt-2">
                    <button type="submit" 
                        class="px-6 py-3 bg-linear-to-r from-indigo-500 to-purple-500 text-white font-semibold rounded-xl hover:shadow-lg transition-all">
                        <i class="fa-solid fa-save mr-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Password Section --}}
    <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 sticky top-24">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-lg bg-linear-to-br from-pink-500 to-rose-500 flex items-center justify-center">
                    <i class="fa-solid fa-lock text-white"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Ubah Password</h2>
                    <p class="text-sm text-gray-500">Keamanan akun Anda</p>
                </div>
            </div>
            
            @if (session('success_password'))
                <div class="mb-4 p-3 bg-linear-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl">
                    <p class="text-green-700 text-sm font-medium">{{ session('success_password') }}</p>
                </div>
            @endif

            <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-2">Password Saat Ini</label>
                    <input type="password" id="current_password" name="current_password" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 transition-all">
                    @error('current_password', 'updatePassword')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password Baru</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 transition-all">
                    @error('password', 'updatePassword')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Konfirmasi Password Baru</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 transition-all">
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" 
                        class="w-full px-6 py-3 bg-linear-to-r from-pink-500 to-rose-500 text-white font-semibold rounded-xl hover:shadow-lg transition-all">
                        <i class="fa-solid fa-key mr-2"></i>Ubah Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const photoInput = document.getElementById('photo');
        const selectPhotoBtn = document.getElementById('select-photo-btn');
        const photoPreview = document.getElementById('photo-preview');

        if (selectPhotoBtn && photoInput) {
            selectPhotoBtn.addEventListener('click', () => {
                photoInput.click();
            });
        }

        if (photoInput && photoPreview) {
            photoInput.addEventListener('change', (event) => {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        photoPreview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>
@endsection