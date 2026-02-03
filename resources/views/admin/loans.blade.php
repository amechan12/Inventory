@extends('layout')

@section('title', 'Kelola Pinjaman')

@section('content')
    {{-- Success/Error Messages --}}
    @if (session('success'))
        <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-xl" role="alert">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-500 flex items-center justify-center">
                    <i class="fa-solid fa-check text-white"></i>
                </div>
                <div>
                    <p class="font-bold text-green-800">Sukses!</p>
                    <p class="text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 rounded-xl" role="alert">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-500 flex items-center justify-center">
                    <i class="fa-solid fa-exclamation-triangle text-white"></i>
                </div>
                <div>
                    <p class="font-bold text-red-800">Error!</p>
                    <p class="text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="mb-6">
        <h1
            class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
            <i class="fa-solid fa-clipboard-list mr-3"></i>Kelola Pinjaman
        </h1>
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Menunggu Persetujuan</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['pending_count'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center">
                    <i class="fa-solid fa-clock text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Menunggu Pengembalian</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['returning_count'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fa-solid fa-rotate-left text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Sedang Dipinjam</p>
                    <p class="text-2xl font-bold" style="color: #0078fe;">{{ $stats['active_loans'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-full flex items-center justify-center"
                    style="background-color: rgba(0,120,254,0.1);">
                    <i class="fa-solid fa-box text-xl" style="color: #0078fe;"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Item Dipinjam</p>
                    <p class="text-2xl font-bold" style="color: #0078fe;">{{ $stats['total_items_borrowed'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                    <i class="fa-solid fa-boxes text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Pending Loans Section --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-clock text-orange-500"></i>
                Menunggu Persetujuan ({{ $pendingLoans->count() }})
            </h2>

            @if($pendingLoans->count() > 0)
                <form id="approveAllForm" action="{{ route('admin.loans.approveAll') }}" method="POST"
                    onsubmit="return confirm('Yakin setujui semua pengajuan peminjaman? Pastikan stok cukup.');">
                    @csrf
                    <button type="submit" id="approveAllBtn"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-sm hover:shadow-lg">
                        <i class="fa-solid fa-check mr-1"></i> Setujui Semua
                    </button>
                </form>
            @endif
        </div>

        @if($pendingLoans->count() > 0)
            <div class="space-y-4">
                @foreach($pendingLoans as $loan)
                    <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-all">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-start gap-4">
                                    <div class="flex-1">
                                        <h3 class="font-bold text-gray-800 mb-2">
                                            {{ $loan->products->first()->name }}
                                            @if($loan->products->count() > 1)
                                                <span class="text-xs text-gray-500">+{{ $loan->products->count() - 1 }} lainnya</span>
                                            @endif
                                        </h3>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-sm text-gray-600">
                                            <div>
                                                <span class="font-semibold">No. Pinjaman:</span> {{ $loan->invoice_number }}
                                            </div>
                                            <div>
                                                <span class="font-semibold">Peminjam:</span> {{ $loan->user->name }}
                                            </div>
                                            <div>
                                                <span class="font-semibold">Jumlah Item:</span>
                                                {{ $loan->products->sum(function ($p) {
                        return $p->pivot->quantity; }) }}
                                            </div>
                                            <div>
                                                <span class="font-semibold">Durasi:</span>
                                                @if($loan->duration == 0)
                                                    <span class="text-purple-600 font-semibold">Permanen</span>
                                                @else
                                                    {{ $loan->duration }} hari
                                                @endif
                                            </div>
                                            <div>
                                                <span class="font-semibold">Tanggal Pengajuan:</span>
                                                {{ $loan->created_at->format('d/m/Y H:i') }}
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="font-semibold text-gray-700">Alasan:</span>
                                            <p class="text-gray-600 mt-1">{{ $loan->borrow_reason }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2">
                                <form action="{{ route('admin.loans.approve', $loan->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $loan->products->first()->id }}">
                                    <button type="submit"
                                        class="w-full bg-gradient-to-r from-green-500 to-emerald-500 text-white font-semibold py-2 px-4 rounded-lg hover:shadow-lg transition-all flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-check"></i>
                                        <span>Approve</span>
                                    </button>
                                </form>
                                <form action="{{ route('admin.loans.reject', $loan->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="w-full bg-gradient-to-r from-red-500 to-rose-500 text-white font-semibold py-2 px-4 rounded-lg hover:shadow-lg transition-all flex items-center justify-center gap-2"
                                        onclick="return confirm('Yakin ingin menolak peminjaman ini?')">
                                        <i class="fa-solid fa-xmark"></i>
                                        <span>Tolak</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <i class="fa-solid fa-check-circle text-4xl text-gray-300 mb-2"></i>
                <p>Tidak ada pengajuan peminjaman yang menunggu persetujuan</p>
            </div>
        @endif
    </div>

    {{-- Returning Loans Section --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-rotate-left text-blue-500"></i>
            Menunggu Konfirmasi Pengembalian ({{ $returningLoans->count() }})
        </h2>

        @if($returningLoans->count() > 0)
            <div class="space-y-4">
                @foreach($returningLoans as $loan)
                    <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-all">
                        <div class="flex flex-col gap-4">
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-800 mb-2">
                                    {{ $loan->products->first()->name }}
                                    @if($loan->products->count() > 1)
                                        <span class="text-xs text-gray-500">+{{ $loan->products->count() - 1 }} lainnya</span>
                                    @endif
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-600 mb-3">
                                    <div>
                                        <span class="font-semibold">No. Pinjaman:</span> {{ $loan->invoice_number }}
                                    </div>
                                    <div>
                                        <span class="font-semibold">Peminjam:</span> {{ $loan->user->name }}
                                    </div>
                                    <div>
                                        <span class="font-semibold">Jumlah Item:</span>
                                        {{ $loan->products->sum(function ($p) {
                        return $p->pivot->quantity; }) }}
                                    </div>
                                    <div>
                                        <span class="font-semibold">Tanggal Pinjam:</span> {{ $loan->borrow_date->format('d/m/Y') }}
                                    </div>
                                </div>

                                <div class="mt-2 text-sm text-gray-700">
                                    <ul class="space-y-1">
                                        @foreach($loan->products as $p)
                                            <li>{{ $p->name }} &times; <strong>{{ $p->pivot->quantity }}</strong></li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>

                            {{-- Confirm Return Form --}}
                            <form action="{{ route('admin.loans.confirm-return', $loan->id) }}" method="POST" class="border-t pt-4">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fa-solid fa-clipboard-check mr-2"></i>Kondisi Barang
                                        </label>
                                        <select name="condition_on_return" required
                                            class="block w-full border border-gray-200 rounded-xl shadow-sm py-2.5 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <option value="good">Baik (Tidak ada kerusakan)</option>
                                            <option value="damaged">Rusak (Ada kerusakan)</option>
                                            <option value="lost">Hilang</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fa-solid fa-comment-dots mr-2"></i>Catatan (Opsional)
                                        </label>
                                        <textarea name="return_notes" rows="2"
                                            class="block w-full border border-gray-200 rounded-xl shadow-sm py-2.5 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            placeholder="Catatan tentang kondisi barang..."></textarea>
                                    </div>
                                    <button type="submit"
                                        class="w-full bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-semibold py-3 px-4 rounded-lg hover:shadow-lg transition-all flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-check-circle"></i>
                                        <span>Konfirmasi Pengembalian</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <i class="fa-solid fa-check-circle text-4xl text-gray-300 mb-2"></i>
                <p>Tidak ada pengembalian yang menunggu konfirmasi</p>
            </div>
        @endif
    </div>

    <script>
        // Auto-hide alerts
        setTimeout(function () {
            const alerts = document.querySelectorAll('.bg-gradient-to-r');
            alerts.forEach(alert => {
                if (alert.classList.contains('from-green-50') || alert.classList.contains('from-red-50')) {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            });
        }, 5000);
    </script>
@endsection