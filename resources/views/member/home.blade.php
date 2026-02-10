@extends('layout')

@section('title', 'Home - Dashboard Anggota')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <div class="grid grid-cols-1 gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Halo, {{ Auth::user()->name }} 👋</h2>
                <p class="text-sm text-gray-500 mt-1">Selamat datang di dashboard anggota. Di sini kamu bisa melihat status pinjaman dan melakukan aksi cepat.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <a href="{{ route('loan.borrow') }}" class="flex items-center gap-3 px-6 h-12 rounded-xl text-white font-semibold tracking-wide shadow-md bg-gradient-to-r from-indigo-600 to-purple-600">
                    <span class="w-10 h-10 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-box text-white text-lg"></i>
                    </span>
                    <span class="text-base">Barang Masuk</span>
                </a>

                <a href="{{ route('loan.return') }}" class="flex items-center gap-3 px-5 h-12  rounded-xl text-white font-semibold tracking-wide shadow-sm bg-gradient-to-r from-emerald-500 to-teal-400">
                    <span class="w-10 h-10 rounded-lg flex items-center justify-center text-white">
                        <i class="fa-solid fa-rotate-left text-lg"></i>
                    </span>
                    <span class="font-medium text-sm">Kembalikan</span>
                </a>

                <a href="{{ route('history.index') }}" class="flex items-center gap-3 px-5 h-12  rounded-xl text-white font-semibold tracking-wide shadow-sm bg-gradient-to-r from-amber-400 to-orange-400">
                    <span class="w-10 h-10 rounded-lg flex items-center justify-center text-white">
                        <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                    </span>
                    <span class="font-medium text-sm">Riwayat</span>
                </a>

                <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-5 h-12  rounded-xl text-white font-semibold tracking-wide shadow-sm bg-gradient-to-r from-sky-500 to-cyan-500">
                    <span class="w-10 h-10 rounded-lg flex items-center justify-center text-white">
                        <i class="fa-solid fa-user text-lg"></i>
                    </span>
                    <span class="font-medium text-sm">Profil</span>
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div class="flex items-center justify-between">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center bg-indigo-50 text-indigo-700">
                                <i class="fa-solid fa-user-clock text-lg"></i>
                            </div>
                            <div class="text-left">
                                <div class="text-xs text-gray-500">Sedang Dipinjam</div>
                                <div class="text-2xl font-bold text-indigo-800">{{ $activeLoans }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div class="flex items-center justify-between">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center bg-yellow-50 text-yellow-700">
                                <i class="fa-solid fa-hourglass-half text-lg"></i>
                            </div>
                            <div class="text-left">
                                <div class="text-xs text-gray-500">Menunggu Persetujuan</div>
                                <div class="text-2xl font-bold text-gray-800">{{ $pendingLoans }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div class="flex items-center justify-between">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center bg-red-50 text-red-600">
                                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                            </div>
                            <div class="text-left">
                                <div class="text-xs text-gray-500">Jatuh Tempo (3 hari)</div>
                                <div class="text-2xl font-bold text-red-600">{{ $dueSoonLoans }}</div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h3 class="font-semibold text-gray-800 mb-4">Pinjaman Terbaru</h3>

            @if($currentLoans->isEmpty())
                <div class="text-sm text-gray-500">Belum ada pinjaman. Coba cari barang dan ajukan peminjaman.</div>
            @else
                <ul class="space-y-4">
                    @foreach($currentLoans as $trx)
                        <li class="border border-gray-100 rounded-xl p-4 flex items-start justify-between gap-4">
                            <div>
                                <div class="text-sm text-gray-500">#{{ $trx->invoice_number }} &middot; {{ $trx->created_at->format('d M Y') }}</div>
                                <div class="mt-2 font-medium text-gray-800">{{ $trx->products->pluck('name')->join(', ') }}</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Durasi: 
                                    @if($trx->duration == 0)
                                        <span class="text-purple-600 font-semibold">Permanen</span>
                                    @else
                                        {{ $trx->duration ?? '-' }} hari
                                    @endif
                                    &middot; Kembali: {{ $trx->return_date ? $trx->return_date->format('d M Y') : '-' }}
                                </div>
                            </div>
                            <div class="text-right">
                                @if($trx->status == 'pending')
                                    <span class="px-3 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">Menunggu</span>
                                @elseif($trx->status == 'borrowed')
                                    <span class="px-3 py-1 rounded-full text-xs bg-indigo-100 text-indigo-800">Sedang Dipinjam</span>
                                @elseif($trx->status == 'returning')
                                    <span class="px-3 py-1 rounded-full text-xs bg-orange-100 text-orange-800">Menunggu Pengembalian</span>
                                @elseif($trx->status == 'returned')
                                    <span class="px-3 py-1 rounded-full text-xs bg-green-100 text-green-800">Selesai</span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs bg-gray-100 text-gray-700">{{ ucfirst($trx->status) }}</span>
                                @endif
                                <div class="mt-2">
                                    <a href="{{ route('history.show', $trx->id) }}" class="inline-flex items-center gap-2 text-sm text-indigo-600 hover:underline">Lihat Detail</a>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h3 class="font-semibold text-gray-800 mb-4">Rekomendasi & Top Barang</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($topProductLabels as $index => $label)
                    <div class="p-4 border border-gray-100 rounded-xl flex items-center justify-between gap-4">
                        <div>
                            <div class="text-sm text-gray-500">{{ $label }}</div>
                            <div class="mt-1 text-xs text-gray-400">Dipinjam {{ $topProductData[$index] ?? 0 }} kali</div>
                        </div>
                        <div>
                            <a href="{{ route('loan.borrow') }}" class="inline-flex items-center gap-2 px-3 py-1 rounded-xl bg-indigo-600 text-white text-sm">Pinjam</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Daftar Barang yang Tersedia --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h3 class="text-2xl font-bold text-gray-800">Daftar Barang yang Tersedia</h3>
                <p class="text-sm text-gray-500 mt-2">Lihat semua barang yang dapat dipinjam</p>
            </div>
        </div>

        {{-- Filters --}}
        @include('components.category-filter', ['categories' => $categories ?? [], 'boxes' => $boxes ?? [], 'products' => $products, 'totalProducts' => $totalProducts ?? $products->count()])

        {{-- Products Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse ($products as $product)
                <div
                    class="bg-white rounded-2xl shadow-sm overflow-hidden group hover:shadow-xl transition-all duration-300 border border-gray-100 flex flex-col">
                    <div
                        class="w-full aspect-square bg-gradient-to-br from-indigo-50 to-purple-50 flex items-center justify-center relative overflow-hidden">
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">

                        @php $avail = $product->available_stock; @endphp
                        @if ($avail <= 1)
                            <div
                                class="absolute top-3 left-3 bg-red-500 text-white text-xs px-3 py-1 rounded-full font-semibold shadow-lg">
                                @if ($avail == 0)
                                    Kosong
                                @else
                                    Sisa {{ $avail }}
                                @endif
                            </div>
                        @else
                            <div
                                class="absolute top-3 left-3 bg-emerald-500 text-white text-xs px-3 py-1 rounded-full font-semibold shadow-lg">
                                Tersedia {{ $avail }}
                            </div>
                        @endif
                    </div>

                    <div class="p-4 flex-1 flex flex-col">
                        <h3 class="text-lg font-bold text-gray-800 line-clamp-2 mb-2">{{ $product->name }}</h3>
                        
                        <div class="flex justify-between items-center mb-4 text-sm">
                            @if($product->category)
                                <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded-lg font-medium">{{ $product->category }}</span>
                            @endif
                            <p class="font-semibold {{ $avail > 5 ? 'text-gray-500' : ($avail > 0 ? 'text-orange-500' : 'text-red-500') }}">
                                Stok: {{ $avail }}
                            </p>
                        </div>

                        {{-- Box Info --}}
                        @if($product->boxes->count() > 0)
                            <div class="mb-4 text-xs bg-purple-50 border border-purple-200 rounded-lg p-2">
                                <p class="text-purple-700 font-medium">
                                    <i class="fa-solid fa-box mr-1"></i>
                                    @foreach($product->boxes as $box)
                                        @if($loop->count > 1)
                                            {{ $box->name }}{{ !$loop->last ? ',' : '' }}
                                        @else
                                            {{ $box->name }}
                                        @endif
                                    @endforeach
                                </p>
                            </div>
                        @endif

                        {{-- Action Button --}}
                        <a href="{{ route('loan.borrow') }}" 
                            class="mt-auto w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-center py-2.5 px-3 rounded-xl hover:shadow-md transition-all font-medium {{ $avail == 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                            {{ $avail == 0 ? 'onclick="return false;"' : '' }}>
                            <i class="fa-solid fa-plus-circle mr-1"></i>Pinjam
                        </a>
                    </div>
                </div>
            @empty
                <div class="sm:col-span-2 xl:col-span-3 text-center py-20">
                    <i class="fa-solid fa-box-open text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">
                        @if (request('search'))
                            Barang dengan nama "{{ request('search') }}" tidak ditemukan.
                        @elseif(request('category') || request('box'))
                            Barang dengan filter ini tidak ditemukan.
                        @else
                            Belum ada barang yang tersedia.
                        @endif
                    </h3>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($products->hasPages())
            <div class="mt-8">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection