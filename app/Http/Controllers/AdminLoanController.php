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
                ->sum(function ($transaction) {
                    return $transaction->products->sum('pivot.quantity');
                }),
        ];

        return view('admin.loans', compact('pendingLoans', 'returningLoans', 'stats'));
    }

    // Approve peminjaman (status: pending -> borrowed). Handles multiple products & quantities.
    public function approveBorrow(Request $request, $id)
    {
        try {
            $transaction = Transaction::where('id', $id)
                ->where('status', 'pending')
                ->with('products')
                ->firstOrFail();

            // Check availability for all products in the transaction
            // Validate by checking if consumeForApproval would succeed for each
            $insufficient = [];
            foreach ($transaction->products as $prod) {
                $qty = (int) ($prod->pivot->quantity ?? 1);
                $productModel = Product::with('boxes')->findOrFail($prod->id);

                // Check if consumeForApproval will succeed
                $boxQty = $productModel->getTotalQuantityInBoxes();
                if ($boxQty > 0) {
                    // Product is in boxes; check total box quantity
                    if ($boxQty < $qty) {
                        $insufficient[] = "{$productModel->name} (dibutuhkan {$qty}, tersedia di kotak {$boxQty})";
                    }
                } else {
                    // Product is not in any box; check product.stock
                    if ($productModel->stock < $qty) {
                        $insufficient[] = "{$productModel->name} (dibutuhkan {$qty}, stok fisik {$productModel->stock})";
                    }
                }
            }

            if (count($insufficient) > 0) {
                throw new \Exception('Stok tidak mencukupi untuk: ' . implode('; ', $insufficient));
            }

            DB::beginTransaction();

            // All good -> apply changes for every product in this transaction
            // Use Product::consumeForApproval to deduct from box pivots when applicable,
            // otherwise deduct from product.stock. Then clear reserved_stock.
            foreach ($transaction->products as $prod) {
                $qty = $prod->pivot->quantity ?? 1;
                $productModel = Product::with('boxes')->findOrFail($prod->id);

                // Attempt to consume quantity (will throw if insufficient)
                $productModel->consumeForApproval((int) $qty);

                // Clear reservation
                $productModel->decrement('reserved_stock', $qty);
            }

            // Check if this is a permanent borrow (duration = 0)
            $isPermanent = $transaction->duration == 0;

            $transaction->update([
                'status' => $isPermanent ? 'completed' : 'borrowed',
                'approved_by' => Auth::id(),
                'approved_at' => Carbon::now(),
                'borrow_date' => Carbon::now(),
            ]);

            DB::commit();

            $message = $isPermanent
                ? 'Pinjaman permanen berhasil disetujui. Stok barang telah dipotong permanen.'
                : 'Peminjaman berhasil disetujui. Stok barang dikurangi.';

            return redirect()->route('admin.loans')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyetujui peminjaman: ' . $e->getMessage());
        }
    }

    // Reject peminjaman (status: pending -> cancelled) and restore reserved_stock for all products in transaction
    public function rejectBorrow($id)
    {
        try {
            DB::beginTransaction();

            $transaction = Transaction::where('id', $id)
                ->where('status', 'pending')
                ->with('products')
                ->firstOrFail();

            // Restore reserved_stock for each product based on pivot quantity
            foreach ($transaction->products as $prod) {
                $qty = $prod->pivot->quantity ?? 1;
                $productModel = Product::find($prod->id);
                if ($productModel) {
                    $productModel->decrement('reserved_stock', $qty);
                }
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

    // Approve all pending peminjaman (process each transaction and all its products)
    public function approveAll(Request $request)
    {
        $pending = Transaction::where('status', 'pending')->with('products')->get();
        $approvedCount = 0;
        $failed = [];

        foreach ($pending as $transaction) {
            // Check availability for all products in this transaction
            $insufficient = [];
            foreach ($transaction->products as $prod) {
                $qty = (int) ($prod->pivot->quantity ?? 1);
                $productModel = Product::with('boxes')->findOrFail($prod->id);

                // Check if consumeForApproval will succeed
                $boxQty = $productModel->getTotalQuantityInBoxes();
                if ($boxQty > 0) {
                    // Product is in boxes; check total box quantity
                    if ($boxQty < $qty) {
                        $insufficient[] = "{$productModel->name} (dibutuhkan {$qty}, tersedia di kotak {$boxQty})";
                    }
                } else {
                    // Product is not in any box; check product.stock
                    if ($productModel->stock < $qty) {
                        $insufficient[] = "{$productModel->name} (dibutuhkan {$qty}, stok fisik {$productModel->stock})";
                    }
                }
            }

            if (count($insufficient) > 0) {
                $failed[] = "{$transaction->invoice_number}: stok tidak mencukupi untuk - " . implode('; ', $insufficient);
                continue;
            }

            try {
                DB::beginTransaction();

                // Deduct stock (permanently consume) and reserved_stock (clear reservation) for each product
                foreach ($transaction->products as $prod) {
                    $qty = (int) ($prod->pivot->quantity ?? 1);
                    $productModel = Product::with('boxes')->findOrFail($prod->id);

                    // Attempt to consume quantity (will throw if insufficient)
                    $productModel->consumeForApproval($qty);

                    // Clear reservation
                    $productModel->decrement('reserved_stock', $qty);
                }

                // Check if this is a permanent borrow (duration = 0)
                $isPermanent = $transaction->duration == 0;

                $transaction->update([
                    'status' => $isPermanent ? 'completed' : 'borrowed',
                    'approved_by' => Auth::id(),
                    'approved_at' => Carbon::now(),
                    'borrow_date' => Carbon::now(),
                ]);

                DB::commit();
                $approvedCount++;
            } catch (\Exception $e) {
                DB::rollBack();
                $failed[] = "{$transaction->invoice_number}: " . $e->getMessage();
            }
        }

        $message = "{$approvedCount} peminjaman berhasil disetujui.";
        if (count($failed) > 0) {
            $message .= ' Beberapa peminjaman gagal disetujui: ' . implode('; ', array_slice($failed, 0, 5));
            if (count($failed) > 5)
                $message .= '...';
            return redirect()->route('admin.loans')->with('error', $message);
        }

        return redirect()->route('admin.loans')->with('success', $message);
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

            // Ubah status menjadi returned
            $transaction->update([
                'status' => 'returned',
                'return_date' => Carbon::now(),
                'condition_on_return' => $request->condition_on_return,
                'return_notes' => $request->return_notes,
            ]);

            // Kembalikan stock hanya jika kondisi good atau damaged (lost tidak dikembalikan)
            if ($request->condition_on_return !== 'lost') {
                foreach ($transaction->products as $prod) {
                    $qty = $prod->pivot->quantity ?? 1;
                    $productModel = Product::find($prod->id);
                    if ($productModel) {
                        $productModel->increment('stock', $qty);
                    }
                }
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
            ->whereHas('products', function ($query) use ($productId) {
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