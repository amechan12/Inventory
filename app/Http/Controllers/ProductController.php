<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\Common\EccLevel;
use App\Models\Segment;
use App\Models\Box;

class ProductController extends Controller
{
    // Menampilkan halaman manage product
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%');
            });
        }

        // Bangun daftar kategori (nama, slug, jumlah produk)
        $allCategories = Product::whereNotNull('category')
            ->get()
            ->groupBy('category')
            ->map(function ($group, $name) {
                return [
                    'name' => $name,
                    'slug' => \Illuminate\Support\Str::slug($name),
                    'count' => $group->count(),
                ];
            })->values()->toArray();

        // Filter berdasarkan kategori (slug -> nama asli)
        if ($request->filled('category')) {
            $catSlug = $request->input('category');
            $matched = collect($allCategories)->firstWhere('slug', $catSlug);
            if ($matched) {
                $query->where('category', $matched['name']);
            }
        }

        // Filter berdasarkan kotak (produk yang berada di kotak tertentu)
        if ($request->filled('box')) {
            $boxId = (int) $request->input('box');
            $query->whereHas('boxes', function ($q) use ($boxId) {
                $q->where('boxes.id', $boxId);
            });
        }

        // Apply segment filter by id
        if ($request->filled('segment')) {
            $query->where('segment_id', $request->input('segment'));
        }

        $products = $query->latest()->get();
        $segments = Segment::orderBy('name', 'asc')->get();
        // Kotak yang tersedia beserta jumlah produk di dalamnya
        $boxes = Box::withCount('products')->orderBy('name')->get();
        $totalProducts = Product::count();
        $categories = $allCategories;

        return view('manage', compact('products', 'segments', 'categories', 'totalProducts', 'boxes'));
    }

    // Menyimpan produk baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'category' => 'nullable|string',
            'segment_id' => 'nullable|exists:segments,id',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('image_path')) {
            $path = $request->file('image_path')->store('products', 'public');
        }

        $product = Product::create([
            'name' => $request->name,
            'stock' => $request->stock,
            'category' => $request->category,
            'image_path' => $path,
            'segment_id' => $request->segment_id,
        ]);

        // Attach to box if provided
        $addedToBox = false;
        $addedQty = null;
        if ($request->filled('box_id')) {
            $box = Box::find($request->box_id);
            if ($box) {
                $qty = $request->input('box_quantity', 1);
                $box->products()->attach($product->id, ['quantity' => max(1, (int)$qty)]);
                $addedToBox = true;
                $addedQty = max(1, (int)$qty);
            }
        }

        if ($request->wantsJson() || $request->ajax() || str_contains($request->header('accept', ''), 'application/json')) {
            return response()->json([
                'success' => true,
                'product' => $product->fresh(),
                'added_to_box' => $addedToBox,
                'quantity' => $addedQty,
            ]);
        }

        return back()->with('success', 'Barang berhasil ditambahkan!');
    }

    // Update produk
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string',
            'segment_id' => 'nullable|exists:segments,id',
            'box_id' => 'nullable|exists:boxes,id',
            'box_quantity' => 'nullable|integer|min:1',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_image' => 'nullable',
        ]);

        // Update basic fields
        $product->update($request->only('name', 'category', 'segment_id'));

        // Handle new uploaded image
        if ($request->hasFile('image_path')) {
            $path = $request->file('image_path')->store('products', 'public');

            // delete old image if exists
            if ($product->image_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image_path);
            }

            $product->image_path = $path;
            $product->save();
        } else if ($request->filled('remove_image')) {
            // Remove existing image if requested
            if ($product->image_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image_path);
                $product->image_path = null;
                $product->save();
            }
        }

        // Handle box assignment: if box_id provided, move product to that box (remove from old boxes first)
        if ($request->filled('box_id')) {
            $newBoxId = (int) $request->box_id;
            $box = Box::find($newBoxId);
            if ($box) {
                // Get current boxes for this product
                $currentBoxes = $product->boxes()->pluck('boxes.id')->toArray();
                
                // If product is already in the selected box, keep the current quantity (no change)
                if (in_array($newBoxId, $currentBoxes)) {
                    // Product already in this box, no need to change quantity
                    // Quantity remains the same
                } else {
                    // Get current quantity from old box (if exists) or use default 1
                    $currentQty = 1;
                    $oldBox = $product->boxes()->first();
                    if ($oldBox && $oldBox->pivot) {
                        $currentQty = $oldBox->pivot->quantity ?? 1;
                    }
                    
                    // If box_quantity is provided, use it; otherwise use current quantity
                    $qty = $request->input('box_quantity');
                    if ($qty === null || $qty === '') {
                        $qty = $currentQty;
                    } else {
                        $qty = max(1, (int)$qty);
                    }
                    
                    // Remove product from all current boxes first
                    $product->boxes()->detach();
                    // Then attach to the new box with the quantity
                    $box->products()->attach($product->id, ['quantity' => $qty]);
                }
            }
        } else {
            // If box_id is empty, remove product from all boxes
            $product->boxes()->detach();
        }

        if ($request->wantsJson() || $request->ajax() || str_contains($request->header('accept', ''), 'application/json')) {
            return response()->json(['success' => true, 'product' => $product->fresh()]);
        }

        return back()->with('success', 'Barang berhasil diperbarui!');
    }

    // Restock produk
    public function restock(Request $request, Product $product)
    {
        $request->validate(['stock_added' => 'required|integer|min:1']);

        $stockAdded = (int) $request->stock_added;
        
        // Update stock produk
        $product->increment('stock', $stockAdded);
        
        // Refresh product untuk mendapatkan stock terbaru
        $product->refresh();
        
        // Jika produk ada di kotak, juga update jumlah di kotak
        // Update pivot quantity di semua kotak yang mengandung produk ini
        $boxes = $product->boxes()->get();
        if ($boxes->isNotEmpty()) {
            foreach ($boxes as $box) {
                $currentQty = $box->pivot->quantity ?? 0;
                $newQty = $currentQty + $stockAdded;
                $box->products()->updateExistingPivot($product->id, ['quantity' => $newQty]);
            }
        }

        if ($request->wantsJson() || $request->ajax() || str_contains($request->header('accept', ''), 'application/json')) {
            return response()->json([
                'success' => true, 
                'message' => 'Stok barang berhasil ditambahkan', 
                'product_id' => $product->id, 
                'new_stock' => $product->stock,
                'stock_added' => $stockAdded,
                'updated_boxes' => $boxes->isNotEmpty(),
                'product' => $product->fresh()
            ]);
        }

        return back()->with('success', 'Stok barang berhasil ditambahkan!');
    }

    // Hapus produk
    public function destroy(Request $request, Product $product)
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        $product->delete();

        // If request expects JSON (AJAX/fetch), return JSON to avoid parse errors on client
        if ($request->wantsJson() || $request->ajax() || str_contains($request->header('accept', ''), 'application/json')) {
            return response()->json(['success' => true, 'message' => 'Barang berhasil dihapus']);
        }

        return back()->with('success', 'Barang berhasil dihapus!');
    }

    // Menampilkan QR code untuk produk
    public function showQR($id)
    {
        try {
            $product = Product::findOrFail($id);

            $qrUrl = route('loan.borrow') . '?qr_product=' . $product->id;

            // Generate QR code dengan Chillerlan
            $qrcode = new QRCode();
            $qrImage = $qrcode->render($qrUrl);

            // Jika sudah base64 data URI, langsung gunakan
            if (strpos($qrImage, 'data:image') === 0) {
                $qrCode = '<img src="' . $qrImage . '" alt="QR Code" class="mx-auto border-4 border-indigo-500 rounded-lg shadow-lg" style="width: 300px; height: 300px;" />';
            } else {
                // Convert to base64 data URI
                $qrCode = '<img src="data:image/png;base64,' . base64_encode($qrImage) . '" alt="QR Code" class="mx-auto border-4 border-indigo-500 rounded-lg shadow-lg" style="width: 300px; height: 300px;" />';
            }

            return view('product-qr', compact('product', 'qrCode'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal generate QR Code: ' . $e->getMessage());
        }
    }
}
