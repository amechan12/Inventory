<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'products', 'approver']); // Eager load relasi

        // Filter berdasarkan role user
        if (Auth::user()->role === 'anggota') {
            // Anggota hanya bisa lihat pinjaman mereka sendiri
            $query->where('user_id', Auth::id());
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                // Mencari di tabel transactions berdasarkan invoice_number
                $q->where('invoice_number', 'like', '%' . $search . '%')
                    // Mencari di tabel users berdasarkan nama
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%');
                    })
                    // Mencari di tabel products berdasarkan nama produk
                    ->orWhereHas('products', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Filter berdasarkan status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $transactions = $query->latest()->paginate(15)->withQueryString();

        return view('history', compact('transactions'));
    }

    public function show($id)
    {
        try {
            $transaction = Transaction::with(['user', 'products'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $transaction->id,
                    'invoice_number' => $transaction->invoice_number,
                    'cashier_name' => $transaction->user->name ?? 'N/A',
                    'date' => $transaction->created_at->format('d/m/Y H:i:s'),
                    'payment_method' => ucfirst($transaction->payment_method),
                    'status' => $transaction->status,
                    'status_label' => $this->getStatusLabel($transaction->status),
                    'borrow_reason' => $transaction->borrow_reason,
                    'duration' => $transaction->duration,
                    'borrow_date' => $transaction->borrow_date ? $transaction->borrow_date->format('d/m/Y') : null,
                    'return_date' => $transaction->return_date ? $transaction->return_date->format('d/m/Y') : null,
                    'condition_on_return' => $transaction->condition_on_return,
                    'return_notes' => $transaction->return_notes,
                    'approved_by_name' => $transaction->approver->name ?? null,
                    'approved_at' => $transaction->approved_at ? $transaction->approved_at->format('d/m/Y H:i') : null,
                    'total_amount' => $transaction->total_amount,
                    'total_amount_formatted' => $transaction->total_amount ? 'Rp ' . number_format($transaction->total_amount, 0, ',', '.') : null,
                    'products' => $transaction->products->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'category' => $product->category ?? 'Tidak ada kategori',
                            'quantity' => $product->pivot->quantity,
                            'price_per_item' => $product->pivot->price_per_item,
                            'price_per_item_formatted' => $product->pivot->price_per_item ? 'Rp ' . number_format($product->pivot->price_per_item, 0, ',', '.') : null,
                            'subtotal' => $product->pivot->quantity * $product->pivot->price_per_item,
                            'subtotal_formatted' => $product->pivot->price_per_item ? 'Rp ' . number_format($product->pivot->quantity * $product->pivot->price_per_item, 0, ',', '.') : null,
                        ];
                    }),
                    'total_items' => $transaction->products->sum('pivot.quantity')
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan.'
            ], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $transaction = Transaction::findOrFail($id);
            
            // Hapus relasi many-to-many dengan products jika ada
            $transaction->products()->detach();
            
            // Hapus transaksi
            $transaction->delete();
            
            return redirect()->route('history.index')
                ->with('success', 'Transaksi berhasil dihapus.');
                
        } catch (\Exception $e) {
            return redirect()->route('history.index')
                ->with('error', 'Gagal menghapus transaksi. ' . $e->getMessage());
        }
    }

    private function getStatusLabel($status)
    {
        $labels = [
            'pending' => 'Menunggu Persetujuan',
            'borrowed' => 'Sedang Dipinjam',
            'returning' => 'Menunggu Pengembalian',
            'returned' => 'Sudah Dikembalikan',
            'cancelled' => 'Dibatalkan'
        ];

        return $labels[$status] ?? ucfirst($status);
    }
}