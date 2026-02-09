@extends('layout')

@section('title', 'QR Code Kotak - ' . $box->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6 print:hidden">
        <a href="{{ route('boxes.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all">
            <i class="fa-solid fa-arrow-left mr-2"></i>
            Kembali ke Kotak
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-200" id="qr-print-area">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $box->name }}</h1>
            <p class="text-sm text-gray-500">QR Code kotak ini mengarah ke halaman detail kotak</p>
            <p class="text-xs text-gray-400 mt-1">Barcode: <span class="font-mono">{{ $box->barcode }}</span></p>
        </div>

        <div class="text-center mb-8">
            <div class="inline-block p-6 bg-white rounded-2xl shadow-lg border-4 border-indigo-500">
                {!! $qrCode !!}
            </div>
            <p class="text-sm text-gray-600 mt-4">Scan QR code ini untuk membuka halaman isi kotak ini</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 print:hidden">
            <button onclick="window.print()" 
                    class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-xl hover:shadow-lg transition-all font-semibold">
                <i class="fa-solid fa-print mr-2"></i>
                Print QR Code
            </button>
        </div>

        <div class="mt-8 p-6 bg-blue-50 rounded-xl border border-blue-200 print:hidden">
            <h3 class="font-bold text-blue-900 mb-3 flex items-center">
                <i class="fa-solid fa-info-circle mr-2"></i>
                Cara Menggunakan QR Kotak
            </h3>
            <ol class="list-decimal list-inside space-y-2 text-sm text-blue-800">
                <li>Scan QR kotak menggunakan fitur QR scanner di aplikasi.</li>
                <li>Halaman kotak akan menampilkan daftar produk di dalam kotak tersebut.</li>
                <li>Pilih produk yang ingin dipinjam lalu lanjutkan proses peminjaman.</li>
            </ol>
        </div>
    </div>
</div>

<style>
@media print {
    body * { visibility: hidden; }
    #qr-print-area, #qr-print-area * { visibility: visible; }
    #qr-print-area { position: absolute; left: 0; top: 0; width: 100%; }
    nav, footer, .print\:hidden { display: none !important; }
    @page { margin: 1cm; size: A4; }
    #qr-print-area img { max-width: 400px !important; height: auto !important; }
}
#qr-print-area img { display: block; margin: 0 auto; }
</style>

<script>
// Ctrl/Cmd + P untuk print
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
        e.preventDefault();
        window.print();
    }
});
</script>
@endsection
