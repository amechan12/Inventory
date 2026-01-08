@extends('layout')

@section('title', 'Nota Transaksi')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex justify-between items-center print:hidden">
        <a href="{{ route('shop.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all">
            <i class="fa-solid fa-arrow-left mr-2"></i>
            Kembali ke Shop
        </a>
        
        <button onclick="window.print()" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-xl hover:shadow-lg transition-all">
            <i class="fa-solid fa-print mr-2"></i>
            Print Nota
        </button>
    </div>

    {{-- Receipt Container --}}
    <div id="receipt-area" class="bg-white rounded-2xl shadow-lg p-8 border border-gray-200">
        {{-- Header --}}
        <div class="text-center border-b-2 border-dashed border-gray-300 pb-6 mb-6">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-2">
                NOTA PEMBELIAN
            </h1>
            <p class="text-gray-600">Terima kasih atas pembelian Anda!</p>
        </div>

        {{-- Invoice Info --}}
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <p class="text-sm text-gray-600">Nomor Invoice</p>
                <p class="font-bold text-lg text-gray-800">{{ $transaction->invoice_number }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600">Tanggal</p>
                <p class="font-bold text-lg text-gray-800">{{ $transaction->created_at->format('d M Y, H:i') }}</p>
            </div>
        </div>

        {{-- Customer Info --}}
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-4 mb-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Kasir</p>
                    <p class="font-semibold text-gray-800">{{ $transaction->user->name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">Metode Pembayaran</p>
                    <p class="font-semibold text-gray-800">
                        @if($transaction->payment_method === 'cash')
                            💵 Cash
                        @elseif($transaction->payment_method === 'qris')
                            📱 QRIS
                        @else
                            💳 Debit Card
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Items Table --}}
        <div class="mb-6">
            <table class="w-full">
                <thead>
                    <tr class="border-b-2 border-gray-300">
                        <th class="text-left py-3 text-sm font-semibold text-gray-700">Produk</th>
                        <th class="text-center py-3 text-sm font-semibold text-gray-700">Qty</th>
                        <th class="text-right py-3 text-sm font-semibold text-gray-700">Harga</th>
                        <th class="text-right py-3 text-sm font-semibold text-gray-700">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaction->products as $product)
                    <tr class="border-b border-gray-200">
                        <td class="py-3">
                            <p class="font-medium text-gray-800">{{ $product->name }}</p>
                            @if($product->category)
                                <p class="text-xs text-gray-500">{{ $product->category }}</p>
                            @endif
                        </td>
                        <td class="text-center py-3 text-gray-700">{{ $product->pivot->quantity }}</td>
                        <td class="text-right py-3 text-gray-700">Rp {{ number_format($product->pivot->price_per_item, 0, ',', '.') }}</td>
                        <td class="text-right py-3 font-semibold text-gray-800">
                            Rp {{ number_format($product->pivot->price_per_item * $product->pivot->quantity, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Total --}}
        <div class="border-t-2 border-gray-300 pt-4 mb-6">
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-600">Subtotal</span>
                <span class="text-gray-800 font-medium">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-600">Pajak (0%)</span>
                <span class="text-gray-800 font-medium">Rp 0</span>
            </div>
            <div class="flex justify-between items-center pt-4 border-t border-dashed border-gray-300">
                <span class="text-xl font-bold text-gray-800">TOTAL</span>
                <span class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                    Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                </span>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center border-t-2 border-dashed border-gray-300 pt-6 text-gray-600">
            <p class="mb-2 font-semibold">Terima kasih atas kunjungan Anda!</p>
            <p class="text-sm">Barang yang sudah dibeli tidak dapat dikembalikan</p>
            <p class="text-xs mt-4">Simpan nota ini sebagai bukti pembelian yang sah</p>
        </div>
    </div>

    {{-- Action Buttons (Print Only) --}}
    <div class="mt-6 flex gap-4 print:hidden">
        <button onclick="window.print()" class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-xl hover:shadow-lg transition-all font-semibold">
            <i class="fa-solid fa-print mr-2"></i>
            Print Nota
        </button>
        
        <button onclick="savePDF()" class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-500 to-cyan-500 text-white rounded-xl hover:shadow-lg transition-all font-semibold">
            <i class="fa-solid fa-file-pdf mr-2"></i>
            Simpan sebagai PDF
        </button>
        
        <a href="{{ route('shop.index') }}" class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-all font-semibold">
            <i class="fa-solid fa-shopping-cart mr-2"></i>
            Transaksi Baru
        </a>
    </div>
</div>

{{-- Print Styles --}}
<style>
@media print {
    /* Hide everything except receipt area */
    body * {
        visibility: hidden;
    }
    
    #receipt-area, #receipt-area * {
        visibility: visible;
    }
    
    #receipt-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        box-shadow: none !important;
        border: none !important;
        border-radius: 0 !important;
        padding: 20mm !important;
    }
    
    /* Hide navigation, footer, and buttons */
    nav, footer, .print\:hidden {
        display: none !important;
    }
    
    /* Page setup */
    @page {
        margin: 0;
        size: A4 portrait;
    }
    
    body {
        background: white;
        margin: 0;
        padding: 0;
    }
    
    /* Ensure colors print */
    .bg-gradient-to-r,
    .border-indigo-600,
    .border-purple-600 {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        color-adjust: exact;
    }
    
    /* Table styling for print */
    table {
        page-break-inside: avoid;
    }
    
    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
    
    /* Adjust font sizes for print */
    h1 {
        font-size: 24pt !important;
    }
    
    .text-2xl {
        font-size: 18pt !important;
    }
    
    .text-xl {
        font-size: 14pt !important;
    }
    
    .text-sm {
        font-size: 10pt !important;
    }
    
    .text-xs {
        font-size: 8pt !important;
    }
}

/* Smooth transition for print */
#receipt-area {
    transition: all 0.3s ease;
}
</style>

<script>
// Function to save as PDF using browser's print to PDF
function savePDF() {
    // Show instruction
    alert('Gunakan "Print to PDF" atau "Save as PDF" pada dialog print yang akan muncul.');
    window.print();
}

// Keyboard shortcut for print
document.addEventListener('keydown', function(e) {
    // Ctrl+P or Cmd+P
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
        e.preventDefault();
        window.print();
    }
});

// Auto print on page load (optional - comment out if not needed)
// setTimeout(() => {
//     if (confirm('Print nota sekarang?')) {
//         window.print();
//     }
// }, 500);
</script>
@endsection