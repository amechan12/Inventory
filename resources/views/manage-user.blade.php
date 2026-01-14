@extends('layout')

@section('title', 'Kelola Pengguna')

@section('content')
<style>
    /* Prevent body scroll on mobile devices */
    @media (max-width: 1024px) {
        /* Prevent body scroll but allow main content scroll */
        body.no-scroll {
            position: fixed;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        
        /* Allow scroll only in main content area */
        main {
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            position: relative;
        }
        
        /* Ensure table container is scrollable */
        .table-container {
            max-height: calc(100vh - 250px);
            overflow-y: auto;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            touch-action: pan-x pan-y;
        }
    }
</style>

<div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">

    <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center">
            <i class="fa-solid fa-users-cog text-white text-xl"></i>
        </div>
        <div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Kelola Pengguna</h1>
            <p class="text-sm text-gray-500">Atur role dan izin pengguna</p>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-xl">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-check-circle text-green-500 text-xl"></i>
                <p class="text-green-700 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <div class="table-container rounded-xl border border-gray-100 w-full overflow-hidden" style="max-height: calc(100vh - 300px); display: flex; flex-direction: column;">
        <div class="overflow-x-auto overflow-y-hidden flex-shrink-0">
            <table class="min-w-full w-full text-sm text-left">
                <thead class="text-xs uppercase bg-gradient-to-r from-indigo-50 to-purple-50 text-gray-700 sticky top-0 z-10">
                    <tr>
                        <th scope="col" class="px-4 sm:px-6 py-4 font-semibold whitespace-nowrap">Pengguna</th>
                        <th scope="col" class="px-4 sm:px-6 py-4 font-semibold whitespace-nowrap">Email</th>
                        <th scope="col" class="px-4 sm:px-6 py-4 font-semibold whitespace-nowrap">Role Saat Ini</th>
                        <th scope="col" class="px-4 sm:px-6 py-4 font-semibold whitespace-nowrap">Ganti Role</th>
                    </tr>
                </thead>
            </table>
        </div>
        <div class="overflow-x-auto overflow-y-auto flex-1" style="-webkit-overflow-scrolling: touch; touch-action: pan-x pan-y;">
            <table class="min-w-full w-full text-sm text-left">
                <tbody>
                @forelse ($users as $user)
                <tr class="bg-white border-b hover:bg-gradient-to-r hover:from-indigo-50/50 hover:to-purple-50/50 transition-all">
                    <th scope="row" class="px-4 sm:px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                        <div class="flex items-center gap-2 sm:gap-4">
                            <img class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl object-cover border-2 border-indigo-100 shadow-sm flex-shrink-0"
                                 src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=6366f1&color=fff' }}"
                                 alt="{{ $user->name }}">
                            <div class="min-w-0">
                                <div class="font-semibold text-gray-800 truncate">{{ $user->name }}</div>
                                <div class="text-xs text-gray-500">ID: #{{ $user->id }}</div>
                            </div>
                        </div>
                    </th>
                    <td class="px-4 sm:px-6 py-4 text-gray-600 whitespace-nowrap">{{ $user->email }}</td>
                    <td class="px-4 sm:px-6 py-4">
                        <span class="px-3 py-1.5 font-semibold text-xs rounded-full
                            {{ $user->role == 'pengelola' ? 'bg-gradient-to-r from-red-100 to-rose-100 text-red-700' : '' }}
                            {{ $user->role == 'kasir' ? 'bg-gradient-to-r from-yellow-100 to-amber-100 text-yellow-700' : '' }}
                            {{ $user->role == 'anggota' ? 'bg-gradient-to-r from-green-100 to-emerald-100 text-green-700' : '' }}">
                            <i class="fa-solid 
                                {{ $user->role == 'pengelola' ? 'fa-crown' : '' }}
                                {{ $user->role == 'kasir' ? 'fa-cash-register' : '' }}
                                {{ $user->role == 'anggota' ? 'fa-user' : '' }} mr-1"></i>
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="px-4 sm:px-6 py-4">
                        <form action="{{ route('users.update.role', $user->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3">
                                <select name="role" class="flex-1 px-3 sm:px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all text-sm font-medium">
                                    <option value="anggota" {{ $user->role == 'anggota' ? 'selected' : '' }}>👤 Anggota</option>
                                    <option value="pengelola" {{ $user->role == 'pengelola' ? 'selected' : '' }}>👑 Pengelola</option>
                                </select>
                                <button type="submit" class="px-3 sm:px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-semibold rounded-xl hover:shadow-lg transition-all text-sm whitespace-nowrap">
                                    <i class="fa-solid fa-save mr-1"></i><span class="hidden sm:inline">Simpan</span><span class="sm:hidden">Simpan</span>
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
</div>

    <script>
    // Prevent body scroll on mobile devices
    document.addEventListener('DOMContentLoaded', function() {
        if (window.innerWidth <= 1024) {
            document.body.classList.add('no-scroll');
            
            // Prevent touchmove on body except in scrollable containers
            document.body.addEventListener('touchmove', function(e) {
                const target = e.target;
                const isScrollable = target.closest('.table-container') || 
                                    target.closest('main') ||
                                    target.closest('.overflow-y-auto') ||
                                    target.closest('.overflow-auto');
                
                if (!isScrollable) {
                    e.preventDefault();
                }
            }, { passive: false });
            
            // Cleanup on page unload
            window.addEventListener('beforeunload', function() {
                document.body.classList.remove('no-scroll');
            });
        }
    });
    
    // Alert auto-dismiss
    setTimeout(function() {
        const alerts = document.querySelectorAll('.bg-gradient-to-r');
        alerts.forEach(alert => {
            if (alert.classList.contains('from-green-50')) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        });
    }, 5000);

    // Use layout search input to filter users live
    (function() {
        function debounce(fn, wait) {
            let t;
            return function(...args) {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), wait);
            };
        }

        document.addEventListener('DOMContentLoaded', function() {
            const search = document.querySelector('input[name="search"]');
            if (!search) return;

            const tbody = document.querySelector('tbody');
            if (!tbody) return;

            const userRows = Array.from(tbody.querySelectorAll('tr'));

            function showNoResults(show) {
                let nr = document.getElementById('no-users-found-row');
                if (show) {
                    if (!nr) {
                        nr = document.createElement('tr');
                        nr.id = 'no-users-found-row';
                        nr.innerHTML = '<td colspan="4" class="text-center py-8 text-gray-500">Tidak ada pengguna yang cocok.</td>';
                        tbody.appendChild(nr);
                    }
                } else if (nr) {
                    nr.remove();
                }
            }

            function applyFilter() {
                const q = search.value.trim().toLowerCase();
                let visible = 0;
                userRows.forEach(r => {
                    // If this row is the empty/placeholder row (colspan), keep it as-is and treat as not a data row
                    const colspanTd = r.querySelector('td[colspan]');
                    if (colspanTd) return;

                    const text = r.textContent.toLowerCase();
                    if (text.includes(q)) {
                        r.style.display = '';
                        visible++;
                    } else {
                        r.style.display = 'none';
                    }
                });

                showNoResults(visible === 0);
            }

            const debouncedFilter = debounce(applyFilter, 180);
            search.addEventListener('input', debouncedFilter);

            // Apply initial filter if the layout search already has a value
            applyFilter();
        });
    })();
</script>
@endsection