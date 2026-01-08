@extends('layout')

@section('title', 'Home - Dashboard Anggota')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Halo, {{ Auth::user()->name }} 👋</h2>
            <p class="text-sm text-gray-500 mt-1">Selamat datang di dashboard anggota. Di sini kamu bisa melihat status pinjaman dan melakukan aksi cepat.</p>
            <div class="mt-4 flex gap-3 flex-wrap">
                <a href="{{ route('loan.borrow') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-sm hover:opacity-95">
                    <i class="fa-solid fa-box mr-1"></i> Pinjam Barang
                </a>
                <a href="{{ route('loan.return') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 shadow-sm hover:bg-gray-50">
                    <i class="fa-solid fa-rotate-left mr-1"></i> Kembalikan
                </a>
                <a href="{{ route('history.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 shadow-sm hover:bg-gray-50">
                    <i class="fa-solid fa-clock-rotate-left mr-1"></i> Riwayat
                </a>
                <a href="{{ route('profile.show') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 shadow-sm hover:bg-gray-50">
                    <i class="fa-solid fa-user mr-1"></i> Profil
                </a>
            </div>
        </div>

        <div class="flex gap-4 w-full sm:w-auto">
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 text-center w-36">
                <div class="text-xs text-gray-500">Sedang Dipinjam</div>
                <div class="text-2xl font-bold text-gray-800">{{ $activeLoans }}</div>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 text-center w-36">
                <div class="text-xs text-gray-500">Menunggu Persetujuan</div>
                <div class="text-2xl font-bold text-gray-800">{{ $pendingLoans }}</div>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 text-center w-36">
                <div class="text-xs text-gray-500">Jatuh Tempo (3 hari)</div>
                <div class="text-2xl font-bold text-red-600">{{ $dueSoonLoans }}</div>
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
                                <div class="text-xs text-gray-500 mt-1">Durasi: {{ $trx->duration ?? '-' }} hari &middot; Kembali: {{ $trx->return_date ? $trx->return_date->format('d M Y') : '-' }}</div>
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
</div>
@endsection
