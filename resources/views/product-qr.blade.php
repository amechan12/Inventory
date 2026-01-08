@extends('layout')

@section('title', 'QR Code - ' . $product->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6 print:hidden">
        <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all">
            <i class="fa-solid fa-arrow-left mr-2"></i>
            Kembali ke Kelola Barang
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-200" id="qr-print-area">
        {{-- Product Info --}}
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $product->name }}</h1>
            <p class="text-gray-600">QR Code untuk scan barang</p>
        </div>

        {{-- Product Details --}}
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-6 mb-8">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Stok</p>
                    <p class="text-xl font-bold text-purple-600">{{ $product->stock }} pcs</p>
                </div>
                @if($product->category)
                <div class="col-span-2">
                    <p class="text-sm text-gray-600 mb-1">Kategori</p>
                    <p class="font-semibold text-gray-800">{{ $product->category }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- QR Code Display --}}
        <div class="text-center mb-8">
            <div class="inline-block p-6 bg-white rounded-2xl shadow-lg border-4 border-indigo-500">
                {!! $qrCode !!}
            </div>
            <p class="text-sm text-gray-600 mt-4">Scan QR code ini untuk meminjam barang</p>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-col sm:flex-row gap-4 print:hidden">
            <button onclick="window.print()" 
                    class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-xl hover:shadow-lg transition-all font-semibold">
                <i class="fa-solid fa-print mr-2"></i>
                Print QR Code
            </button>
        </div>

        {{-- Instructions --}}
        <div class="mt-8 p-6 bg-blue-50 rounded-xl border border-blue-200 print:hidden">
            <h3 class="font-bold text-blue-900 mb-3 flex items-center">
                <i class="fa-solid fa-info-circle mr-2"></i>
                Cara Menggunakan QR Code
            </h3>
            <ol class="list-decimal list-inside space-y-2 text-sm text-blue-800">
                <li>Buka halaman Pinjam Barang di aplikasi</li>
                <li>Klik tombol "Scan QR code barang"</li>
                <li>Arahkan kamera ke QR code ini</li>
                <li>Produk akan otomatis diajukan untuk dipinjam</li>
            </ol>
        </div>
    </div>
</div>

{{-- Print Styles --}}
<style>
@media print {
    /* Hide everything except QR area */
    body * {
        visibility: hidden;
    }
    
    #qr-print-area, #qr-print-area * {
        visibility: visible;
    }
    
    #qr-print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        box-shadow: none !important;
        border: none !important;
    }
    
    /* Hide navigation and footer */
    nav, footer, .print\:hidden {
        display: none !important;
    }
    
    /* Optimize page for printing */
    @page {
        margin: 1cm;
        size: A4;
    }
    
    /* Ensure colors print */
    .bg-gradient-to-r,
    .border-indigo-500 {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        color-adjust: exact;
    }
    
    /* Center content */
    body {
        background: white;
    }
    
    /* Adjust QR size for print */
    #qr-print-area img {
        max-width: 400px !important;
        height: auto !important;
    }
}

/* Make sure QR is visible */
#qr-print-area img {
    display: block;
    margin: 0 auto;
}
</style>

<script>
// Keyboard shortcut for print
document.addEventListener('keydown', function(e) {
    // Ctrl+P or Cmd+P
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
        e.preventDefault();
        window.print();
    }
});
</script>
@endsection