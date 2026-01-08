<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminLoanController extends Controller
{
    // Menampilkan dashboard admin dengan daftar pending dan returning
    public function index()
    {
        // Transaksi pending (menunggu approval peminjaman)
        $pendingLoans = Transaction::where('status', 'pending')
            ->with(['user', 'products'])
            ->latest()
            ->get();

        // Transaksi returning (menunggu konfirmasi pengembalian)
        $returningLoans = Transaction::where('status', 'returning')
            ->with(['user', 'products'])
            ->latest()
            ->get();

        // Statistik
        $stats = [
            'pending_count' => $pendingLoans->count(),
            'returning_count' => $returningLoans->count(),
            'active_loans' => Transaction::where('status', 'borrowed')->count(),
            'total_items_borrowed' => Transaction::where('status', 'borrowed')
                ->with('products')
                ->get()
                ->sum(function($transaction) {
                    return $transaction->products->sum('pivot.quantity');
                }),
        ];

        return view('admin.loans', compact('pendingLoans', 'returningLoans', 'stats'));
    }

    // Approve peminjaman (status: pending -> borrowed)
    public function approveBorrow(Request $request, $id)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::where('id', $id)
                ->where('status', 'pending')
                ->firstOrFail();

            $product = Product::findOrFail($request->product_id);

            // Pastikan produk masih tersedia
            $availableStock = $product->stock - $product->reserved_stock;
            
            if ($availableStock <= 0) {
                throw new \Exception('Barang tidak tersedia. Stok habis atau sedang dipesan.');
            }

            // Ubah status menjadi borrowed
            $transaction->update([
                'status' => 'borrowed',
                'approved_by' => Auth::id(),
                'approved_at' => Carbon::now(),
                'borrow_date' => Carbon::now(),
            ]);

            // Kurangi reserved_stock dan kurangi stock secara resmi
            $product->decrement('reserved_stock');
            $product->decrement('stock');

            DB::commit();
            
            return redirect()->route('admin.loans')
                ->with('success', 'Peminjaman berhasil disetujui. Stok barang dikurangi.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyetujui peminjaman: ' . $e->getMessage());
        }
    }

    // Reject peminjaman (status: pending -> cancelled)
    public function rejectBorrow($id)
    {
        try {
            DB::beginTransaction();

            $transaction = Transaction::where('id', $id)
                ->where('status', 'pending')
                ->firstOrFail();

            // Ambil produk dari transaksi
            $product = $transaction->products->first();
            
            if ($product) {
                // Kembalikan reserved_stock
                $product->decrement('reserved_stock');
            }

            // Ubah status menjadi cancelled
            $transaction->update([
                'status' => 'cancelled',
                'approved_by' => Auth::id(),
                'approved_at' => Carbon::now(),
            ]);

            DB::commit();
            
            return redirect()->route('admin.loans')
                ->with('success', 'Peminjaman berhasil ditolak.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menolak peminjaman: ' . $e->getMessage());
        }
    }

    // Confirm pengembalian (status: returning -> returned)
    public function confirmReturn(Request $request, $id)
    {
        $request->validate([
            'condition_on_return' => 'required|in:good,damaged,lost',
            'return_notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::where('id', $id)
                ->where('status', 'returning')
                ->firstOrFail();

            // Ambil produk dari transaksi
            $product = $transaction->products->first();
            
            if (!$product) {
                throw new \Exception('Produk tidak ditemukan dalam transaksi.');
            }

            // Ubah status menjadi returned
            $transaction->update([
                'status' => 'returned',
                'return_date' => Carbon::now(),
                'condition_on_return' => $request->condition_on_return,
                'return_notes' => $request->return_notes,
            ]);

            // Kembalikan stock hanya jika kondisi good atau damaged (lost tidak dikembalikan)
            if ($request->condition_on_return !== 'lost') {
                $product->increment('stock');
            }

            DB::commit();
            
            $message = $request->condition_on_return === 'lost' 
                ? 'Pengembalian dikonfirmasi. Barang hilang, stok tidak dikembalikan.'
                : 'Pengembalian dikonfirmasi. Stok barang dikembalikan.';
            
            return redirect()->route('admin.loans')
                ->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengonfirmasi pengembalian: ' . $e->getMessage());
        }
    }

    // API untuk mendapatkan info transaksi via QR (untuk admin confirm return)
    public function getLoanTransactionByQR($productId, $transactionId)
    {
        $transaction = Transaction::where('id', $transactionId)
            ->where('status', 'returning')
            ->whereHas('products', function($query) use ($productId) {
                $query->where('products.id', $productId);
            })
            ->with(['user', 'products'])
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'error' => 'Transaksi pengembalian tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'transaction' => [
                'id' => $transaction->id,
                'invoice_number' => $transaction->invoice_number,
                'user' => [
                    'id' => $transaction->user->id,
                    'name' => $transaction->user->name,
                ],
                'product' => $transaction->products->first(),
                'borrow_date' => $transaction->borrow_date->format('d/m/Y'),
                'duration' => $transaction->duration,
                'borrow_reason' => $transaction->borrow_reason,
            ]
        ]);
    }
}