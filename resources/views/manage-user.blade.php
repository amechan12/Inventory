@extends('layout')

@section('title', 'Kelola Pengguna')

@section('content')
<div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">

    <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 rounded-xl bg-linear-to-br from-indigo-500 to-purple-500 flex items-center justify-center">
            <i class="fa-solid fa-users-cog text-white text-xl"></i>
        </div>
        <div>
            <h1 class="text-2xl font-bold bg-linear-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Kelola Pengguna</h1>
            <p class="text-sm text-gray-500">Atur role dan izin pengguna</p>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-linear-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-xl">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-check-circle text-green-500 text-xl"></i>
                <p class="text-green-700 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <div class="overflow-x-auto overflow-y-hidden rounded-xl border border-gray-100 w-full" style="-webkit-overflow-scrolling: touch; max-width:100vw; box-sizing:border-box;">
        <div class="    w-full">
            <table class="min-w-max w-full text-sm text-left">
            <thead class="text-xs uppercase bg-linear-to-r from-indigo-50 to-purple-50 text-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-4 font-semibold">Pengguna</th>
                    <th scope="col" class="px-6 py-4 font-semibold">Email</th>
                    <th scope="col" class="px-6 py-4 font-semibold">Role Saat Ini</th>
                    <th scope="col" class="px-6 py-4 font-semibold">Ganti Role</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                <tr class="bg-white border-b hover:bg-linear-to-r hover:from-indigo-50/50 hover:to-purple-50/50 transition-all">
                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                        <div class="flex items-center gap-4">
                            <img class="h-12 w-12 rounded-xl object-cover border-2 border-indigo-100 shadow-sm"
                                 src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=6366f1&color=fff' }}"
                                 alt="{{ $user->name }}">
                            <div>
                                <div class="font-semibold text-gray-800">{{ $user->name }}</div>
                                <div class="text-xs text-gray-500">ID: #{{ $user->id }}</div>
                            </div>
                        </div>
                    </th>
                    <td class="px-6 py-4 text-gray-600">{{ $user->email }}</td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1.5 font-semibold text-xs rounded-full
                            {{ $user->role == 'pengelola' ? 'bg-linear-to-r from-red-100 to-rose-100 text-red-700' : '' }}
                            {{ $user->role == 'kasir' ? 'bg-linear-to-r from-yellow-100 to-amber-100 text-yellow-700' : '' }}
                            {{ $user->role == 'anggota' ? 'bg-linear-to-r from-green-100 to-emerald-100 text-green-700' : '' }}">
                            <i class="fa-solid 
                                {{ $user->role == 'pengelola' ? 'fa-crown' : '' }}
                                {{ $user->role == 'kasir' ? 'fa-cash-register' : '' }}
                                {{ $user->role == 'anggota' ? 'fa-user' : '' }} mr-1"></i>
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <form action="{{ route('users.update.role', $user->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="flex items-center gap-3">
                                <select name="role" class="flex-1 px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all text-sm font-medium">
                                    <option value="anggota" {{ $user->role == 'anggota' ? 'selected' : '' }}>👤 Anggota</option>
                                    <option value="pengelola" {{ $user->role == 'pengelola' ? 'selected' : '' }}>👑 Pengelola</option>
                                </select>
                                <button type="submit" class="px-4 py-2 bg-linear-to-r from-indigo-500 to-purple-500 text-white font-semibold rounded-xl hover:shadow-lg transition-all text-sm whitespace-nowrap">
                                    <i class="fa-solid fa-save mr-1"></i>Simpan
                                </button>
                            </div>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-20 text-gray-500">
                        <i class="fa-solid fa-users-slash text-6xl text-gray-300 mb-4 block"></i>
                        <p class="text-lg font-medium">Tidak ada pengguna lain yang ditemukan.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    setTimeout(function() {
        const alerts = document.querySelectorAll('.bg-linear-to-r');
        alerts.forEach(alert => {
            if (alert.classList.contains('from-green-50')) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        });
    }, 5000);
</script>
@endsection