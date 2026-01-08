@extends('layout')

@section('title', 'Pinjam Barang')

@section('content')
    {{-- Success/Error Messages --}}
    @if (session('success'))
        <div class="mb-6 p-4 bg-linear-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-xl" role="alert">
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
        <div class="mb-6 p-4 bg-linear-to-r from-red-50 to-rose-50 border-l-4 border-red-500 rounded-xl" role="alert">
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

    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
            <h1 class="text-2xl md:text-3xl font-bold bg-linear-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-6">
                <i class="fa-solid fa-box mr-3"></i>Pinjam Barang
            </h1>

            {{-- QR Scanner Section --}}
            <div class="mb-8">
                <button id="qr-scanner-btn" class="w-full bg-linear-to-r from-blue-500 to-cyan-500 text-white font-semibold py-4 px-6 rounded-xl hover:shadow-lg transition-all flex items-center justify-center gap-3">
                    <i class="fa-solid fa-qrcode text-2xl"></i>
                    <span class="text-lg">Scan QR Code Barang</span>
                </button>
            </div>

            {{-- Product Info Section --}}
            <div id="product-info" class="hidden mb-8">
                <div class="bg-linear-to-br from-indigo-50 to-purple-50 rounded-xl p-6 border border-indigo-100">
                    <div class="flex flex-col md:flex-row gap-6">
                        <div id="product-image" class="shrink-0">
                            <div class="w-32 h-32 rounded-xl bg-white flex items-center justify-center shadow-md">
                                <i class="fa-solid fa-image text-4xl text-gray-300"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h2 id="product-name" class="text-xl font-bold text-gray-800 mb-2"></h2>
                            <div class="flex flex-wrap gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500">Kategori:</span>
                                    <span id="product-category" class="font-semibold text-gray-800 ml-2"></span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Stok Tersedia:</span>
                                    <span id="product-available" class="font-semibold text-green-600 ml-2"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Borrow Form --}}
            <form id="borrow-form" action="{{ route('loan.submit') }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="product_id" id="product-id">

                <div class="space-y-6">
                    <div>
                        <label for="duration" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fa-solid fa-calendar-days mr-2"></i>Durasi Peminjaman (hari)
                        </label>
                        <input type="number" name="duration" id="duration" min="1" max="365" required
                            class="block w-full border border-gray-200 rounded-xl shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Contoh: 7 (untuk 7 hari)">
                        <p class="mt-1 text-sm text-gray-500">Masukkan jumlah hari peminjaman (maksimal 365 hari)</p>
                    </div>

                    <div>
                        <label for="borrow_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fa-solid fa-comment-dots mr-2"></i>Alasan Peminjaman
                        </label>
                        <textarea name="borrow_reason" id="borrow_reason" rows="4" required
                            class="block w-full border border-gray-200 rounded-xl shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Jelaskan alasan Anda meminjam barang ini..."></textarea>
                        <p class="mt-1 text-sm text-gray-500">Maksimal 500 karakter</p>
                    </div>

                    <button type="submit" class="w-full bg-linear-to-r from-indigo-500 to-purple-500 text-white font-bold py-4 rounded-xl hover:shadow-lg transition-all flex items-center justify-center gap-3">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Ajukan Pinjam</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- QR Scanner Modal --}}
    <div id="qr-scanner-modal" class="fixed inset-0 bg-black/70 z-50 hidden">
        <div class="fixed inset-4 md:inset-auto md:top-1/2 md:left-1/2 md:transform md:-translate-x-1/2 md:-translate-y-1/2 bg-white rounded-2xl p-6 max-w-lg w-full">
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

    {{-- QR Code Scanner Library --}}
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const qrScannerBtn = document.getElementById('qr-scanner-btn');
            const qrScannerModal = document.getElementById('qr-scanner-modal');
            const closeQrScanner = document.getElementById('close-qr-scanner');
            const productInfo = document.getElementById('product-info');
            const borrowForm = document.getElementById('borrow-form');
            let html5QrCode = null;

            // QR Scanner Functions
            if (qrScannerBtn && qrScannerModal) {
                qrScannerBtn.addEventListener('click', function() {
                    qrScannerModal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                    startQrScanner();
                });
            }

            if (closeQrScanner && qrScannerModal) {
                closeQrScanner.addEventListener('click', function() {
                    stopQrScanner();
                    qrScannerModal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                });
            }

            if (qrScannerModal) {
                qrScannerModal.addEventListener('click', function(e) {
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
                            const url = new URL(decodedText);
                            const productId = url.searchParams.get('qr_product');

                            if (productId) {
                                fetchAndLoadProduct(productId);
                                stopQrScanner();
                                qrScannerModal.classList.add('hidden');
                                document.body.style.overflow = 'auto';
                            } else {
                                showNotification('QR Code tidak valid!', 'error');
                            }
                        } catch (e) {
                            showNotification('Format QR Code tidak valid!', 'error');
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
                fetchAndLoadProduct(qrProductId);
                // Remove the parameter from URL
                urlParams.delete('qr_product');
                const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
                window.history.replaceState({}, '', newUrl);
            }

            function showNotification(message, type = 'success') {
                const notification = document.createElement('div');
                notification.className =
                    `fixed top-4 right-4 z-50 p-4 rounded-xl shadow-lg ${type === 'success' ? 'bg-linear-to-r from-green-500 to-emerald-500' : 'bg-linear-to-r from-red-500 to-rose-500'} text-white transform translate-x-full transition-transform duration-300`;
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

            // Auto-hide alerts
            setTimeout(function() {
                const alerts = document.querySelectorAll('.bg-linear-to-r');
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