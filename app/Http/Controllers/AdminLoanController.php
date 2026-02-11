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

            // Stock is already deducted in submitBorrow, so no need to validate here

            DB::beginTransaction();

            // Stock is already deducted in submitBorrow, so we only need to change the status here

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
            'products' => 'required|array',
            'products.*.good' => 'required|integer|min:0',
            'products.*.damaged' => 'required|integer|min:0',
            'products.*.lost' => 'required|integer|min:0',
            'return_notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::where('id', $id)
                ->where('status', 'returning')
                ->with('products')
                ->firstOrFail();

            // Validate total quantities match borrowed amount
            foreach ($transaction->products as $prod) {
                $borrowedQty = $prod->pivot->quantity;
                $input = $request->products[$prod->id] ?? null;
                
                if (!$input) {
                    throw new \Exception("Data pengembalian tidak lengkap untuk produk {$prod->name}");
                }

                $totalReturned = $input['good'] + $input['damaged'] + $input['lost'];
                
                if ($totalReturned != $borrowedQty) {
                    throw new \Exception("Jumlah pengembalian untuk {$prod->name} tidak sesuai (Dipinjam: {$borrowedQty}, Diinput: {$totalReturned})");
                }
            }

            // Ubah status menjadi returned
            // Note: Since we now have detailed condition per product, we might set a general 'condition_on_return' 
            // based on whether there are any damaged/lost items.
            $hasDamaged = collect($request->products)->sum('damaged') > 0;
            $hasLost = collect($request->products)->sum('lost') > 0;
            
            $generalCondition = 'good';
            if ($hasLost) $generalCondition = 'lost';
            elseif ($hasDamaged) $generalCondition = 'damaged';

            $transaction->update([
                'status' => 'returned',
                'return_date' => Carbon::now(),
                'condition_on_return' => $generalCondition, 
                'return_notes' => $request->return_notes,
            ]);

            // Restore stock logic
            foreach ($transaction->products as $prod) {
                $input = $request->products[$prod->id];
                // Only restore Good and Damaged items. Lost items are gone.
                $qtyToRestore = $input['good'] + $input['damaged'];
                
                if ($qtyToRestore > 0) {
                    $productModel = Product::with('boxes')->find($prod->id);
                    
                    if ($productModel) {
                        // Check if product is in a box
                        $box = $productModel->boxes->first();
                        
                        if ($box) {
                            // If in box, restore to box pivot
                            $currentQty = $box->pivot->quantity;
                            $productModel->boxes()->updateExistingPivot($box->id, ['quantity' => $currentQty + $qtyToRestore]);
                        }
                        
                        // ALWAYS restore to global stock
                        $productModel->increment('stock', $qtyToRestore);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.loans')
                ->with('success', 'Pengembalian berhasil dikonfirmasi.');

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