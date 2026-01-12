<?php

namespace App\Http\Controllers;

use App\Models\Segment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\Common\EccLevel;

class SegmentController extends Controller
{
    // List segments (pengelola only)
    public function index()
    {
        $segments = Segment::latest()->get();
        return view('segments.index', compact('segments'));
    }

    public function create()
    {
        return view('segments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $code = Str::slug($request->name) . '-' . strtolower(Str::random(4));

        $path = null;
        if ($request->hasFile('image_path')) {
            $path = $request->file('image_path')->store('segments', 'public');
        }

        Segment::create([
            'name' => $request->name,
            'code' => $code,
            'description' => $request->description,
            'image_path' => $path,
        ]);

        return redirect()->route('segments.index')->with('success', 'Segmen berhasil dibuat.');
    }

    public function edit(Segment $segment)
    {
        return view('segments.edit', compact('segment'));
    }

    public function update(Request $request, Segment $segment)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only('name', 'description');

        // Handle image upload
        if ($request->hasFile('image_path')) {
            // Delete old image if exists
            if ($segment->image_path) {
                Storage::disk('public')->delete($segment->image_path);
            }
            // Store new image
            $data['image_path'] = $request->file('image_path')->store('segments', 'public');
        }

        $segment->update($data);

        return redirect()->route('segments.index')->with('success', 'Segmen berhasil diperbarui.');
    }

    public function destroy(Segment $segment)
    {
        // Delete image if exists
        if ($segment->image_path) {
            Storage::disk('public')->delete($segment->image_path);
        }
        // Optional: check whether products exist in the segment
        $segment->delete();
        return redirect()->route('segments.index')->with('success', 'Segmen berhasil dihapus.');
    }

    // Page untuk pengembalian per segmen
    public function returnPage(Segment $segment)
    {
        // Cari transaksi dengan status returning (menunggu konfirmasi) yang punya produk di segmen ini
        $transactions = Transaction::where('status', 'returning')
            ->whereHas('products', function ($q) use ($segment) {
                $q->where('segment_id', $segment->id);
            })
            ->with(['user', 'products'])
            ->latest()
            ->get();

        // Transaksi yang sedang dipinjam (borrowed) untuk produk di segmen ini
        $activeLoans = Transaction::where('status', 'borrowed')
            ->whereHas('products', function ($q) use ($segment) {
                $q->where('segment_id', $segment->id);
            })
            ->with('products')
            ->latest()
            ->get();

        return view('segments.return', compact('segment', 'transactions', 'activeLoans'));
    }

    // Tampilkan QR generator seperti di page kelola barang
    public function showQR($id)
    {
        try {
            $segment = Segment::findOrFail($id);
            $qrUrl = route('segments.return', $segment->id);

            // Generate QR code dengan Chillerlan
            $qrcode = new QRCode();
            $qrImage = $qrcode->render($qrUrl);

            if (strpos($qrImage, 'data:image') === 0) {
                $qrCode = '<img src="' . $qrImage . '" alt="QR Code" class="mx-auto border-4 border-indigo-500 rounded-lg shadow-lg" style="width: 300px; height: 300px;" />';
            } else {
                $qrCode = '<img src="data:image/png;base64,' . base64_encode($qrImage) . '" alt="QR Code" class="mx-auto border-4 border-indigo-500 rounded-lg shadow-lg" style="width: 300px; height: 300px;" />';
            }

            return view('segments.qr', compact('segment', 'qrCode'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal generate QR Code: ' . $e->getMessage());
        }
    }

    // API untuk QR segmen (mengembalikan redirect URL)
    public function getByQR($id)
    {
        $segment = Segment::find($id);
        if (!$segment) {
            return response()->json(['success' => false, 'error' => 'Segmen tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'redirect' => route('segments.return', $segment->id),
            'segment' => [
                'id' => $segment->id,
                'name' => $segment->name,
                'code' => $segment->code,
            ]
        ]);
    }

    // API: cari transaksi returning untuk produk di segmen tertentu (dipakai saat scan di halaman segmen)
    public function getReturningByProduct($segmentId, $productId)
    {
        $transaction = Transaction::where('status', 'returning')
            ->whereHas('products', function ($q) use ($productId, $segmentId) {
                $q->where('products.id', $productId)
                  ->where('products.segment_id', $segmentId);
            })
            ->with(['user', 'products'])
            ->first();

        if (!$transaction) {
            return response()->json(['success' => false, 'error' => 'Tidak ada transaksi pengembalian untuk produk ini di segmen ini'], 404);
        }

        $matching = $transaction->products->firstWhere('id', $productId);

        return response()->json([
            'success' => true,
            'transaction' => [
                'id' => $transaction->id,
                'invoice_number' => $transaction->invoice_number,
                'user' => [
                    'id' => $transaction->user->id,
                    'name' => $transaction->user->name,
                ],
                'product' => $matching ? [
                    'id' => $matching->id,
                    'name' => $matching->name,
                    'quantity' => $matching->pivot->quantity ?? 1,
                ] : null,
                'products' => $transaction->products->map(function($p){ return ['id' => $p->id, 'name' => $p->name, 'quantity' => $p->pivot->quantity ?? 1]; }),
                'borrow_date' => $transaction->borrow_date ? $transaction->borrow_date->format('d/m/Y') : null,
                'duration' => $transaction->duration,
                'borrow_reason' => $transaction->borrow_reason,
            ]
        ]);
    }
}