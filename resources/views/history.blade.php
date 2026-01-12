@extends('layout')

@section('title', 'Riwayat Pinjaman')

@section('content')
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">

        {{-- Alert Messages --}}
        @if (session('success'))
            <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-500 flex items-center justify-center">
                        <i class="fa-solid fa-check text-white"></i>
                    </div>
                    <p class="text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 p-4 bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 rounded-xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-red-500 flex items-center justify-center">
                        <i class="fa-solid fa-exclamation-triangle text-white"></i>
                    </div>
                    <p class="text-red-700 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent mb-2">Riwayat Pinjaman</h1>
                <p class="text-sm text-gray-500">Kelola dan export data pinjaman</p>
            </div>
            <div class="flex flex-col md:flex-row gap-3">
                {{-- Filter Status --}}
                <form method="GET" action="{{ route('history.index') }}" class="flex gap-2">
                    <select name="status" onchange="this.form.submit()"
                        class="border border-gray-200 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                        <option value="borrowed" {{ request('status') === 'borrowed' ? 'selected' : '' }}>Sedang Dipinjam</option>
                        <option value="returning" {{ request('status') === 'returning' ? 'selected' : '' }}>Menunggu Pengembalian</option>
                        <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Sudah Dikembalikan</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                    @if(request('status'))
                        <a href="{{ route('history.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200">
                            <i class="fa-solid fa-times"></i>
                        </a>
                    @endif
                </form>
                <button id="exportButton"
                    class="flex items-center space-x-2 bg-gradient-to-r from-green-500 to-emerald-500 text-white px-6 py-3 rounded-xl hover:shadow-lg transition-all font-semibold">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Export ke Excel</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto overflow-y-hidden rounded-xl border border-gray-100 w-full" style="-webkit-overflow-scrolling: touch; max-width:100vw; box-sizing:border-box;">
            <div class="w-full">
                <table id="historyTable" class="min-w-max w-full text-sm text-left">
                <thead class="text-xs uppercase bg-gradient-to-r from-indigo-50 to-purple-50 text-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">No. Pinjaman</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Peminjam</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Barang</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Jumlah Item</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Tanggal Pinjam</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Durasi</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $trx)
                        <tr class="bg-white border-b hover:bg-gradient-to-r hover:from-indigo-50/50 hover:to-purple-50/50 transition-all">
                            <th scope="row" class="px-6 py-4 font-semibold text-indigo-600">
                                {{ $trx->invoice_number }}
                            </th>
                            <td class="px-6 py-4 text-gray-700">{{ $trx->user->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-gray-700">
                                {{ $trx->products->first()->name ?? 'N/A' }}
                                @if($trx->products->count() > 1)
                                    <span class="text-gray-500 text-xs">+{{ $trx->products->count() - 1 }} lainnya</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-700">
                                {{ $trx->products->sum('pivot.quantity') }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusClasses = [
                                        'pending' => 'bg-orange-100 text-orange-700',
                                        'borrowed' => 'bg-indigo-100 text-indigo-700',
                                        'returning' => 'bg-blue-100 text-blue-700',
                                        'returned' => 'bg-green-100 text-green-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Menunggu Persetujuan',
                                        'borrowed' => 'Sedang Dipinjam',
                                        'returning' => 'Menunggu Pengembalian',
                                        'returned' => 'Sudah Dikembalikan',
                                        'cancelled' => 'Dibatalkan',
                                    ];
                                @endphp
                                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $statusClasses[$trx->status] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ $statusLabels[$trx->status] ?? ucfirst($trx->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $trx->borrow_date ? $trx->borrow_date->format('d/m/Y') : $trx->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $trx->duration ?? '-' }} hari
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    <button class="detail-btn px-4 py-2 bg-gradient-to-r from-blue-500 to-cyan-500 text-white rounded-lg hover:shadow-md transition-all text-xs font-semibold"
                                        data-transaction-id="{{ $trx->id }}">
                                        <i class="fa-solid fa-eye mr-1"></i>Detail
                                    </button>

                                    @if (in_array(Auth::user()->role, ['kasir', 'pengelola']) && in_array($trx->status, ['cancelled', 'returned']))
                                        <form action="{{ route('history.destroy', $trx->id) }}" method="POST" class="inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="delete-btn px-4 py-2 bg-gradient-to-r from-red-500 to-rose-500 text-white rounded-lg hover:shadow-md transition-all text-xs font-semibold"
                                                data-invoice="{{ $trx->invoice_number }}">
                                                <i class="fa-solid fa-trash mr-1"></i>Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-20 text-gray-500">
                                <i class="fa-solid fa-inbox text-6xl text-gray-300 mb-4 block"></i>
                                <p class="text-lg font-medium">
                                    @if (request('search'))
                                        Transaksi dengan kata kunci "{{ request('search') }}" tidak ditemukan.
                                    @else
                                        Belum ada riwayat transaksi.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Detail Transaksi --}}
    <div id="detailModal" class="fixed inset-0 bg-black/60 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-10 mx-auto p-6 border max-w-4xl shadow-2xl rounded-2xl bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Detail Transaksi</h3>
                        <p class="text-sm text-gray-500 mt-1">Informasi lengkap transaksi</p>
                    </div>
                    <button id="closeDetailModal" class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-100">
                        <i class="fa-solid fa-times text-2xl"></i>
                    </button>
                </div>

                <div id="detailLoading" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-indigo-200 border-t-indigo-600"></div>
                    <p class="mt-4 text-gray-500 font-medium">Memuat data...</p>
                </div>

                <div id="detailContent" class="hidden">
                    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 p-6 rounded-xl mb-6 border border-indigo-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="mb-4">
                                    <span class="text-xs font-semibold text-gray-500 uppercase">ID Transaksi</span>
                                    <p id="detail-invoice" class="text-lg font-bold text-indigo-600 mt-1"></p>
                                </div>
                                <div class="mb-4">
                                    <span class="text-xs font-semibold text-gray-500 uppercase">Nama Kasir</span>
                                    <p id="detail-cashier" class="text-base text-gray-800 mt-1"></p>
                                </div>
                                <div>
                                    <span class="text-xs font-semibold text-gray-500 uppercase">Tanggal Transaksi</span>
                                    <p id="detail-date" class="text-base text-gray-800 mt-1"></p>
                                </div>
                            </div>
                            <div>
                                <div class="mb-4">
                                    <span class="text-xs font-semibold text-gray-500 uppercase">Status</span>
                                    <p id="detail-payment" class="text-base text-gray-800 mt-1"></p>
                                </div>
                                <div class="mb-4">
                                    <span class="text-xs font-semibold text-gray-500 uppercase">Total Item</span>
                                    <p id="detail-total-items" class="text-base text-gray-800 mt-1"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-lg font-bold text-gray-800 mb-4">Daftar Produk</h4>
                        <div class="overflow-x-auto rounded-xl border border-gray-100">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs uppercase bg-gradient-to-r from-indigo-50 to-purple-50 text-gray-700">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 font-semibold">Nama Produk</th>
                                        <th scope="col" class="px-4 py-3 font-semibold">Kategori</th>
                                        <th scope="col" class="px-4 py-3 text-center font-semibold">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody id="detail-products-list"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="detailError" class="hidden text-center py-12">
                    <div class="text-red-500 mb-4">
                        <i class="fa-solid fa-exclamation-circle text-6xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-2">Terjadi Kesalahan</h4>
                    <p class="text-gray-600" id="error-message"></p>
                </div>

                <div class="mt-6 flex justify-end">
                    <button id="closeDetailModalBtn"
                        class="px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-600 text-white font-semibold rounded-xl hover:shadow-lg transition-all">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Konfirmasi Hapus --}}
    <div id="deleteModal" class="fixed inset-0 bg-black/60 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-6 border w-96 shadow-2xl rounded-2xl bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-xl bg-red-100">
                    <i class="fa-solid fa-exclamation-triangle text-red-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mt-5">Konfirmasi Hapus</h3>
                <div class="mt-4 px-7 py-3">
                    <p class="text-sm text-gray-600">
                        Apakah Anda yakin ingin menghapus transaksi <span id="invoiceNumber" class="font-bold text-gray-800"></span>?
                        <br><br>
                        <span class="text-red-600 font-semibold">Tindakan ini tidak dapat dibatalkan!</span>
                    </p>
                </div>
                <div class="items-center px-4 py-3 flex justify-center space-x-3">
                    <button id="cancelDelete"
                        class="px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-600 text-white font-semibold rounded-xl hover:shadow-lg transition-all">
                        Batal
                    </button>
                    <button id="confirmDelete"
                        class="px-6 py-3 bg-gradient-to-r from-red-500 to-rose-500 text-white font-semibold rounded-xl hover:shadow-lg transition-all">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const exportButton = document.getElementById('exportButton');
            const deleteModal = document.getElementById('deleteModal');
            const detailModal = document.getElementById('detailModal');
            const cancelDeleteBtn = document.getElementById('cancelDelete');
            const confirmDeleteBtn = document.getElementById('confirmDelete');
            const closeDetailModal = document.getElementById('closeDetailModal');
            const closeDetailModalBtn = document.getElementById('closeDetailModalBtn');
            const invoiceNumberSpan = document.getElementById('invoiceNumber');
            let currentForm = null;

            exportButton.addEventListener('click', function() {
                let table = document.getElementById('historyTable');
                let workbook = XLSX.utils.table_to_book(table, { sheet: "Riwayat" });
                let excelBuffer = XLSX.write(workbook, { bookType: 'xlsx', type: 'array' });
                saveAs(new Blob([excelBuffer], { type: "application/octet-stream" }), `Riwayat Pinjaman - {{ date('Y-m-d') }}.xlsx`);
            });

            document.querySelectorAll('.detail-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const transactionId = this.getAttribute('data-transaction-id');
                    showTransactionDetail(transactionId);
                });
            });

            function showTransactionDetail(transactionId) {
                detailModal.classList.remove('hidden');
                document.getElementById('detailLoading').classList.remove('hidden');
                document.getElementById('detailContent').classList.add('hidden');
                document.getElementById('detailError').classList.add('hidden');

                fetch(`{{ url('/history') }}/${transactionId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            populateTransactionDetail(data.data);
                        } else {
                            showDetailError(data.message || 'Terjadi kesalahan saat memuat data');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showDetailError('Gagal memuat data riwayat');
                    })
                    .finally(() => {
                        document.getElementById('detailLoading').classList.add('hidden');
                    });
            }

            function populateTransactionDetail(transaction) {
                document.getElementById('detail-invoice').textContent = transaction.invoice_number;
                document.getElementById('detail-cashier').textContent = transaction.cashier_name;
                document.getElementById('detail-date').textContent = transaction.date;
                document.getElementById('detail-payment').textContent = transaction.status_label || transaction.payment_method || 'N/A';
                document.getElementById('detail-total-items').textContent = transaction.total_items + ' item';

                const productsList = document.getElementById('detail-products-list');
                productsList.innerHTML = '';
                transaction.products.forEach(product => {
                    const row = document.createElement('tr');
                    row.className = 'bg-white border-b hover:bg-gradient-to-r hover:from-indigo-50/50 hover:to-purple-50/50 transition-all';
                    row.innerHTML = `
                        <td class="px-4 py-3 font-medium text-gray-900">${product.name}</td>
                        <td class="px-4 py-3 text-gray-600">${product.category}</td>
                        <td class="px-4 py-3 text-center"><span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full text-xs font-semibold">${product.quantity}</span></td>
                    `;
                    productsList.appendChild(row);
                });

                document.getElementById('detailContent').classList.remove('hidden');
            }

            function showDetailError(message) {
                document.getElementById('error-message').textContent = message;
                document.getElementById('detailError').classList.remove('hidden');
            }

            [closeDetailModal, closeDetailModalBtn].forEach(btn => {
                btn.addEventListener('click', function() {
                    detailModal.classList.add('hidden');
                });
            });

            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    currentForm = this.closest('.delete-form');
                    const invoiceNumber = this.getAttribute('data-invoice');
                    invoiceNumberSpan.textContent = invoiceNumber;
                    deleteModal.classList.remove('hidden');
                });
            });

            cancelDeleteBtn.addEventListener('click', function() {
                deleteModal.classList.add('hidden');
                currentForm = null;
            });

            confirmDeleteBtn.addEventListener('click', function() {
                if (currentForm) {
                    currentForm.submit();
                }
            });

            [detailModal, deleteModal].forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.classList.add('hidden');
                        if (modal === deleteModal) {
                            currentForm = null;
                        }
                    }
                });
            });

            // Use layout search input to filter history table live
            function debounce(fn, wait) {
                let t;
                return function(...args) {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, args), wait);
                };
            }

            (function() {
                const search = document.querySelector('input[name="search"]');
                const tbody = document.querySelector('#historyTable tbody');
                if (!search || !tbody) {
                    // Still run the alert cleanup below
                } else {
                    const rows = Array.from(tbody.querySelectorAll('tr'));

                    function showNoResults(show) {
                        let nr = document.getElementById('no-history-found-row');
                        if (show) {
                            if (!nr) {
                                nr = document.createElement('tr');
                                nr.id = 'no-history-found-row';
                                nr.innerHTML = '<td colspan="7" class="text-center py-20 text-gray-500">Transaksi dengan kata kunci "' + (search.value || '') + '" tidak ditemukan.</td>';
                                tbody.appendChild(nr);
                            } else {
                                // update message to reflect current query
                                nr.querySelector('td').innerHTML = 'Transaksi dengan kata kunci "' + (search.value || '') + '" tidak ditemukan.';
                            }
                        } else if (nr) {
                            nr.remove();
                        }
                    }

                    function applyFilter() {
                        const q = search.value.trim().toLowerCase();
                        let visible = 0;
                        rows.forEach(r => {
                            const colspanTd = r.querySelector('td[colspan]');
                            if (colspanTd) return; // skip placeholder/no-data rows

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

                    const debounced = debounce(applyFilter, 180);
                    search.addEventListener('input', debounced);

                    // Apply initial filter (e.g., when server populated ?search=...)
                    applyFilter();
                }

                // Alert auto-dismiss
                setTimeout(function() {
                    const alerts = document.querySelectorAll('.bg-gradient-to-r');
                    alerts.forEach(alert => {
                        if (alert.classList.contains('from-green-50') || alert.classList.contains('from-red-50')) {
                            alert.style.transition = 'opacity 0.5s';
                            alert.style.opacity = '0';
                            setTimeout(() => alert.remove(), 500);
                        }
                    });
                }, 5000);
            })();
        });
    </script>
@endsection