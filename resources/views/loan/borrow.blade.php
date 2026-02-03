@extends('layout')

@section('title', 'Pinjam Barang')

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

    <div class="max-w-6xl mx-auto">

        <h1
            class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-6">
            <i class="fa-solid fa-shopping-cart mr-3"></i>Pinjam Barang
        </h1>

        {{-- Cart Section --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Keranjang Pinjaman</h2>

            {{-- QR Scanner Button --}}
            <div class="mb-6">
                <button id="qr-scanner-btn"
                    class="w-full bg-gradient-to-r from-blue-500 to-cyan-500 text-white font-semibold py-4 px-6 rounded-xl hover:shadow-lg transition-all flex items-center justify-center gap-3">
                    <i class="fa-solid fa-qrcode text-2xl"></i>
                    <span class="text-lg">Scan QR Code Barang</span>
                </button>
                <p class="mt-3 text-sm text-gray-500">Tip: <strong>Scan QR barang</strong> untuk mengajukan peminjaman
                    barang.</p>
            </div>

            <form id="checkout-form">
                @csrf
                <input type="hidden" name="cart" id="cart-input">

                <div id="cart-items" class="space-y-3 mb-6"></div>

                <button type="submit" id="checkout-btn"
                    class="w-full bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-bold py-3 rounded-xl hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                    <i class="fa-solid fa-check mr-2"></i>Ajukan Pinjam (Semua Item)
                </button>
            </form>
        </div>

        {{-- Pending Loans Section --}}
        @if ($pendingLoans && $pendingLoans->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mt-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Peminjaman Menunggu Persetujuan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($pendingLoans as $loan)
                        <div
                            class="border border-yellow-200 rounded-xl p-4 hover:shadow-md transition-all bg-gradient-to-br from-yellow-50 to-white">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex-1">
                                    <h3 class="font-bold text-gray-800 mb-1">
                                        {{ $loan->products->first()->name ?? 'Barang' }}
                                        @if($loan->products->count() > 1)
                                            <span class="text-xs text-gray-500">+{{ $loan->products->count() - 1 }} lainnya</span>
                                        @endif
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-semibold">No. Pengajuan:</span> {{ $loan->invoice_number }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-semibold">Tanggal Ajuan:</span>
                                        {{ $loan->created_at->format('d/m/Y H:i') }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-semibold">Durasi:</span>
                                        @if($loan->duration == 0)
                                            <span class="text-purple-600 font-semibold">Permanen</span>
                                        @else
                                            {{ $loan->duration }} hari
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-semibold">Alasan:</span> {{ $loan->borrow_reason }}
                                    </p>
                                    <p class="text-sm text-gray-600 mt-2">
                                        <span class="font-semibold">Jumlah Item:</span> {{ $loan->products->sum('pivot.quantity') }}
                                    </p>

                                    <div class="mt-3">
                                        <ul class="text-sm text-gray-700 space-y-1">
                                            @foreach($loan->products as $p)
                                                <li>{{ $p->name }} &times; <strong>{{ $p->pivot->quantity }}</strong></li>
                                            @endforeach
                                        </ul>
                                    </div>

                                    <div class="mt-4">
                                        <button type="button"
                                            class="w-full cancel-pending-loan-btn px-4 py-2 rounded-xl bg-red-100 text-red-600 hover:bg-red-200 transition-all font-semibold text-sm"
                                            data-loan-id="{{ $loan->id }}" data-invoice="{{ $loan->invoice_number }}">
                                            <i class="fa-solid fa-times mr-1"></i>Batalkan Pengajuan
                                        </button>
                                    </div>
                                </div>
                                <div class="ml-2">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                        <i class="fa-solid fa-clock mr-1"></i>Menunggu
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Card --}}

    {{-- QR Scanner Modal --}}
    <div id="qr-scanner-modal" class="fixed inset-0 bg-black/70 z-50 hidden">
        <div
            class="fixed inset-4 md:inset-auto md:top-1/2 md:left-1/2 md:transform md:-translate-x-1/2 md:-translate-y-1/2 bg-white rounded-2xl p-6 max-w-lg w-full">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800">Scan QR Code Barang</h2>
                <button id="close-qr-scanner" class="text-gray-500 hover:text-gray-700">
                    <i class="fa-solid fa-times text-2xl"></i>
                </button>
            </div>

            <div id="qr-reader" class="w-full"></div>

            <div class="mt-4 text-center text-sm text-gray-600">
                <p>Arahkan kamera ke QR code barang yang ingin dipinjam</p>
            </div>
        </div>
    </div>

    {{-- Borrow Checkout Modal --}}
    <div id="borrow-modal" class="fixed inset-0 bg-black/50 z-50 hidden">
        <div
            class="fixed inset-4 md:inset-auto md:top-1/2 md:left-1/2 transform md:-translate-x-1/2 md:-translate-y-1/2 bg-white rounded-2xl p-6 w-full max-w-lg">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800">Ajukan Pinjam (Keranjang)</h2>
                <button id="borrow-cancel-btn" class="text-gray-500 hover:text-gray-700"><i
                        class="fa-solid fa-times text-2xl"></i></button>
            </div>

            <form id="borrow-modal-form" class="space-y-4" onsubmit="return false;">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Tipe Peminjaman</label>
                    <div class="space-y-3">
                        <label
                            class="flex items-center p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-all">
                            <input type="radio" name="borrow-type" value="temporary" checked
                                class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                            <div class="ml-3 flex-1">
                                <span class="font-semibold text-gray-800">Pinjaman Sementara</span>
                                <p class="text-xs text-gray-500">Barang akan dikembalikan sesuai durasi</p>
                            </div>
                        </label>
                        <label
                            class="flex items-center p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-all">
                            <input type="radio" name="borrow-type" value="permanent"
                                class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                            <div class="ml-3 flex-1">
                                <span class="font-semibold text-gray-800">Pinjaman Permanen</span>
                                <p class="text-xs text-gray-500">Barang tidak perlu dikembalikan</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div id="duration-field">
                    <label for="borrow-duration" class="block text-sm font-medium text-gray-700 mb-2">Durasi Peminjaman
                        (hari)</label>
                    <input id="borrow-duration" type="number" min="1" value="3" required
                        class="block w-full border border-gray-200 rounded-xl shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="borrow-reason" class="block text-sm font-medium text-gray-700 mb-2">Alasan
                        Peminjaman</label>
                    <textarea id="borrow-reason" rows="4" required
                        class="block w-full border border-gray-200 rounded-xl shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Contoh: Kebutuhan tugas kuliah..."></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" id="borrow-cancel-btn-2"
                        class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200">Batal</button>
                    <button type="button" id="borrow-confirm-btn"
                        class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-500 text-white">Ajukan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Cancel Borrow Modal --}}
    <div id="cancel-borrow-modal" class="fixed inset-0 bg-black/50 z-50 hidden">
        <div
            class="fixed inset-4 md:inset-auto md:top-1/2 md:left-1/2 transform md:-translate-x-1/2 md:-translate-y-1/2 bg-white rounded-2xl p-6 w-full max-w-md">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800">Konfirmasi Pembatalan</h2>
                <button id="cancel-modal-close-btn" class="text-gray-500 hover:text-gray-700"><i
                        class="fa-solid fa-times text-2xl"></i></button>
            </div>

            <div class="mb-6">
                <p class="text-gray-600 mb-4">Apakah Anda yakin ingin membatalkan pengajuan peminjaman ini?</p>
                <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-xl p-4">
                    <p class="text-sm font-semibold text-yellow-800">
                        <i class="fa-solid fa-exclamation-triangle mr-2"></i>
                        Aksi ini tidak dapat dibatalkan. Barang akan dikembalikan ke stok normal.
                    </p>
                </div>
            </div>

            <div id="cancel-loan-invoice" class="mb-4 text-sm">
                <span class="text-gray-600">Invoice:</span>
                <span id="cancel-loan-invoice-number" class="font-bold text-gray-800"></span>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" id="cancel-modal-cancel-btn"
                    class="px-6 py-2 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 font-semibold">Batal</button>
                <button type="button" id="cancel-modal-confirm-btn"
                    class="px-6 py-2 rounded-xl bg-gradient-to-r from-red-500 to-rose-500 text-white hover:shadow-lg font-semibold">Batalkan
                    Pinjaman</button>
            </div>
        </div>
    </div>

    {{-- QR Code Scanner Library --}}
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const qrScannerBtn = document.getElementById('qr-scanner-btn');
            const qrScannerModal = document.getElementById('qr-scanner-modal');
            const closeQrScanner = document.getElementById('close-qr-scanner');
            const productInfo = document.getElementById('product-info');
            const borrowForm = document.getElementById('borrow-form');
            let html5QrCode = null;

            // Cancel Pending Loan Modal
            const cancelBorrowModal = document.getElementById('cancel-borrow-modal');
            const cancelModalCloseBtn = document.getElementById('cancel-modal-close-btn');
            const cancelModalCancelBtn = document.getElementById('cancel-modal-cancel-btn');
            const cancelModalConfirmBtn = document.getElementById('cancel-modal-confirm-btn');
            let selectedLoanId = null;

            function openCancelBorrowModal(loanId, invoiceNumber) {
                selectedLoanId = loanId;
                document.getElementById('cancel-loan-invoice-number').textContent = invoiceNumber;
                cancelBorrowModal.classList.remove('hidden');
            }

            function closeCancelBorrowModal() {
                cancelBorrowModal.classList.add('hidden');
                selectedLoanId = null;
            }

            // Attach handlers to cancel buttons
            document.querySelectorAll('.cancel-pending-loan-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const loanId = btn.dataset.loanId;
                    const invoiceNumber = btn.dataset.invoice;
                    openCancelBorrowModal(loanId, invoiceNumber);
                });
            });

            if (cancelModalCloseBtn) {
                cancelModalCloseBtn.addEventListener('click', closeCancelBorrowModal);
            }

            if (cancelModalCancelBtn) {
                cancelModalCancelBtn.addEventListener('click', closeCancelBorrowModal);
            }

            // Handle cancel confirmation
            if (cancelModalConfirmBtn) {
                cancelModalConfirmBtn.addEventListener('click', async () => {
                    if (!selectedLoanId) return;

                    try {
                        const token = '{{ csrf_token() }}';
                        const res = await fetch(`/borrow/${selectedLoanId}/cancel`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token
                            }
                        });

                        const data = await res.json();

                        if (res.ok && data.success) {
                            showNotification('Peminjaman berhasil dibatalkan');
                            closeCancelBorrowModal();
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            showNotification(data.message || 'Gagal membatalkan peminjaman', 'error');
                        }
                    } catch (e) {
                        console.error(e);
                        showNotification('Terjadi kesalahan saat membatalkan peminjaman', 'error');
                    }
                });
            }

            // Close modal on Escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && cancelBorrowModal && !cancelBorrowModal.classList.contains('hidden')) {
                    closeCancelBorrowModal();
                }
            });

            // QR Scanner Functions
            if (qrScannerBtn && qrScannerModal) {
                qrScannerBtn.addEventListener('click', function () {
                    qrScannerModal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                    startQrScanner();
                });
            }

            if (closeQrScanner && qrScannerModal) {
                closeQrScanner.addEventListener('click', function () {
                    stopQrScanner();
                    qrScannerModal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                });
            }

            if (qrScannerModal) {
                qrScannerModal.addEventListener('click', function (e) {
                    if (e.target === qrScannerModal) {
                        stopQrScanner();
                        qrScannerModal.classList.add('hidden');
                        document.body.style.overflow = 'auto';
                    }
                });
            }

            function startQrScanner() {
                html5QrCode = new Html5Qrcode("qr-reader");

                html5QrCode.start({
                    facingMode: "environment"
                }, {
                    fps: 10,
                    qrbox: {
                        width: 250,
                        height: 250
                    }
                },
                    (decodedText, decodedResult) => {
                        try {
                            console.debug('QR decoded:', decodedText);

                            // Try parse as URL first
                            let productId = null;
                            try {
                                const url = new URL(decodedText);

                                // If the QR points to a product (contains qr_product) -> add to cart
                                productId = url.searchParams.get('qr_product');

                                // If URL is a segment return path, navigate there
                                if ((url.origin === window.location.origin && url.pathname.includes('/return/segment')) || url.pathname.includes('/return/segment')) {
                                    stopQrScanner();
                                    qrScannerModal.classList.add('hidden');
                                    document.body.style.overflow = 'auto';
                                    window.location.href = url.href;
                                    return;
                                }
                            } catch (urlErr) {
                                // Not a URL — maybe the QR encodes a raw product id or JSON
                                const raw = decodedText.trim();
                                // If raw is numeric, treat as product ID
                                if (/^\d+$/.test(raw)) {
                                    productId = raw;
                                } else {
                                    // If JSON with product_id
                                    try {
                                        const parsed = JSON.parse(raw);
                                        if (parsed && (parsed.product_id || parsed.id)) {
                                            productId = parsed.product_id || parsed.id;
                                        }
                                    } catch (jsErr) {
                                        // ignore
                                    }
                                }
                            }

                            if (productId) {
                                // Add to cart (supports multiple items)
                                fetchAndAddProductFromQR(productId);
                                stopQrScanner();
                                qrScannerModal.classList.add('hidden');
                                document.body.style.overflow = 'auto';
                                return;
                            }

                            showNotification('QR Code tidak valid atau tidak terkait dengan produk!', 'error');
                        } catch (e) {
                            console.error('Scanner error:', e);
                            showNotification('Terjadi kesalahan saat memproses QR Code.', 'error');
                        }
                    },
                    (errorMessage) => {
                        // Scanning error, ignore
                    }
                ).catch((err) => {
                    console.error('Error starting QR scanner:', err);
                    showNotification('Gagal mengakses kamera!', 'error');
                });
            }

            function stopQrScanner() {
                if (html5QrCode) {
                    html5QrCode.stop().then(() => {
                        html5QrCode.clear();
                        html5QrCode = null;
                    }).catch((err) => {
                        console.error('Error stopping scanner:', err);
                    });
                }
            }

            function fetchAndLoadProduct(productId) {
                fetch(`/api/product/${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const product = data.product;

                            if (product.available_stock <= 0) {
                                showNotification('Barang tidak tersedia untuk dipinjam!', 'error');
                                return;
                            }

                            // Set product ID
                            document.getElementById('product-id').value = product.id;

                            // Show product info
                            document.getElementById('product-name').textContent = product.name;
                            document.getElementById('product-category').textContent = product.category || 'Tidak ada kategori';
                            document.getElementById('product-available').textContent = product.available_stock + ' unit';

                            // Set product image
                            const productImageDiv = document.getElementById('product-image');
                            if (product.image_path) {
                                productImageDiv.innerHTML = `<img src="${product.image_path}" alt="${product.name}" class="w-32 h-32 rounded-xl object-cover shadow-md">`;
                            }

                            // Show product info and form
                            productInfo.classList.remove('hidden');
                            borrowForm.classList.remove('hidden');

                            // Scroll to form
                            borrowForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

                            showNotification('Produk berhasil dimuat!');
                        } else {
                            showNotification('Produk tidak ditemukan!', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Gagal memuat produk!', 'error');
                    });
            }

            // Check if QR product parameter exists in URL (for direct QR code scan)
            const urlParams = new URLSearchParams(window.location.search);
            const qrProductId = urlParams.get('qr_product');
            if (qrProductId) {
                // Add scanned product to cart instead of forcing single-product flow
                fetchAndAddProductFromQR(qrProductId);
                // Remove the parameter from URL
                urlParams.delete('qr_product');
                const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
                window.history.replaceState({}, '', newUrl);
            }

            function showNotification(message, type = 'success') {
                const notification = document.createElement('div');
                notification.className =
                    `fixed top-4 right-4 z-50 p-4 rounded-xl shadow-lg ${type === 'success' ? 'bg-gradient-to-r from-green-500 to-emerald-500' : 'bg-gradient-to-r from-red-500 to-rose-500'} text-white transform translate-x-full transition-transform duration-300`;
                notification.innerHTML = `
                                    <div class="flex items-center space-x-3">
                                        <i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'} text-xl"></i>
                                        <span class="font-medium">${message}</span>
                                    </div>
                                `;

                document.body.appendChild(notification);

                setTimeout(() => notification.classList.remove('translate-x-full'), 100);
                setTimeout(() => {
                    notification.classList.add('translate-x-full');
                    setTimeout(() => document.body.removeChild(notification), 300);
                }, 3000);
            }

            // Cart functionality (persisted in localStorage)
            const CART_KEY = 'borrow_cart';
            let cart = [];

            function loadCart() {
                try {
                    const raw = localStorage.getItem(CART_KEY);
                    cart = raw ? JSON.parse(raw) : [];
                } catch (e) {
                    cart = [];
                }
            }

            function saveCart() {
                localStorage.setItem(CART_KEY, JSON.stringify(cart));
                renderCart();
            }

            function renderCart() {
                const cartItemsContainer = document.getElementById('cart-items');
                const checkoutBtn = document.getElementById('checkout-btn');
                const desktopCartCount = document.getElementById('desktop-cart-count');

                const totalCount = cart.reduce((s, it) => s + (it.quantity || 1), 0);
                if (desktopCartCount) desktopCartCount.textContent = totalCount;

                if (cartItemsContainer) {
                    cartItemsContainer.innerHTML = '';
                    if (cart.length === 0) {
                        cartItemsContainer.innerHTML = '<p>Keranjang masih kosong</p>';
                    } else {
                        cart.forEach(item => {
                            const node = document.createElement('div');
                            node.className = 'cart-item flex justify-between items-center bg-gradient-to-r from-gray-50 to-indigo-50 p-3 rounded-xl border border-gray-100';
                            node.innerHTML = `
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-semibold text-gray-800 text-sm truncate">${item.name}</p>
                                                    <p class="text-xs text-gray-500">Qty: ${item.quantity || 1}</p>
                                                </div>
                                                <div class="flex items-center space-x-2 ml-2">
                                                    <button type="button" class="cart-quantity-btn w-8 h-8 flex items-center justify-center text-indigo-600 bg-white rounded-lg border hover:border-indigo-400 transition-all" data-id="${item.id}" data-action="decrease">
                                                        <i class="fa-solid fa-minus text-xs"></i>
                                                    </button>
                                                    <span class="font-bold text-sm min-w-[2rem] text-center text-indigo-600">${item.quantity || 1}</span>
                                                    <button type="button" class="cart-quantity-btn w-8 h-8 flex items-center justify-center text-indigo-600 bg-white rounded-lg border hover:border-indigo-400 transition-all" data-id="${item.id}" data-action="increase">
                                                        <i class="fa-solid fa-plus text-xs"></i>
                                                    </button>
                                                    <button type="button" class="cart-remove-btn text-red-500 hover:text-red-700 ml-1 w-8 h-8 flex items-center justify-center bg-red-50 rounded-lg hover:bg-red-100 transition-all" data-id="${item.id}">
                                                        <i class="fa-solid fa-trash text-xs"></i>
                                                    </button>
                                                </div>
                                            `;
                            cartItemsContainer.appendChild(node);
                        });

                        // attach handlers
                        cartItemsContainer.querySelectorAll('.cart-remove-btn').forEach(b => b.addEventListener('click', (e) => {
                            const id = b.dataset.id;
                            cart = cart.filter(i => i.id != id);
                            saveCart();
                            showNotification('Item dihapus dari keranjang');
                        }));

                        cartItemsContainer.querySelectorAll('.cart-quantity-btn').forEach(b => b.addEventListener('click', (e) => {
                            const id = b.dataset.id;
                            const action = b.dataset.action;
                            const item = cart.find(i => i.id == id);
                            if (!item) return;
                            if (action === 'increase') {
                                if (item.stock && item.quantity >= item.stock) {
                                    showNotification('Melebihi stok tersedia!', 'error');
                                    return;
                                }
                                item.quantity = (item.quantity || 1) + 1;
                            } else {
                                if ((item.quantity || 1) <= 1) {
                                    cart = cart.filter(i => i.id != id);
                                } else {
                                    item.quantity = (item.quantity || 1) - 1;
                                }
                            }
                            saveCart();
                        }));
                    }
                }

                // update checkout button
                const isEmpty = cart.length === 0;
                if (checkoutBtn) {
                    checkoutBtn.disabled = isEmpty;
                    checkoutBtn.innerHTML = isEmpty ? '<i class="fa-solid fa-ban mr-2"></i>Keranjang Kosong' : '<i class="fa-solid fa-check mr-2"></i>Ajukan Pinjam (Semua Item)';
                }
            }

            function addToCart(item) {
                const existing = cart.find(i => i.id == item.id);
                if (existing) {
                    if (item.stock && existing.quantity >= item.stock) {
                        showNotification('Melebihi stok tersedia!', 'error');
                        return;
                    }
                    existing.quantity = (existing.quantity || 1) + 1;
                } else {
                    cart.push({ id: item.id, name: item.name, quantity: 1, stock: item.stock || null });
                }
                saveCart();
                showNotification(`${item.name} ditambahkan ke keranjang`);
            }

            // wire up add to cart
            document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const card = btn.closest('.product-card');
                    if (!card) return;
                    const id = card.dataset.id;
                    const name = card.dataset.name;
                    const stock = parseInt(card.dataset.stock || '0', 10);
                    addToCart({ id, name, stock });
                });
            });

            // fetch product and add to cart from QR result
            async function fetchAndAddProductFromQR(productId) {
                try {
                    const res = await fetch(`/api/product/${productId}`);
                    const j = await res.json();
                    if (j.success) {
                        const product = j.product;
                        if (product.stock <= 0) {
                            showNotification('Produk ini stoknya habis!', 'error');
                            return;
                        }
                        addToCart({ id: product.id, name: product.name, stock: product.stock });
                        showNotification(`${product.name} ditambahkan ke keranjang dari QR!`);
                    } else {
                        showNotification('Produk tidak ditemukan!', 'error');
                    }
                } catch (e) {
                    console.error(e);
                    showNotification('Gagal memuat produk!', 'error');
                }
            }

            // cart action handlers
            document.addEventListener('click', function (e) {
                const target = e.target.closest('.cart-quantity-btn, .cart-remove-btn');
                if (!target) return;
                // handled inside renderCart via delegation already for newly created nodes
            });

            // mobile cart toggle
            // const mobileCartToggle = document.getElementById('mobile-cart-toggle');
            // const mobileCartModal = document.getElementById('mobile-cart-modal');
            // const closeMobileCart = document.getElementById('close-mobile-cart');
            // if (mobileCartToggle && mobileCartModal) {
            //     mobileCartToggle.addEventListener('click', () => {
            //         mobileCartModal.classList.remove('hidden');
            //         document.body.style.overflow = 'hidden';
            //     });
            // }
            // if (closeMobileCart && mobileCartModal) {
            //     closeMobileCart.addEventListener('click', () => {
            //         mobileCartModal.classList.add('hidden');
            //         document.body.style.overflow = 'auto';
            //     });
            // }

            // checkout flow: submit per-item to loan.submit
            async function handleBulkCheckout(duration, borrowReason) {
                const token = '{{ csrf_token() }}';
                let successUnits = 0; // count units (sum of quantities) successfully submitted
                let errorMessages = []; // collect error messages

                for (const item of cart) {
                    try {
                        const res = await fetch('{{ route('loan.submit') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({ product_id: item.id, duration: duration, borrow_reason: borrowReason, quantity: item.quantity || 1 })
                        });
                        const j = await res.json().catch(() => null);
                        if (res.ok && (j === null || j.success !== false)) {
                            successUnits += (item.quantity || 1);
                        } else {
                            console.error('Failed to submit borrow for', item, j);
                            const errorMsg = j && j.message ? j.message : `Gagal meminjam ${item.name}`;
                            errorMessages.push(errorMsg);
                        }
                    } catch (e) {
                        console.error(e);
                        errorMessages.push(`Error untuk ${item.name}: ${e.message}`);
                    }
                }

                if (successUnits > 0) {
                    showNotification(`${successUnits} item berhasil diajukan untuk pinjam`);
                    localStorage.removeItem(CART_KEY);
                    cart = [];
                    renderCart();
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    // Show specific error if available, otherwise generic message
                    const errorMsg = errorMessages.length > 0
                        ? errorMessages[0]
                        : 'Gagal mengajukan peminjaman. Coba lagi.';
                    showNotification(errorMsg, 'error');
                }
            }

            // intercept checkout form submit (desktop only)
            const checkoutForm = document.getElementById('checkout-form');
            const checkoutBtn = document.getElementById('checkout-btn');

            // Modal-based checkout
            const borrowModal = document.getElementById('borrow-modal');
            const borrowDuration = document.getElementById('borrow-duration');
            const borrowReasonInput = document.getElementById('borrow-reason');
            const borrowConfirmBtn = document.getElementById('borrow-confirm-btn');
            const borrowCancelBtn = document.getElementById('borrow-cancel-btn');
            const borrowCancelBtn2 = document.getElementById('borrow-cancel-btn-2');

            function openBorrowModal() {
                borrowDuration.value = '7';
                borrowReasonInput.value = '';
                // Reset to temporary borrow type
                const tempRadio = document.querySelector('input[name="borrow-type"][value="temporary"]');
                if (tempRadio) tempRadio.checked = true;
                // Show duration field
                const durationField = document.getElementById('duration-field');
                if (durationField) durationField.style.display = 'block';
                borrowDuration.required = true;
                borrowModal.classList.remove('hidden');
                setTimeout(() => borrowDuration.focus(), 50);
            }

            function closeBorrowModal() {
                borrowModal.classList.add('hidden');
            }

            // Handle borrow type change
            const borrowTypeRadios = document.querySelectorAll('input[name="borrow-type"]');
            const durationField = document.getElementById('duration-field');

            borrowTypeRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    if (this.value === 'permanent') {
                        // Hide duration field for permanent borrow
                        durationField.style.display = 'none';
                        borrowDuration.required = false;
                    } else {
                        // Show duration field for temporary borrow
                        durationField.style.display = 'block';
                        borrowDuration.required = true;
                    }
                });
            });

            if (checkoutForm) {
                checkoutForm.addEventListener('submit', (e) => {
                    e.preventDefault();
                    if (cart.length === 0) {
                        showNotification('Keranjang Anda kosong!', 'error');
                        return;
                    }
                    openBorrowModal();
                });
            }

            // Modal confirm/cancel handlers
            if (borrowConfirmBtn) {
                borrowConfirmBtn.addEventListener('click', () => {
                    const borrowType = document.querySelector('input[name="borrow-type"]:checked');
                    const isPermanent = borrowType && borrowType.value === 'permanent';

                    let duration;
                    if (isPermanent) {
                        // Set duration to 0 for permanent borrow
                        duration = '0';
                    } else {
                        // Validate duration for temporary borrow
                        duration = borrowDuration.value ? borrowDuration.value.trim() : '';
                        if (!duration || isNaN(duration) || parseInt(duration) < 1) {
                            showNotification('Durasi harus berupa angka minimal 1 hari.', 'error');
                            borrowDuration.focus();
                            return;
                        }
                    }

                    const borrowReason = borrowReasonInput.value ? borrowReasonInput.value.trim() : '';
                    if (!borrowReason) {
                        showNotification('Alasan peminjaman harus diisi!', 'error');
                        borrowReasonInput.focus();
                        return;
                    }

                    closeBorrowModal();
                    handleBulkCheckout(duration, borrowReason);
                });
            }

            [borrowCancelBtn, borrowCancelBtn2].forEach(btn => {
                if (btn) btn.addEventListener('click', closeBorrowModal);
            });

            // Close modal on Escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && borrowModal && !borrowModal.classList.contains('hidden')) {
                    closeBorrowModal();
                }
            });

            // init cart
            loadCart();
            renderCart();

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
        });
    </script>
@endsection